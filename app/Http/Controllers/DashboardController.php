<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\Presensi;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Redirect pegawai to their specific dashboard
        if ($user->role === 'pegawai') {
            return redirect()->route('pegawai.home');
        }

        $today = now()->toDateString();

        // Hitung hadir hari ini (presensi masuk)
        $hadirHariIni = Presensi::whereDate('tanggal_presensi', $today)
            ->where('type', 'masuk')
            ->count();

        // Hitung terlambat (misal jam masuk > 08:00)
        $terlambatHariIni = Presensi::whereDate('tanggal_presensi', $today)
            ->where('type', 'masuk')
            ->whereTime('jam_masuk', '>', '08:00:00')
            ->count();

        // Total pegawai
        $totalPegawai = Pegawai::count();

        // Tidak hadir (total pegawai - hadir)
        $tidakHadirHariIni = $totalPegawai - $hadirHariIni;

        return view('dashboard', compact('hadirHariIni', 'terlambatHariIni', 'totalPegawai', 'tidakHadirHariIni'));
    }
}
