<?php

namespace App\Http\Controllers;

use App\Models\Izin;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class IzinController extends Controller
{
    /**
     * Show form to create izin (pegawai).
     */
    public function create()
    {
        return view('pegawai.izin');
    }

    /**
     * Store a new izin request.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'jenis_izin' => 'required|string|max:100',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'keterangan' => 'required|string|max:2000',
            'bukti' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB
        ]);

        // find pegawai record for current user
        $user = Auth::user();
        $pegawai = Pegawai::where('users_id', $user->id)->first();
        if (! $pegawai) {
            return back()->withErrors(['bukti' => 'Data pegawai tidak ditemukan untuk akun ini.']);
        }

        $data = [
            'nip' => $pegawai->nip,
            'jenis_izin' => $validated['jenis_izin'],
            'status_izin' => 'pending',
            'tanggal_mulai' => $validated['tanggal_mulai'],
            'tanggal_selesai' => $validated['tanggal_selesai'] ?? null,
            'keterangan' => $validated['keterangan'],
        ];

        if ($request->hasFile('bukti')) {
            $path = $request->file('bukti')->store('izin', 'public');
            $data['bukti_path'] = $path;
        }

        Izin::create($data);

        return redirect()->route('pegawai.home')->with('success', 'Permohonan izin berhasil dikirim.');
    }
}
