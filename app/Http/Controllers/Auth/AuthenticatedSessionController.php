<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Pegawai;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();

        Log::info('Login attempt redirect check', ['user_id' => $user->id ?? null, 'role' => $user->role ?? null]);

        // Prefer explicit role match
        if ($user && isset($user->role)) {
            $role = strtolower($user->role);
            if ($role === 'admin') {
                Log::info('Redirecting to dashboard (admin)', ['user_id' => $user->id]);
                return redirect()->route('dashboard');
            }
            if ($role === 'pegawai') {
                Log::info('Redirecting to pegawai.home (role)', ['user_id' => $user->id]);
                return redirect()->route('pegawai.home');
            }
        }

        // If role is not set or doesn't match but user has a Pegawai record, treat as pegawai
        if ($user && Pegawai::where('users_id', $user->id)->exists()) {
            Log::info('Redirecting to pegawai.home (has Pegawai record)', ['user_id' => $user->id]);
            return redirect()->route('pegawai.home');
        }

        // Default fallback
        Log::info('Fallback redirect to dashboard', ['user_id' => $user->id ?? null]);
        return redirect()->route('dashboard');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
