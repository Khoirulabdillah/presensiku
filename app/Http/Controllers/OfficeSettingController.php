<?php

namespace App\Http\Controllers;

use App\Models\OfficeSetting;
use Illuminate\Http\Request;

class OfficeSettingController extends Controller
{
    /**
     * Display the office settings page.
     */
    public function index()
    {
        $officeSetting = OfficeSetting::first();
        return view('admin.office-settings', compact('officeSetting'));
    }

    /**
     * Update the office settings.
     */
    public function update(Request $request)
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
}