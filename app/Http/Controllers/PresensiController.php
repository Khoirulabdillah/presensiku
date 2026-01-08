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

            // 5. Kirim ke Flask (Gunakan 'source_image' dan 'target_image' sesuai file Flask Anda)
            // GANTI URL DI BAWAH INI SESUAI URL FLASK ANDA (pastikan menyertakan scheme http/https)
            $urlFlask = 'https://presensiku.pribumics.my.id/python-api/compare'; 
            
            $response = Http::attach(
                'source_image', fopen($sourceImagePath, 'r'), 'source.jpg'
            )->attach(
                'target_image', fopen($tempPath, 'r'), 'target.jpg'
            )->post($urlFlask, [
                // tune these if necessary. num_jitters helps encoding stability but costs CPU
                'tolerance' => 0.55,
                'num_jitters' => 1,
                'model' => 'hog',
            ]);

            $resData = $response->json();

            // Log respons Flask untuk diagnosa
            if ($response->failed()) {
                Log::error('Flask compare failed', ['status' => $response->status(), 'body' => $response->body()]);
            } else {
                Log::info('Flask compare response', ['status' => $response->status(), 'body' => $response->body()]);
                if (is_array($resData) && isset($resData['debug'])) {
                    Log::debug('Flask debug', ['debug' => $resData['debug']]);
                }
            }

            // Cek jika Flask mengembalikan error (misal: wajah tidak terdeteksi)
            if ($response->failed() || isset($resData['error']) || (isset($resData['match']) && !$resData['match'])) {
                $msg = $resData['error'] ?? 'Wajah tidak cocok';
                Log::warning('Presensi: face verification failed', ['nip' => $pegawai->nip, 'flask' => $resData, 'message' => $msg]);
                if (file_exists($tempPath)) @unlink($tempPath);
                return response()->json(['success' => false, 'message' => $msg], 422);
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

            return response()->json(['success' => true, 'message' => 'Presensi berhasil dicatat.']);

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