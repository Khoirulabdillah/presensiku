<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!auth()->check() || auth()->user()->role !== $role) {
            // If request expects JSON (API/AJAX), keep returning 403
            if ($request->expectsJson() || $request->isXmlHttpRequest()) {
                abort(403, 'Unauthorized');
            }

            // For normal web requests, redirect back to home with an error message
            return redirect('/')->withErrors(['error' => 'Anda tidak memiliki akses ke halaman yang diminta.']);
        }

        return $next($request);
    }
}
