<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use App\Models\Divisi;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PegawaiController extends Controller
{
    /**
     * Display a listing of the pegawai for admin.
     */
    public function index()
    {
        $pegawai = Pegawai::with(['divisi', 'user'])->paginate(10);

        return view('admin.pegawai', compact('pegawai'));
    }

    /**
     * Show the registration form for creating a new pegawai (admin side).
     */
    public function showRegistrationForm()
    {
        $divisi = Divisi::all();

        return view('auth.register', compact('divisi'));
    }

    // Method to handle the registration form submission
    public function storePegawaiRegistration(Request $request)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username',
            'nip' => 'required|unique:pegawai,nip|max:20',
            'nama_pegawai' => 'required|string|max:100',
            'jabatan' => 'required|string|max:50',
            'divisi_id' => 'required|exists:divisi,id',
            'password' => 'required|confirmed|min:6',
        ]);

        // Create User (User model casts password to hashed)
        $user = User::create([
            'name' => $validatedData['name'],
            'username' => $validatedData['username'],
            'password' => $validatedData['password'],
            'role' => 'pegawai',
        ]);

        // Create Pegawai linked to user
        $pegawai = Pegawai::create([
            'divisi_id' => $validatedData['divisi_id'],
            'users_id' => $user->id,
            'nip' => $validatedData['nip'],
            'nama_pegawai' => $validatedData['nama_pegawai'],
            'jabatan' => $validatedData['jabatan'],
        ]);

        return redirect()->route('admin.pegawai.index')->with('success', 'Pegawai registered successfully.');
    }

    /**
     * Show the form to create a new Pegawai (admin CRUD).
     */
    public function create()
    {
        $divisi = Divisi::all();

        return view('admin.pegawai-create', compact('divisi'));
    }

    /**
     * Store a newly created Pegawai and its User.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username',
            'password' => 'required|confirmed|min:6',
            'nip' => 'required|string|unique:pegawai,nip|max:20',
            'nama_pegawai' => 'required|string|max:100',
            'jabatan' => 'required|string|max:50',
            'divisi_id' => 'required|exists:divisi,id',
            'foto_wajah_asli' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'password' => $validated['password'],
            'role' => 'pegawai',
        ]);

        $pegawai = Pegawai::create([
            'divisi_id' => $validated['divisi_id'],
            'users_id' => $user->id,
            'nip' => $validated['nip'],
            'nama_pegawai' => $validated['nama_pegawai'],
            'jabatan' => $validated['jabatan'],
            'foto_wajah_asli' => null,
        ]);

        if ($request->hasFile('foto_wajah_asli')) {
            $path = $request->file('foto_wajah_asli')->store('pegawai', 'public');
            $pegawai->update(['foto_wajah_asli' => $path]);
        }

        return redirect()->route('admin.pegawai.index')->with('success', 'Pegawai berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($nip)
    {
        $pegawai = Pegawai::findOrFail($nip);
        $divisi = Divisi::all();

        return view('admin.pegawai-edit', compact('pegawai', 'divisi'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $nip)
    {
        $pegawai = Pegawai::findOrFail($nip);

        $validated = $request->validate([
            'nip' => "required|string|max:20|unique:pegawai,nip,{$pegawai->nip},nip",
            'nama_pegawai' => 'required|string|max:100',
            'jabatan' => 'required|string|max:50',
            'divisi_id' => 'required|exists:divisi,id',
            'password' => 'nullable|confirmed|min:6',
            'foto_wajah_asli' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ]);

        // Update password if provided
        if (!empty($validated['password'])) {
            $user = User::find($pegawai->users_id);
            if ($user) {
                $user->update(['password' => $validated['password']]);
            }
        }

        // If NIP changed, we need to update primary key field
        if ($validated['nip'] !== $pegawai->nip) {
            // create new record with new primary key and delete old one
            $oldUsersId = $pegawai->users_id;
            $oldFoto = $pegawai->foto_wajah_asli;
            $pegawai->delete();

            $newData = [
                'divisi_id' => $validated['divisi_id'],
                'users_id' => $oldUsersId,
                'nip' => $validated['nip'],
                'nama_pegawai' => $validated['nama_pegawai'],
                'jabatan' => $validated['jabatan'],
                'foto_wajah_asli' => null,
            ];

            if ($request->hasFile('foto_wajah_asli')) {
                $path = $request->file('foto_wajah_asli')->store('pegawai', 'public');
                $newData['foto_wajah_asli'] = $path;
                if (!empty($oldFoto)) {
                    Storage::disk('public')->delete($oldFoto);
                }
            } else {
                $newData['foto_wajah_asli'] = $oldFoto;
            }

            Pegawai::create($newData);
        } else {
            $updateData = [
                'divisi_id' => $validated['divisi_id'],
                'nama_pegawai' => $validated['nama_pegawai'],
                'jabatan' => $validated['jabatan'],
            ];

            if ($request->hasFile('foto_wajah_asli')) {
                $path = $request->file('foto_wajah_asli')->store('pegawai', 'public');
                if (!empty($pegawai->foto_wajah_asli)) {
                    Storage::disk('public')->delete($pegawai->foto_wajah_asli);
                }
                $updateData['foto_wajah_asli'] = $path;
            }

            $pegawai->update($updateData);
        }

        return redirect()->route('admin.pegawai.index')->with('success', 'Pegawai berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($nip)
    {
        $pegawai = Pegawai::findOrFail($nip);
        $pegawai->delete();

        return redirect()->route('admin.pegawai.index')->with('success', 'Pegawai berhasil dihapus.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
