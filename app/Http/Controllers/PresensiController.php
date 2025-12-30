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

            $validated = $request->validate([
                'photo' => 'required|string', // base64 image
                'type' => 'required|in:masuk,pulang',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
            ]);

            // Ambil titik koordinat kantor
            $office = OfficeSetting::first();
            if ($office) {
                $distance = $this->haversine($validated['latitude'], $validated['longitude'], $office->latitude, $office->longitude);
                \Log::info("Presensi attempt - User location: {$validated['latitude']}, {$validated['longitude']} | Office: {$office->latitude}, {$office->longitude} | Distance: {$distance}m | Radius: {$office->radius}m");
                if ($distance > $office->radius) {
                    return response()->json(['success' => false, 'message' => 'Anda berada di luar radius kantor'], 403);
                }
            }

            $today = now()->toDateString();

            // Decode base64 image
            $imageData = $validated['photo'];
            $imageData = str_replace('data:image/jpeg;base64,', '', $imageData);
            $imageData = str_replace('data:image/png;base64,', '', $imageData);
            $imageData = str_replace(' ', '+', $imageData);
            $imageData = base64_decode($imageData);

            // Generate filename
            $filename = 'presensi_' . $pegawai->nip . '_' . $validated['type'] . '_' . now()->format('Y-m-d_H-i-s') . '.jpg';
            $path = 'presensi/' . $filename;

            // Save image
            Storage::disk('public')->put($path, $imageData);

            // Prepare data for updateOrCreate
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

            // Update or create presensi record
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
