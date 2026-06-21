<?php

namespace App\Http\Middleware;

use App\Models\LogAktivitas;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogActivity
{
    /**
     * Log user activity
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->user()) {
            // Only log important actions
            $importantRoutes = [
                'pengajuan.store',
                'pengajuan.update',
                'bauak.verifikasi.verify',
                'warek3.persetujuan.approve',
            ];

            $routeName = $request->route()->getName();

            if (in_array($routeName, $importantRoutes)) {
                LogAktivitas::create([
                    'user_id' => $request->user()->id,
                    'aktivitas' => $this->getActivityName($routeName),
                    'modul' => $this->getModuleName($routeName),
                    'subjek_type' => $this->getSubjectType($routeName),
                    'subjek_id' => $request->route()->parameter('pengajuan'),
                    'deskripsi' => $this->getDescription($request),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            }
        }

        return $response;
    }

    private function getActivityName($routeName): string
    {
        $activities = [
            'pengajuan.store' => 'Mengajukan Kegiatan',
            'pengajuan.update' => 'Memperbarui Pengajuan',
            'bauak.verifikasi.verify' => 'Verifikasi Pengajuan',
            'warek3.persetujuan.approve' => 'Menyetujui Pengajuan',
        ];

        return $activities[$routeName] ?? 'Aktivitas';
    }

    private function getModuleName($routeName): string
    {
        if (str_contains($routeName, 'pengajuan')) return 'pengajuan';
        if (str_contains($routeName, 'verifikasi')) return 'verifikasi';
        if (str_contains($routeName, 'persetujuan')) return 'persetujuan';

        return 'sistem';
    }

    private function getSubjectType($routeName): string
    {
        return 'App\\Models\\PengajuanKegiatan';
    }

    private function getDescription($request): string
    {
        return $request->method() . ' ' . $request->path();
    }
}

// Register middleware in app/Http/Kernel.php or bootstrap/app.php (Laravel 11)
// protected $middlewareAliases = [
//     'role' => \App\Http\Middleware\RoleMiddleware::class,
//     'ormawa.complete' => \App\Http\Middleware\CheckOrmawaComplete::class,
//     'log.activity' => \App\Http\Middleware\LogActivity::class,
// ];
