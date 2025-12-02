<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use App\Models\User;
use App\Models\Divisi;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $divisi = Divisi::all();
        return view('auth.register', compact('divisi'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'nip' => ['required', 'string', 'max:25', 'unique:pegawai,nip'],
            'divisi_id' => ['required', 'integer', 'exists:divisi,id'],
            'nama_pegawai' => ['required', 'string', 'max:255'],
            'jabatan' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Create user first
        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
        ]);

        // Create pegawai and link to created user. Note: pegawai table uses `users_id` as FK.
        $pegawai = Pegawai::create([
            'nip' => $request->nip,
            'divisi_id' => $request->divisi_id,
            'users_id' => $user->id,
            'nama_pegawai' => $request->nama_pegawai,
            'jabatan' => $request->jabatan,
        ]);

        // Fire the standard Registered event for the new user
        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
