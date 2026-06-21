<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckOrmawaComplete
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        // Only check for Ormawa users
        if ($user && $user->role === 'ormawa') {
            $ormawa = $user->ormawa;

            // Check if profile exists and has kop_surat
            if (!$ormawa) {
                // Create empty ormawa profile if not exists
                \App\Models\Ormawa::create([
                    'user_id' => $user->id,
                    'nama_ormawa' => '',
                    'ketua' => '',
                ]);

                return redirect()
                    ->route('profile.edit')
                    ->with('warning', 'Silakan lengkapi profil Ormawa Anda terlebih dahulu.');
            }

            // Check if required fields are filled
            if (empty($ormawa->nama_ormawa) || empty($ormawa->ketua)) {
                return redirect()
                    ->route('profile.edit')
                    ->with('warning', 'Silakan lengkapi nama Ormawa dan nama Ketua.');
            }

            // Kop surat is optional - allow access even without it
            // This allows Ormawa to submit pengajuan without uploading kop surat
            // If you want to make it mandatory, uncomment below:

            // if (empty($ormawa->kop_surat)) {
            //     return redirect()
            //         ->route('profile.edit')
            //         ->with('warning', 'Silakan upload Kop Surat terlebih dahulu.');
            // }

        }

        return $next($request);
    }
}
