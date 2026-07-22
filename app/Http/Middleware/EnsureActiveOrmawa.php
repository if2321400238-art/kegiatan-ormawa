<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveOrmawa
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Verify user is mahasiswa
        if ($user && $user->role !== 'mahasiswa') {
            return $next($request);
        }

        // If no active ormawa is selected, redirect to dashboard
        if (!session('active_ormawa_id')) {
            return redirect()
                ->route('mahasiswa.dashboard')
                ->with('warning', 'Silakan pilih organisasi terlebih dahulu.');
        }

        // Verify that the active ormawa is still valid (user is still a member)
        if ($user && !$user->ormawas()
            ->where('ormawa_id', session('active_ormawa_id'))
            ->wherePivot('status', true)
            ->exists()) {
            session()->forget('active_ormawa_id');
            return redirect()
                ->route('mahasiswa.dashboard')
                ->with('error', 'Organisasi aktif tidak valid. Silakan pilih organisasi lain.');
        }

        return $next($request);
    }
}
