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

        return view('pegawai.presensi', compact('pegawai', 'presensiMasuk', 'presensiPulang', 'office'));
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

            // Filename generation
            $finalFilename = 'presensi_' . $pegawai->nip . '_' . $validated['type'] . '_' . now()->format('Ymd_His') . '.jpg';
            $finalRelative = $dir . '/' . $finalFilename;
            Storage::disk('public')->put($finalRelative, $imageBinary);

            // verify file was saved
            if (!Storage::disk('public')->exists($finalRelative)) {
                Log::error('Presensi Error: saved file missing - ' . $finalRelative);
                return response()->json(['success' => false, 'message' => 'Gagal menyimpan foto presensi. Periksa konfigurasi storage.'], 500);
            }

            // Geofencing check
            $office = OfficeSetting::first();
            $distance = null;
            if ($office) {
                $distance = $this->haversine($validated['latitude'], $validated['longitude'], $office->latitude, $office->longitude);
                $overrideRadius = intval(env('PRESENSI_MAX_RADIUS', 200));
                $effectiveRadius = min($office->radius ?? $overrideRadius, $overrideRadius);
                if ($distance > $effectiveRadius) {
                    Storage::disk('public')->delete($finalRelative);
                    return response()->json(['success' => false, 'message' => 'Di luar radius kantor (' . round($distance) . 'm)', 'distance' => $distance], 403);
                }
            }

            // Face validation
            $flaskUrl = rtrim(env('FLASK_SERVER_URL', 'http://127.0.0.1:5000'), '/');
            $referencePath = $pegawai->foto_wajah_asli;
            
            // NOTE: Bagian ini opsional, jika pegawai belum punya foto referensi, bisa di-skip atau return error
            if ($referencePath && Storage::disk('public')->exists($referencePath)) {
                try {
                    $referenceBinary = Storage::disk('public')->get($referencePath);
                    $referenceBase64 = 'data:image/jpeg;base64,' . base64_encode($referenceBinary);
                    $photoBase64 = 'data:image/jpeg;base64,' . base64_encode($imageBinary);

                    $resp = Http::timeout(10)->post($flaskUrl . '/api/validate-face', [
                        'photo' => $photoBase64,
                        'reference_photo' => $referenceBase64,
                    ]);

                    if (!$resp->successful()) {
                        // Jika server flask mati, bisa pilih lanjut atau error. Di sini error.
                        Storage::disk('public')->delete($finalRelative);
                        return response()->json([
                            'success' => false,
                            'message' => 'Validasi wajah gagal (server AI tidak merespon).',
                        ], 502);
                    }

                    $body = $resp->json();
                    $distance = $body['distance'] ?? null;
                    $similarity = $body['similarity'] ?? null;
                    
                    $maxDistance = floatval(env('FACE_DISTANCE_THRESHOLD', 0.50));
                    $minSimilarity = floatval(env('FACE_MIN_SIMILARITY', 70.0));

                    $distanceFail = ($distance !== null) && ($distance >= $maxDistance);
                    $similarityFail = ($similarity !== null) && ($similarity < $minSimilarity);

                    if (!($body['success'] ?? false) || $distanceFail || $similarityFail) {
                        Storage::disk('public')->delete($finalRelative);
                        return response()->json([
                            'success' => false,
                            'message' => 'Wajah tidak cocok / tidak dikenali.',
                            'similarity' => $similarity,
                        ], 403);
                    }
                } catch (\Exception $e) {
                     Log::error('Face validation error: ' . $e->getMessage());
                     // Opsional: boleh return error atau bypass jika server AI bermasalah
                }
            }

            // Prevent duplicate presensi
            $today = now()->toDateString();
            $hasMasuk = Presensi::where('nip', $pegawai->nip)->whereDate('tanggal_presensi', $today)->where('type', 'masuk')->exists();
            $hasPulang = Presensi::where('nip', $pegawai->nip)->whereDate('tanggal_presensi', $today)->where('type', 'pulang')->exists();

            if ($validated['type'] === 'masuk' && $hasMasuk) {
                Storage::disk('public')->delete($finalRelative);
                return response()->json(['success' => false, 'message' => 'Sudah melakukan presensi masuk hari ini.'], 422);
            }
            if ($validated['type'] === 'pulang') {
                if (!$hasMasuk) {
                    Storage::disk('public')->delete($finalRelative);
                    return response()->json(['success' => false, 'message' => 'Belum presensi masuk.'], 422);
                }
                if ($hasPulang) {
                    Storage::disk('public')->delete($finalRelative);
                    return response()->json(['success' => false, 'message' => 'Sudah presensi pulang hari ini.'], 422);
                }
            }

            // Determine status
            $now = Carbon::now();
            $latenessMinutes = 0;
            $earlyMinutes = 0;
            $statusNote = null;
            $jamMasuk = null;
            $jamPulang = null;
            $batasAwalMasuk = 0;
            $toleransiTerlambat = 0;

            if ($office) {
                try {
                    if ($office->jam_masuk) $jamMasuk = Carbon::parse($office->jam_masuk)->setDate($now->year, $now->month, $now->day);
                    if ($office->jam_pulang) $jamPulang = Carbon::parse($office->jam_pulang)->setDate($now->year, $now->month, $now->day);
                } catch (\Exception $e) {}
                $batasAwalMasuk = intval($office->batas_awal_masuk ?? 0);
                $toleransiTerlambat = intval($office->toleransi_terlambat ?? 0);
            }

            if ($validated['type'] === 'masuk') {
                if ($jamMasuk) {
                    $awalAbsen = $jamMasuk->copy()->subMinutes($batasAwalMasuk);
                    if ($now->lt($awalAbsen)) {
                        Storage::disk('public')->delete($finalRelative);
                        return response()->json(['success' => false, 'message' => 'Belum waktunya presensi masuk.'], 422);
                    }
                    $batasToleransi = $jamMasuk->copy()->addMinutes($toleransiTerlambat);
                    if ($now->gt($batasToleransi)) {
                        $statusNote = 'Terlambat';
                        $latenessMinutes = $now->diffInMinutes($jamMasuk);
                    } else {
                        $statusNote = 'Tepat Waktu';
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
                    }
                } else {
                    $statusNote = 'Pulang';
                }
            }

            // Prepare Data
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

            // SIMPAN FOTO KE KOLOM YANG SESUAI
            if ($validated['type'] === 'masuk') {
                $data['jam_masuk'] = $now->format('H:i:s');
                $data['foto_masuk'] = $finalRelative; // Disimpan di kolom foto_masuk
            } else {
                $data['jam_pulang'] = $now->format('H:i:s');
                $data['foto_pulang'] = $finalRelative; // Disimpan di kolom foto_pulang
            }

            Presensi::updateOrCreate(
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
                'status' => $statusNote
            ]);

        } catch (\Exception $e) {
            Log::error('Presensi Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Kesalahan Sistem: ' . $e->getMessage()], 500);
        }
    }

    // --- METHOD BARU: UNTUK PREVIEW DATA DI ADMIN ---
    // Ganti method preview yang lama dengan yang ini
    public function preview($id)
    {
        try {
            // 1. Ambil data presensi yang diklik (misal: data Pulang)
            $presensi = Presensi::with(['pegawai', 'pegawai.divisi'])->findOrFail($id);

            // 2. LOGIKA CERDAS: Fallback Foto
            // Jika ini data PULANG, tapi foto_pulang KOSONG,
            // Maka kita cari data MASUK di hari yang sama untuk mengambil fotonya.
            if ($presensi->type === 'pulang' && empty($presensi->foto_pulang)) {
                $presensiMasuk = Presensi::where('nip', $presensi->nip)
                                    ->where('tanggal_presensi', $presensi->tanggal_presensi)
                                    ->where('type', 'masuk')
                                    ->first();
                
                // Jika ketemu data masuknya, kita "titipkan" fotonya ke variabel respon
                if ($presensiMasuk && !empty($presensiMasuk->foto_masuk)) {
                    $presensi->foto_masuk = $presensiMasuk->foto_masuk;
                }
            }

            return response()->json($presensi);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Data presensi tidak ditemukan.'], 404);
        }
    }
    // ------------------------------------------------

    public function indexAdmin(Request $request)
    {
        $query = Presensi::with('pegawai');

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }
        if ($request->has('type') && $request->type !== '') {
            $query->where('type', $request->type);
        }

        $presensis = $query->orderBy('tanggal_presensi', 'desc')->paginate(15);
        return view('admin.presensi', compact('presensis'));
    }

    public function riwayatPresensi(Request $request)
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $pegawai = Pegawai::with('divisi')->where('users_id', $user->id)->first();
        if (!$pegawai) return redirect()->route('pegawai.home');

        $presensis = Presensi::where('nip', $pegawai->nip)
            ->orderBy('tanggal_presensi', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('pegawai.riwayat-presensi', compact('presensis', 'pegawai'));
    }

    // Helper Functions
    private function haversine($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000;
        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);
        $a = sin($latDelta / 2) * sin($latDelta / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lonDelta / 2) * sin($lonDelta / 2);
        return $earthRadius * (2 * atan2(sqrt($a), sqrt(1 - $a)));
    }
}