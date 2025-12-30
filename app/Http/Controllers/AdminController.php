<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\OfficeSetting;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Display admin presensi page.
     */
    public function presensi()
    {
        $presensis = \App\Models\Presensi::with('pegawai.divisi')->orderBy('tanggal_presensi', 'desc')->paginate(10);
        return view('admin.presensi', compact('presensis'));
    }

    /**
     * Display admin pegawai page.
     */
    public function pegawai()
    {
        $pegawais = Pegawai::with('divisi')->paginate(10);
        return view('admin.pegawai', compact('pegawais'));
    }

    /**
     * Display admin office settings page.
     */
    public function officeSettings()
    {
        $officeSetting = OfficeSetting::first();
        return view('admin.office-settings', compact('officeSetting'));
    }

    /**
     * Update office settings.
     */
    public function updateOfficeSettings(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'required|integer|min:1',
            'jam_masuk' => 'required|date_format:H:i',
            'jam_pulang' => 'required|date_format:H:i',
        ]);

        $officeSetting = OfficeSetting::first();
        if ($officeSetting) {
            $officeSetting->update($validated);
        } else {
            OfficeSetting::create($validated);
        }

        return redirect()->back()->with('success', 'Pengaturan lokasi kantor berhasil diperbarui.');
    }

    /**
     * Display admin setting waktu page.
     */
    public function settingWaktu()
    {
        $officeSetting = OfficeSetting::first();
        return view('admin.setting-waktu', compact('officeSetting'));
    }

    /**
     * Display admin izin page.
     */
    public function izin()
    {
        $izin = \App\Models\Izin::with('pegawai.divisi')->orderBy('created_at', 'desc')->paginate(10);
        return view('admin.izin-index', compact('izin'));
    }
}