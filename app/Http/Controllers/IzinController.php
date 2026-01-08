<?php

namespace App\Http\Controllers;

use App\Models\Izin;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;

class IzinController extends Controller
{
    /**
     * Display izin management page for pegawai (index page with all features).
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

        // Get izin data for current user
        $izin = Izin::where('nip', $pegawai->nip)->orderBy('created_at', 'desc')->get();

        // Calculate statistics
        $stats = [
            'pending' => $izin->where('status_izin', 'pending')->count(),
            'approved' => $izin->where('status_izin', 'approved')->count(),
            'rejected' => $izin->where('status_izin', 'rejected')->count(),
            'total' => $izin->count(),
        ];

        return view('pegawai.izin', compact('izin', 'stats', 'pegawai'));
    }

    /**
     * Show form to create izin (redirect to index with create tab).
     */
    public function create()
    {
        return redirect()->route('pegawai.izin.index')->with('active_tab', 'create');
    }

    /**
     * Store a new izin request.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'jenis_izin' => 'required|string|max:100',
                'tanggal_mulai' => 'required|date',
                'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
                'keterangan' => 'required|string|max:2000',
                'bukti' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB
            ]);

            // find pegawai record for current user
            $user = Auth::user();
            if (!$user) {
                return back()->withErrors(['error' => 'Anda harus login terlebih dahulu.']);
            }

            $pegawai = Pegawai::where('users_id', $user->id)->first();
            if (!$pegawai) {
                return back()->withErrors(['error' => 'Data pegawai tidak ditemukan untuk akun ini.']);
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

            return redirect()->route('pegawai.izin.index')->with('success', 'Permohonan izin berhasil dikirim.');
        } catch (\Exception $e) {
            \Log::error('Error creating izin: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Show form to edit izin for current user (pegawai).
     */
    public function editPegawai($id)
    {
        $user = Auth::user();
        $pegawai = Pegawai::with('divisi')->where('users_id', $user->id)->first();

        if (!$pegawai) {
            return back()->withErrors(['error' => 'Data pegawai tidak ditemukan.']);
        }

        $izin = Izin::where('nip', $pegawai->nip)->findOrFail($id);

        // Only allow editing if status is pending
        if ($izin->status_izin !== 'pending') {
            return back()->withErrors(['error' => 'Izin yang sudah diproses tidak dapat diedit.']);
        }

        return view('pegawai.izin-edit', compact('izin', 'pegawai'));
    }

    /**
     * Update izin for current user (pegawai).
     */
    public function updatePegawai(Request $request, $id)
    {
        try {
            $user = Auth::user();
            $pegawai = Pegawai::where('users_id', $user->id)->first();

            if (!$pegawai) {
                return back()->withErrors(['error' => 'Data pegawai tidak ditemukan.']);
            }

            $izin = Izin::where('nip', $pegawai->nip)->findOrFail($id);

            // Only allow updating if status is pending
            if ($izin->status_izin !== 'pending') {
                return back()->withErrors(['error' => 'Izin yang sudah diproses tidak dapat diupdate.']);
            }

            $validated = $request->validate([
                'jenis_izin' => 'required|string|max:100',
                'tanggal_mulai' => 'required|date',
                'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
                'keterangan' => 'required|string|max:2000',
                'bukti' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB
            ]);

            $data = [
                'jenis_izin' => $validated['jenis_izin'],
                'tanggal_mulai' => $validated['tanggal_mulai'],
                'tanggal_selesai' => $validated['tanggal_selesai'] ?? null,
                'keterangan' => $validated['keterangan'],
            ];

            if ($request->hasFile('bukti')) {
                // Delete old file if exists
                if ($izin->bukti_path && Storage::disk('public')->exists($izin->bukti_path)) {
                    Storage::disk('public')->delete($izin->bukti_path);
                }

                $path = $request->file('bukti')->store('izin', 'public');
                $data['bukti_path'] = $path;
            }

            $izin->update($data);

            return redirect()->route('pegawai.izin.index')->with('success', 'Permohonan izin berhasil diperbarui.');
        } catch (\Exception $e) {
            \Log::error('Error updating izin: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Terjadi kesalahan saat mengupdate data: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Delete izin for current user (pegawai).
     */
    public function destroyPegawai($id)
    {
        try {
            $user = Auth::user();
            $pegawai = Pegawai::where('users_id', $user->id)->first();

            if (!$pegawai) {
                return back()->withErrors(['error' => 'Data pegawai tidak ditemukan.']);
            }

            $izin = Izin::where('nip', $pegawai->nip)->findOrFail($id);

            // Only allow deleting if status is pending
            if ($izin->status_izin !== 'pending') {
                return back()->withErrors(['error' => 'Izin yang sudah diproses tidak dapat dihapus.']);
            }

            // Delete file if exists
            if ($izin->bukti_path && Storage::disk('public')->exists($izin->bukti_path)) {
                Storage::disk('public')->delete($izin->bukti_path);
            }

            $izin->delete();

            return redirect()->route('pegawai.izin.index')->with('success', 'Permohonan izin berhasil dihapus.');
        } catch (\Exception $e) {
            \Log::error('Error deleting izin: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage()]);
        }
    }

    // Admin methods (keeping existing functionality)
    /**
     * Display list of izin requests (admin).
     */
    public function indexAdmin()
    {
        $izin = Izin::with('pegawai')->orderBy('created_at', 'desc')->paginate(10);
        return view('admin.izin-index', compact('izin'));
    }

    /**
     * Display specific izin request (admin).
     */
    public function show($id)
    {
        $izin = Izin::with('pegawai')->findOrFail($id);
        return view('admin.izin-show', compact('izin'));
    }

    /**
     * Show form to edit izin status (admin).
     */
    public function edit($id)
    {
        $izin = Izin::with('pegawai')->findOrFail($id);
        return view('admin.izin-edit', compact('izin'));
    }

    /**
     * Update izin status (admin).
     */
    public function update(Request $request, $id)
    {
        $izin = Izin::findOrFail($id);

        $validated = $request->validate([
            'status_izin' => 'required|in:pending,approved,rejected',
            'catatan_admin' => 'nullable|string|max:1000',
        ]);

        // Prepare update data and only include `catatan_admin` if the column exists
        $updateData = [
            'status_izin' => $validated['status_izin'],
        ];

        if (Schema::hasColumn((new Izin)->getTable(), 'catatan_admin')) {
            $updateData['catatan_admin'] = $validated['catatan_admin'] ?? null;
        }

        $izin->update($updateData);

        return redirect()->route('admin.izin.index')->with('success', 'Status izin berhasil diperbarui.');
    }

    /**
     * Delete izin request (admin).
     */
    public function destroy($id)
    {
        $izin = Izin::findOrFail($id);

        // Delete file if exists
        if ($izin->bukti_path && Storage::disk('public')->exists($izin->bukti_path)) {
            Storage::disk('public')->delete($izin->bukti_path);
        }

        $izin->delete();

        return redirect()->route('admin.izin.index')->with('success', 'Permohonan izin berhasil dihapus.');
    }
}