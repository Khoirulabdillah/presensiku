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
    public function presensi(Request $request)
    {
        // Start with base query
        $presensis = \App\Models\Presensi::query();

        // Filter berdasarkan status jika ada
        if ($request->filled('status')) {
            $presensis->where('status', $request->input('status'));
        }

        // Filter berdasarkan type jika ada
        if ($request->filled('type')) {
            $presensis->where('type', $request->input('type'));
        }

        // Order dan paginate
        $presensis = $presensis
            ->with(['pegawai' => function ($q) {
                $q->with('divisi');
            }])
            ->orderBy('tanggal_presensi', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15);
        
        \Log::info('Presensi Query - Status: ' . ($request->input('status') ?? 'null') . ', Type: ' . ($request->input('type') ?? 'null') . ', Count: ' . count($presensis));
        
        return view('admin.presensi', compact('presensis'));
    }

    // Excel export removed — use PDF or CSV exports instead

    /**
     * Export presensi sebagai PDF
     */
    public function exportPdf(Request $request)
    {
        $query = \App\Models\Presensi::query();
        if ($request->filled('status')) $query->where('status', $request->input('status'));
        if ($request->filled('type')) $query->where('type', $request->input('type'));

        $presensis = $query->with('pegawai')->orderBy('tanggal_presensi', 'desc')->get();

        if (!class_exists('Barryvdh\\DomPDF\\Facade\\Pdf') && !class_exists('Dompdf\\Dompdf')) {
            return redirect()->back()->withErrors(['error' => 'Package barryvdh/laravel-dompdf belum terinstal. Jalankan `composer require barryvdh/laravel-dompdf`.']);
        }

        $pdf = \PDF::loadView('admin.presensi_pdf', compact('presensis'));
        $fileName = 'presensi_'.now()->format('Ymd_His').'.pdf';
        return $pdf->download($fileName);
    }

    /**
     * Export presensi sebagai CSV (stream) — avoids PHPExcel issues on newer PHP versions
     */
    public function exportCsv(Request $request)
    {
        $query = \App\Models\Presensi::query();
        if ($request->filled('status')) $query->where('status', $request->input('status'));
        if ($request->filled('type')) $query->where('type', $request->input('type'));
        // Load presensi with related pegawai and divisi
        $presensis = $query->with(['pegawai.divisi'])->orderBy('tanggal_presensi', 'desc')->get();

        $fileName = 'presensi_'.now()->format('Ymd_His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        // Sort items by Divisi name, pegawai name, then date
        $items = $presensis->sortBy(function($p) {
            $div = $p->pegawai->divisi->nama_divisi ?? 'ZZZ';
            $name = $p->pegawai->nama_pegawai ?? '';
            $date = $p->tanggal_presensi?->toDateString() ?? '';
            return sprintf("%s|%s|%s", $div, $name, $date);
        })->values();

        $fileHeaders = ['Divisi','Jabatan','NIP','Nama','Tanggal','Type','Status','Jam Masuk','Jam Pulang','Latitude','Longitude'];

        $callback = function() use ($items, $fileHeaders) {
            $out = fopen('php://output', 'w');
            // BOM for Excel to recognize UTF-8
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

            // Single header row for the whole file
            fputcsv($out, $fileHeaders);

            $prevDiv = null;
            foreach ($items as $p) {
                $currentDiv = $p->pegawai->divisi->nama_divisi ?? 'Tanpa Divisi';

                // If divisi changed and it's not the first row, insert a blank separator row
                if (!is_null($prevDiv) && $currentDiv !== $prevDiv) {
                    fputcsv($out, array_fill(0, count($fileHeaders), ''));
                }

                fputcsv($out, [
                    $currentDiv,
                    $p->pegawai->jabatan ?? '',
                    $p->nip,
                    $p->pegawai->nama_pegawai ?? '-',
                    $p->tanggal_presensi?->format('d/m/Y') ?? '-',
                    ucfirst($p->type ?? '-'),
                    $p->status ?? '-',
                    $p->jam_masuk ?? '-',
                    $p->jam_pulang ?? '-',
                    $p->latitude ?? '',
                    $p->longitude ?? '',
                ]);

                $prevDiv = $currentDiv;
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Display admin pegawai page.
     */
    public function pegawai(Request $request)
    {
        $query = Pegawai::with('divisi');

        // Filter berdasarkan divisi jika ada
        if ($request->filled('divisi')) {
            $query->where('id_divisi', $request->input('divisi'));
        }

        // Filter berdasarkan jabatan jika ada
        if ($request->filled('jabatan')) {
            $query->where('jabatan', $request->input('jabatan'));
        }

        $pegawais = $query->orderBy('nama_pegawai', 'asc')->paginate(15);
        
        // Get list of divisi dan jabatan untuk dropdown
        $divisis = \App\Models\Divisi::orderBy('nama_divisi')->get();
        $jabatans = Pegawai::select('jabatan')->distinct()->whereNotNull('jabatan')->orderBy('jabatan')->pluck('jabatan');
        
        return view('admin.pegawai', compact('pegawais', 'divisis', 'jabatans'));
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
        // Normalize decimal separators (allow users to enter comma as decimal separator)
        $input = $request->all();
        if (isset($input['latitude'])) {
            $input['latitude'] = str_replace(',', '.', (string) $input['latitude']);
        }
        if (isset($input['longitude'])) {
            $input['longitude'] = str_replace(',', '.', (string) $input['longitude']);
        }

        // Validate after normalization
        $validated = validator($input, [
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius' => ['required', 'integer', 'min:1'],
            'jam_masuk' => ['required', 'date_format:H:i'],
            'jam_pulang' => ['required', 'date_format:H:i'],
        ])->validate();

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

    /**
     * Get preview data untuk presensi tertentu.
     */
    public function presensiPreview($id)
    {
        // 1. Ambil data presensi
        $presensi = \App\Models\Presensi::with(['pegawai' => function ($q) {
            $q->with('divisi');
        }])->findOrFail($id);

        // 2. LOGIKA PILIH FOTO (FIX)
        // Cek dulu apakah ini data Masuk atau Pulang, ambil kolom yang sesuai
        $fileFoto = ($presensi->type === 'masuk') ? $presensi->foto_masuk : $presensi->foto_pulang;

        // 3. LOGIKA FALLBACK (PEMINJAMAN FOTO)
        // Jika ini data PULANG tapi fotonya kosong, cari data MASUK di hari yang sama
        if ($presensi->type === 'pulang' && empty($fileFoto)) {
            $dataMasuk = \App\Models\Presensi::where('nip', $presensi->nip)
                ->where('tanggal_presensi', $presensi->tanggal_presensi)
                ->where('type', 'masuk')
                ->first();
            
            // Jika ketemu data masuknya, pakai foto masuk
            if ($dataMasuk && !empty($dataMasuk->foto_masuk)) {
                $fileFoto = $dataMasuk->foto_masuk;
            }
        }

        return response()->json([
            'id' => $presensi->id,
            'nip' => $presensi->nip,
            'tanggal_presensi' => $presensi->tanggal_presensi?->format('d/m/Y') ?? '-',
            'type' => $presensi->type,
            'status' => $presensi->status,
            'jam_masuk' => $presensi->jam_masuk,
            'jam_pulang' => $presensi->jam_pulang,
            'latitude' => $presensi->latitude,
            'longitude' => $presensi->longitude,
            
            // PENTING: Kirim sebagai 'foto_masuk' karena JavaScript Anda menunggunya dengan nama ini
            'foto_masuk' => $fileFoto, 
            
            'pegawai' => [
                'nip' => $presensi->pegawai->nip ?? '-',
                'nama_pegawai' => $presensi->pegawai->nama_pegawai ?? '-',
                'jabatan' => $presensi->pegawai->jabatan ?? '-',
                'divisi' => [
                    'nama_divisi' => $presensi->pegawai->divisi->nama_divisi ?? '-'
                ]
            ]
        ]);
    }
}