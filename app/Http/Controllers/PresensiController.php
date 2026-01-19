<?php

namespace App\Http\Controllers;

use App\Models\OfficeSetting;
use App\Models\Pegawai;
use App\Models\Presensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class PresensiController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->withErrors(['error' => 'Anda harus login terlebih dahulu.']);
        }

        $pegawai = Pegawai::with('divisi')->where('users_id', $user->id)->first();
        if (!$pegawai) {
            return back()->withErrors(['error' => 'Data pegawai tidak ditemukan untuk akun ini.']);
        }

        // AMBIL DATA PENGATURAN KANTOR (Jam Masuk, Pulang, dll)
        $office = \App\Models\OfficeSetting::first();
        if (!$office) {
        return back()->withErrors(['error' => 'Pengaturan kantor belum diatur oleh admin.']);
        }

        $today = now()->toDateString();
        $presensiHariIni = Presensi::where('nip', $pegawai->nip)
            ->whereDate('tanggal_presensi', $today)
            ->orderBy('created_at', 'asc')
            ->get();

        $presensiMasuk = $presensiHariIni->where('type', 'masuk')->first();
        $presensiPulang = $presensiHariIni->where('type', 'pulang')->first();

        return view('pegawai.presensi', compact('pegawai', 'presensiMasuk', 'presensiPulang','office'));
    }

    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthorized: user not authenticated.'], 401);
            }
            $pegawai = Pegawai::where('users_id', $user->id)->first();
            if (!$pegawai) {
                return response()->json(['success' => false, 'message' => 'Data pegawai untuk user ini tidak ditemukan.'], 404);
            }

            // Validate input (photo is base64 data URL)
            $validated = $request->validate([
                'photo' => 'required|string',
                'type' => 'required|in:masuk,pulang',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
            ]);

            // Decode base64 image
            $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $validated['photo']);
            $imageBinary = base64_decode(str_replace(' ', '+', $imageData));

            // Ensure storage directory
            $dir = 'presensi';
            if (!Storage::disk('public')->exists($dir)) {
                Storage::disk('public')->makeDirectory($dir);
            }

            // Preview: don't save before checks? we'll save then delete if needed
            $finalFilename = 'presensi_' . $pegawai->nip . '_' . $validated['type'] . '_' . now()->format('Ymd_His') . '.jpg';
            $finalRelative = $dir . '/' . $finalFilename;
            Storage::disk('public')->put($finalRelative, $imageBinary);

            // verify file was saved
            if (!Storage::disk('public')->exists($finalRelative)) {
                Log::error('Presensi Error: saved file missing - ' . $finalRelative);
                return response()->json(['success' => false, 'message' => 'Gagal menyimpan foto presensi. Periksa konfigurasi storage.'], 500);
            }

            // Geofencing check (apply optional smaller override via env PRESENSI_MAX_RADIUS in meters)
            $office = OfficeSetting::first();
            $distance = null;
            if ($office) {
                $distance = $this->haversine($validated['latitude'], $validated['longitude'], $office->latitude, $office->longitude);
                $overrideRadius = intval(env('PRESENSI_MAX_RADIUS', 200));
                $effectiveRadius = min($office->radius ?? $overrideRadius, $overrideRadius);
                if ($distance > $effectiveRadius) {
                    // delete saved file
                    Storage::disk('public')->delete($finalRelative);
                    return response()->json(['success' => false, 'message' => 'Di luar radius kantor (' . round($distance) . 'm)', 'distance' => $distance], 403);
                }
            }

            // Face validation against reference photo (if available)
            $flaskUrl = rtrim(env('FLASK_SERVER_URL', 'http://127.0.0.1:5000'), '/');
            $referencePath = $pegawai->foto_wajah_asli;
            if (!$referencePath || !Storage::disk('public')->exists($referencePath)) {
                Storage::disk('public')->delete($finalRelative);
                return response()->json([
                    'success' => false,
                    'message' => 'Foto referensi pegawai belum tersedia, hubungi admin.',
                ], 422);
            }

            try {
                $referenceBinary = Storage::disk('public')->get($referencePath);
                $referenceBase64 = 'data:image/jpeg;base64,' . base64_encode($referenceBinary);
                $photoBase64 = 'data:image/jpeg;base64,' . base64_encode($imageBinary);

                $resp = Http::timeout(10)->post($flaskUrl . '/api/validate-face', [
                    'photo' => $photoBase64,
                    'reference_photo' => $referenceBase64,
                ]);

                if (!$resp->successful()) {
                    Storage::disk('public')->delete($finalRelative);
                    return response()->json([
                        'success' => false,
                        'message' => 'Validasi wajah gagal (server Flask tidak merespon).',
                    ], 502);
                }

                $body = $resp->json();
                $distance = $body['distance'] ?? null;
                $similarity = $body['similarity'] ?? null;
                $match = $body['match'] ?? false;

                $maxDistance = floatval(env('FACE_DISTANCE_THRESHOLD', 0.50));
                $minSimilarity = floatval(env('FACE_MIN_SIMILARITY', 70.0));

                $distanceFail = ($distance !== null) && ($distance >= $maxDistance);
                $similarityFail = ($similarity !== null) && ($similarity < $minSimilarity);

                if (!($body['success'] ?? false) || $distanceFail || $similarityFail) {
                    Storage::disk('public')->delete($finalRelative);
                    return response()->json([
                        'success' => false,
                        'message' => $body['message'] ?? 'Wajah tidak cocok',
                        'similarity' => $similarity,
                        'distance' => $distance,
                        'thresholds' => [
                            'distance_max' => $maxDistance,
                            'similarity_min' => $minSimilarity,
                        ],
                    ], 403);
                }
            } catch (\Exception $e) {
                Storage::disk('public')->delete($finalRelative);
                Log::error('Face validation error: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi wajah gagal diproses.',
                ], 500);
            }

            // Prevent duplicate presensi and enforce Masuk before Pulang
            $today = now()->toDateString();
            $hasMasuk = Presensi::where('nip', $pegawai->nip)->whereDate('tanggal_presensi', $today)->where('type', 'masuk')->exists();
            $hasPulang = Presensi::where('nip', $pegawai->nip)->whereDate('tanggal_presensi', $today)->where('type', 'pulang')->exists();

            if ($validated['type'] === 'masuk' && $hasMasuk) {
                Storage::disk('public')->delete($finalRelative);
                return response()->json(['success' => false, 'message' => 'Sudah melakukan presensi masuk pada hari ini.'], 422);
            }
            if ($validated['type'] === 'pulang') {
                if (!$hasMasuk) {
                    Storage::disk('public')->delete($finalRelative);
                    return response()->json(['success' => false, 'message' => 'Belum melakukan presensi masuk, tidak dapat melakukan presensi pulang.'], 422);
                }
                if ($hasPulang) {
                    Storage::disk('public')->delete($finalRelative);
                    return response()->json(['success' => false, 'message' => 'Sudah melakukan presensi pulang pada hari ini.'], 422);
                }
            }

            // determine status and minute deltas using office settings and Carbon
            $now = Carbon::now();
            $latenessMinutes = 0;
            $earlyMinutes = 0;
            $statusNote = null;

            // parse office times
            $jamMasuk = null;
            $jamPulang = null;
            $batasAwalMasuk = 0;
            $toleransiTerlambat = 0;
            if ($office) {
                try {
                    if ($office->jam_masuk) {
                        $jamMasuk = Carbon::parse($office->jam_masuk)->setDate($now->year, $now->month, $now->day);
                    }
                } catch (\Exception $e) {
                    $jamMasuk = null;
                }
                try {
                    if ($office->jam_pulang) {
                        $jamPulang = Carbon::parse($office->jam_pulang)->setDate($now->year, $now->month, $now->day);
                    }
                } catch (\Exception $e) {
                    $jamPulang = null;
                }
                $batasAwalMasuk = intval($office->batas_awal_masuk ?? 0);
                $toleransiTerlambat = intval($office->toleransi_terlambat ?? 0);
            }

            if ($validated['type'] === 'masuk') {
            if ($jamMasuk) {
                $awalAbsen = $jamMasuk->copy()->subMinutes($batasAwalMasuk);
                
                // 1. Cek Batas Awal (Sudah benar)
                if ($now->lt($awalAbsen)) {
                    Storage::disk('public')->delete($finalRelative);
                    return response()->json(['success' => false, 'message' => 'Belum waktunya presensi masuk.'], 422);
                }

                // 2. Tentukan Batas Toleransi
                $batasToleransi = $jamMasuk->copy()->addMinutes($toleransiTerlambat);

                // 3. Logika Perbandingan (Perbaikan Utama)
                if ($now->gt($batasToleransi)) {
                    $statusNote = 'Terlambat';
                    // Hitung menit keterlambatan (Waktu Sekarang - Jam Masuk)
                    $latenessMinutes = $now->diffInMinutes($jamMasuk);
                } else {
                    $statusNote = 'Tepat Waktu';
                    $latenessMinutes = 0;
                }
            } else {
                $statusNote = 'Tepat Waktu';
            }
        }
            if ($validated['type'] === 'pulang') {
                if ($jamPulang) {
                    if ($now->lt($jamPulang)) {
                        $statusNote = 'Pulang Lebih Awal';
                        $earlyMinutes = $jamPulang->diffInMinutes($now);
                    } else {
                        $statusNote = 'Pulang';
                        $earlyMinutes = 0;
                    }
                } else {
                    $statusNote = 'Pulang';
                }
            }

            // Persist presensi record with computed status/minutes
            $data = [
                'nip' => $pegawai->nip,
                'tanggal_presensi' => $now->toDateString(),
                'type' => $validated['type'],
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'status' => $statusNote,
                'late_minutes' => $latenessMinutes,
                'early_minutes' => $earlyMinutes,
            ];

            if ($validated['type'] === 'masuk') {
                $data['jam_masuk'] = $now->format('H:i:s');
                $data['foto_masuk'] = $finalRelative;
            } else {
                $data['jam_pulang'] = $now->format('H:i:s');
                $data['foto_pulang'] = $finalRelative;
            }

            $presensi = Presensi::updateOrCreate(
                [
                    'nip' => $pegawai->nip,
                    'tanggal_presensi' => $now->toDateString(),
                    'type' => $validated['type'],
                ],
                $data
            );

            return response()->json([
                'success' => true,
                'message' => 'Presensi berhasil dicatat.',
                'face_match' => true,
                'distance' => $distance,
                'status' => $statusNote,
                'late_minutes' => $latenessMinutes,
                'early_minutes' => $earlyMinutes,
            ]);
        } catch (\Exception $e) {
            Log::error('Presensi Error: ' . $e->getMessage() . '\n' . $e->getTraceAsString());
            return response()->json(['success' => false, 'message' => 'Kesalahan Sistem: ' . $e->getMessage()], 500);
        }
    }

    private function haversine($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000;
        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);
        $a = sin($latDelta / 2) * sin($latDelta / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lonDelta / 2) * sin($lonDelta / 2);
        return $earthRadius * (2 * atan2(sqrt($a), sqrt(1 - $a)));
    }

    // Compute average hash (aHash) for an image file path. Returns 64-bit bitstring like '0101...'
    private function imageAHash($filePath)
    {
        if (!file_exists($filePath)) return null;
        try {
            $data = file_get_contents($filePath);
            if ($data === false) return null;
            $img = @imagecreatefromstring($data);
            if (!$img) return null;
            // resize to 8x8
            $w = imagesx($img);
            $h = imagesy($img);
            $thumb = imagecreatetruecolor(8, 8);
            imagecopyresampled($thumb, $img, 0, 0, 0, 0, 8, 8, $w, $h);
            // compute grayscale values
            $total = 0;
            $vals = [];
            for ($y = 0; $y < 8; $y++) {
                for ($x = 0; $x < 8; $x++) {
                    $rgb = imagecolorat($thumb, $x, $y);
                    $r = ($rgb >> 16) & 0xFF;
                    $g = ($rgb >> 8) & 0xFF;
                    $b = $rgb & 0xFF;
                    $gray = (int)round(0.299 * $r + 0.587 * $g + 0.114 * $b);
                    $vals[] = $gray;
                    $total += $gray;
                }
            }
            imagedestroy($thumb);
            imagedestroy($img);
            $mean = $total / 64.0;
            $bits = '';
            foreach ($vals as $v) {
                $bits .= ($v > $mean) ? '1' : '0';
            }
            return $bits;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function hammingDistance($a, $b)
    {
        if ($a === null || $b === null) return PHP_INT_MAX;
        if (strlen($a) !== strlen($b)) return PHP_INT_MAX;
        $dist = 0;
        for ($i = 0, $len = strlen($a); $i < $len; $i++) {
            if ($a[$i] !== $b[$i]) $dist++;
        }
        return $dist;
    }

    public function indexAdmin()
    {
        $presensis = Presensi::with('pegawai')->get();
        return view('admin.presensi', compact('presensis'));
    }

    // Jika ingin mendapatkan hanya pegawai yang pernah presensi (tanpa duplikasi):
    public function pegawaiYangPresesi()
    {
        $pegawais = Pegawai::whereHas('presensis')
            ->distinct()
            ->get();

        return view('admin.pegawai-presensi', compact('pegawais'));
    }
}
