<?php

namespace App\Http\Controllers;

use App\Models\OfficeSetting;
use App\Models\Pegawai;
use App\Models\Presensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PresensiController extends Controller
{
    /**
     * Display presensi page with camera access.
     */
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

        // Get all presensi today
        $today = now()->toDateString();
        $presensiHariIni = Presensi::where('nip', $pegawai->nip)
            ->whereDate('tanggal_presensi', $today)
            ->orderBy('created_at', 'asc')
            ->get();

        // Separate masuk and pulang
        $presensiMasuk = $presensiHariIni->where('type', 'masuk')->first();
        $presensiPulang = $presensiHariIni->where('type', 'pulang')->first();

        return view('pegawai.presensi', compact('pegawai', 'presensiMasuk', 'presensiPulang'));
    }

    /**
     * Store presensi with photo capture.
     */
    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Anda harus login terlebih dahulu.']);
            }

            $pegawai = Pegawai::where('users_id', $user->id)->first();
            if (!$pegawai) {
                return response()->json(['success' => false, 'message' => 'Data pegawai tidak ditemukan.']);
            }

            // Validate that pegawai has original face photo
            if (!$pegawai->foto_wajah_asli) {
                return response()->json(['success' => false, 'message' => 'Foto wajah asli tidak ditemukan di profil Anda.'], 422);
            }

            $validated = $request->validate([
                'foto_selfie' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:8192',
                'type' => 'required|in:masuk,pulang',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
            ]);

            // Handle uploaded selfie file
            $uploaded = $request->file('foto_selfie');
            $tmpDir = 'presensi/tmp';
            $tmpFilename = 'temp_' . $pegawai->nip . '_' . uniqid() . '.' . $uploaded->getClientOriginalExtension();
            // store temporarily on public disk
            $tmpRelative = $uploaded->storeAs($tmpDir, $tmpFilename, 'public');
            $tempPath = storage_path('app/public/' . $tmpRelative);

            // Get source image path (original face reference)
            $sourceImagePath = storage_path('app/public/' . $pegawai->foto_wajah_asli);

            // Call external API for face comparison
            $response = \Illuminate\Support\Facades\Http::attach(
                'source_image',
                fopen($sourceImagePath, 'r'),
                basename($sourceImagePath)
            )->attach(
                'target_image',
                fopen($tempPath, 'r'),
                basename($tempPath)
            )->post('http://domain-anda.com/python-api/compare');

            // Clean up temporary file after comparison (we'll re-save final file later)
            // but only delete the tmp if exists
            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }

            if ($response->failed() || !$response->json('match')) {
                return response()->json(['success' => false, 'message' => 'Wajah tidak cocok'], 422);
            }

            // Validate geofencing
            $office = OfficeSetting::first();
            if ($office) {
                $distance = $this->haversine($validated['latitude'], $validated['longitude'], $office->latitude, $office->longitude);
                Log::info("Presensi attempt - User location: {$validated['latitude']}, {$validated['longitude']} | Office: {$office->latitude}, {$office->longitude} | Distance: {$distance}m | Radius: {$office->radius}m");
                if ($distance > $office->radius) {
                    return response()->json(['success' => false, 'message' => 'Anda berada di luar radius kantor'], 403);
                }
            }

            $today = now()->toDateString();

            // Save final selfie file to presensi/ folder
            $finalFilename = 'presensi_' . $pegawai->nip . '_' . $validated['type'] . '_' . now()->format('Y-m-d_H-i-s') . '.jpg';
            $finalRelative = 'presensi/' . $finalFilename;

            // If original uploaded file is available, store it as final
            if (isset($uploaded) && $uploaded->isValid()) {
                // store the uploaded file content directly to final path on public disk
                \Illuminate\Support\Facades\Storage::disk('public')->putFileAs('presensi', $uploaded, $finalFilename);
            } else {
                // fallback: if comparison returned binary, attempt to use response body (unlikely)
                // create an empty placeholder
                \Illuminate\Support\Facades\Storage::disk('public')->put($finalRelative, '');
            }
            $path = $finalRelative;

            // Prepare data
            $data = [
                'nip' => $pegawai->nip,
                'tanggal_presensi' => $today,
                'type' => $validated['type'],
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
            ];

            if ($validated['type'] === 'masuk') {
                $data['jam_masuk'] = now()->format('H:i:s');
                $data['foto_masuk'] = $path;
            } else {
                $data['jam_pulang'] = now()->format('H:i:s');
                $data['foto_pulang'] = $path;
            }

            Presensi::updateOrCreate(
                [
                    'nip' => $pegawai->nip,
                    'tanggal_presensi' => $today,
                    'type' => $validated['type'],
                ],
                $data
            );

            return response()->json([
                'success' => true,
                'message' => 'Presensi ' . $validated['type'] . ' berhasil dicatat pada ' . now()->format('H:i:s')
            ]);

        } catch (\Exception $e) {
            Log::error('Error storing presensi: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan saat menyimpan presensi.']);
        }
    }

    /**
     * Calculate distance between two coordinates using Haversine formula.
     */
    private function haversine($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Earth radius in meters

        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
