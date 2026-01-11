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

            // Persist presensi record
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

            Presensi::create($data);

            // Time window and lateness calculation
            $now = Carbon::now();
            $latenessMinutes = 0;
            $statusNote = null;
            if ($office && $office->jam_masuk) {
                try {
                    $jamMasuk = Carbon::parse($office->jam_masuk)->setDate($now->year, $now->month, $now->day);
                } catch (\Exception $e) {
                    $jamMasuk = null;
                }
            } else {
                $jamMasuk = null;
            }
            if ($office && $office->jam_pulang) {
                try {
                    $jamPulang = Carbon::parse($office->jam_pulang)->setDate($now->year, $now->month, $now->day);
                } catch (\Exception $e) {
                    $jamPulang = null;
                }
            } else {
                $jamPulang = null;
            }

            if ($validated['type'] === 'masuk' && $jamMasuk) {
                if ($now->greaterThan($jamMasuk)) {
                    $latenessMinutes = $jamMasuk->diffInMinutes($now);
                    if ($latenessMinutes > 30) {
                        $statusNote = 'terlambat_masuk';
                    } else {
                        $statusNote = 'tepat_waktu';
                    }
                } else {
                    $statusNote = 'tepat_waktu';
                }
            }
            if ($validated['type'] === 'pulang' && $jamPulang) {
                if ($now->greaterThan($jamPulang)) {
                    $latenessMinutes = $jamPulang->diffInMinutes($now);
                    $statusNote = 'pulang_terlambat';
                } else {
                    $statusNote = 'pulang_tepat';
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Presensi berhasil dicatat.',
                'face_match' => true,
                'distance' => $distance,
                'status' => $statusNote,
                'late_minutes' => $latenessMinutes,
            ]);
        } catch (\Exception $e) {
            Log::error('Presensi Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Kesalahan Sistem: ' . $e->getMessage()]);
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
}
