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

        $today = now()->toDateString();
        $presensiHariIni = Presensi::where('nip', $pegawai->nip)
            ->whereDate('tanggal_presensi', $today)
            ->orderBy('created_at', 'asc')
            ->get();

        $presensiMasuk = $presensiHariIni->where('type', 'masuk')->first();
        $presensiPulang = $presensiHariIni->where('type', 'pulang')->first();

        return view('pegawai.presensi', compact('pegawai', 'presensiMasuk', 'presensiPulang'));
    }

    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            $pegawai = Pegawai::where('users_id', $user->id)->first();

            // 1. Validasi Input Base64 dari Blade
            $validated = $request->validate([
                'photo' => 'required|string', 
                'type' => 'required|in:masuk,pulang',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
            ]);

            // 2. Proses Konversi Base64 ke File Temp
            $imageData = $request->photo;
            $imageData = str_replace('data:image/jpeg;base64,', '', $imageData);
            $imageData = str_replace(' ', '+', $imageData);
            $imageBinary = base64_decode($imageData);

            $tmpDir = 'presensi/tmp';
            if (!Storage::disk('public')->exists($tmpDir)) {
                Storage::disk('public')->makeDirectory($tmpDir);
            }

            $tmpFilename = 'temp_' . $pegawai->nip . '_' . uniqid() . '.jpg';
            $tmpRelative = $tmpDir . '/' . $tmpFilename;
            Storage::disk('public')->put($tmpRelative, $imageBinary);
            $tempPath = storage_path('app/public/' . $tmpRelative);

            $faceMatched = false; // will be set true when verification passes

            // 3. Siapkan Path Foto Referensi (Foto Asli Pegawai)
            $sourceImagePath = storage_path('app/public/' . $pegawai->foto_wajah_asli);

            if (!file_exists($sourceImagePath)) {
                return response()->json(['success' => false, 'message' => 'Foto referensi wajah asli tidak ditemukan.'], 422);
            }

            // 4. Hitung Geofencing (Cek Jarak)
            $office = OfficeSetting::first();
            if ($office) {
                $distance = $this->haversine($validated['latitude'], $validated['longitude'], $office->latitude, $office->longitude);
                if ($distance > $office->radius) {
                    if (file_exists($tempPath)) @unlink($tempPath);
                    return response()->json(['success' => false, 'message' => 'Di luar radius kantor (' . round($distance) . 'm)'], 403);
                }
            }

            // 5. Browser-side verification path: if client supplied descriptor, compare here.
            $clientDescriptor = $request->input('photo_descriptor');
            $browserTolerance = floatval(env('BROWSER_TOLERANCE', 0.6));
            $urlFlask = env('FLASK_COMPARE_URL');

            if ($clientDescriptor) {
                // Try to use stored encoding if available; otherwise request one-time encoding from Flask and cache it.
                $stored = $pegawai->foto_wajah_encoding;

                if (empty($stored) && $urlFlask) {
                    try {
                        $encodeResp = Http::timeout(10)->attach('image', fopen($sourceImagePath, 'r'), 'source.jpg')
                            ->post(rtrim($urlFlask, '/') . '/encode', [
                                'model' => env('FLASK_MODEL', 'hog'),
                                'num_jitters' => env('FLASK_NUM_JITTERS', 0),
                            ]);

                        $encData = $encodeResp->json();
                        if ($encodeResp->successful() && isset($encData['encoding']) && is_array($encData['encoding'])) {
                            $pegawai->foto_wajah_encoding = $encData['encoding'];
                            $pegawai->save();
                            $stored = $pegawai->foto_wajah_encoding;
                        } else {
                            Log::warning('Presensi: encode failed or no encoding returned', ['nip' => $pegawai->nip, 'resp' => $encData, 'temp' => $tmpRelative]);
                        }
                    } catch (\Exception $e) {
                        Log::warning('Presensi: encode request failed', ['nip' => $pegawai->nip, 'error' => $e->getMessage(), 'temp' => $tmpRelative]);
                    }
                }

                if (!empty($stored) && is_array($stored) && is_array($clientDescriptor)) {
                    $ref = count($stored) === count($clientDescriptor) ? $stored : ($stored[0] ?? $stored);
                    $sum = 0.0;
                    $len = min(count($ref), count($clientDescriptor));
                    for ($i = 0; $i < $len; $i++) {
                        $d = ($ref[$i] - $clientDescriptor[$i]);
                        $sum += $d * $d;
                    }
                    $dist = sqrt($sum);
                    if ($dist > $browserTolerance) {
                        Log::warning('Presensi: browser face verification failed', ['nip' => $pegawai->nip, 'distance' => $dist, 'threshold' => $browserTolerance, 'temp' => $tmpRelative]);
                        if (file_exists($tempPath)) @unlink($tempPath);
                        return response()->json(['success' => false, 'message' => 'Wajah tidak cocok (distance: ' . round($dist, 4) . ')'], 422);
                    }
                    // matched — continue to save
                } else {
                    // If still no stored encoding, fallback to Flask full compare if available
                    if ($urlFlask) {
                        $tolerance = env('FLASK_TOLERANCE', 0.50);
                        $numJitters = env('FLASK_NUM_JITTERS', 2);
                        $model = env('FLASK_MODEL', 'hog');

                        $response = Http::attach(
                            'source_image', fopen($sourceImagePath, 'r'), 'source.jpg'
                        )->attach(
                            'target_image', fopen($tempPath, 'r'), 'target.jpg'
                        )->timeout(15)->post($urlFlask, [
                            'tolerance' => $tolerance,
                            'num_jitters' => $numJitters,
                            'model' => $model,
                        ]);

                        $resData = $response->json();

                        if ($response->failed() || isset($resData['error']) || (isset($resData['match']) && !$resData['match'])) {
                            $msg = $resData['error'] ?? 'Wajah tidak cocok';
                            Log::warning('Presensi: flask verification failed', ['nip' => $pegawai->nip, 'flask' => $resData, 'message' => $msg, 'temp' => $tmpRelative]);
                            if (file_exists($tempPath)) @unlink($tempPath);
                            return response()->json(['success' => false, 'message' => $msg], 422);
                        }
                    } else {
                        if (file_exists($tempPath)) @unlink($tempPath);
                        return response()->json(['success' => false, 'message' => 'Tidak dapat memverifikasi wajah (no encoding).'], 422);
                    }
                }
            } else {
                // No client descriptor: existing fallback behavior
                $urlFlask = env('FLASK_COMPARE_URL');
                if ($urlFlask) {
                    $tolerance = env('FLASK_TOLERANCE', 0.50);
                    $numJitters = env('FLASK_NUM_JITTERS', 2);
                    $model = env('FLASK_MODEL', 'hog');

                    $response = Http::attach(
                        'source_image', fopen($sourceImagePath, 'r'), 'source.jpg'
                    )->attach(
                        'target_image', fopen($tempPath, 'r'), 'target.jpg'
                    )->post($urlFlask, [
                        'tolerance' => $tolerance,
                        'num_jitters' => $numJitters,
                        'model' => $model,
                    ]);

                    $resData = $response->json();

                    if ($response->failed() || isset($resData['error']) || (isset($resData['match']) && !$resData['match'])) {
                        $msg = $resData['error'] ?? 'Wajah tidak cocok';
                        Log::warning('Presensi: flask verification failed', ['nip' => $pegawai->nip, 'flask' => $resData, 'message' => $msg, 'temp' => $tmpRelative]);
                        if (file_exists($tempPath)) @unlink($tempPath);
                        return response()->json(['success' => false, 'message' => $msg], 422);
                    }
                } else {
                    // No client descriptor and no flask available -> cannot verify
                    if (file_exists($tempPath)) @unlink($tempPath);
                    return response()->json(['success' => false, 'message' => 'Tidak dapat memverifikasi wajah (no encoding).'], 422);
                }
            }

            // 6. Simpan File Final dan Database
            $finalFilename = 'presensi_' . $pegawai->nip . '_' . $validated['type'] . '_' . now()->format('Ymd_His') . '.jpg';
            $finalRelative = 'presensi/' . $finalFilename;
            
            Storage::disk('public')->move($tmpRelative, $finalRelative);

            $data = [
                'nip' => $pegawai->nip,
                'tanggal_presensi' => now()->toDateString(),
                'type' => $validated['type'],
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
            ];

            if ($validated['type'] === 'masuk') {
                $data['jam_masuk'] = now()->format('H:i:s');
                $data['foto_masuk'] = $finalRelative;
            } else {
                $data['jam_pulang'] = now()->format('H:i:s');
                $data['foto_pulang'] = $finalRelative;
            }

            Presensi::updateOrCreate(
                ['nip' => $pegawai->nip, 'tanggal_presensi' => now()->toDateString(), 'type' => $validated['type']],
                $data
            );

            // mark successful verification
            $faceMatched = true;

            return response()->json(['success' => true, 'message' => 'Presensi berhasil dicatat.', 'face_match' => $faceMatched]);

        } catch (\Exception $e) {
            Log::error('Presensi Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Kesalahan Sistem: ' . $e->getMessage()]);
        }
    }

    private function haversine($lat1, $lon1, $lat2, $lon2) {
        $earthRadius = 6371000;
        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);
        $a = sin($latDelta / 2) * sin($latDelta / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lonDelta / 2) * sin($lonDelta / 2);
        return $earthRadius * (2 * atan2(sqrt($a), sqrt(1 - $a)));
    }
}