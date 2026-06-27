<?php

namespace App\Helpers;

use App\Models\User;
use App\Models\PengajuanKegiatan;
use Illuminate\Support\Facades\Auth;

/** @phpstan-ignore-next-line */
class PengajuanHelper
{
    public static function getOrmawa()
    {
        $user = Auth::user();

        return match ($user->role) {
            'ormawa'     => $user->ormawa,
            'mahasiswa'  => \App\Http\Controllers\MahasiswaDashboardController::getActiveOrmawa(),
            default      => null,
        };
    }

    public static function getOrmawaId()
    {
        return self::getOrmawa()?->id;
    }

    public static function applyRoleFilter($query)
    {
        $user = Auth::user();

        if ($user->role === 'ormawa') {
            $query->where('ormawa_id', $user->ormawa->id);
        }

        if ($user->role === 'mahasiswa') {
            $ormawa = self::getOrmawa();

            if ($ormawa) {
                $query->where('ormawa_id', $ormawa->id);
            }
        }

        return $query;
    }

    public static function applyFilters($query, $request)
    {
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('judul_kegiatan', 'like', "%{$search}%")
                    ->orWhere('ketua_pelaksana', 'like', "%{$search}%")
                    ->orWhere('lokasi_kegiatan', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal_mulai', '>=', $request->tanggal_dari);
        }

        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal_selesai', '<=', $request->tanggal_sampai);
        }

        return $query;
    }

    public static function getStats()
    {
        $query = PengajuanKegiatan::query();

        self::applyRoleFilter($query);

        $pending = [
            'menunggu_dosen',
            'menunggu_dekan',
            'menunggu_bauak',
            'menunggu_warek3',
            'menunggu_rektor'
        ];

        $revisi = [
            'revisi_dosen',
            'revisi_dekan',
            'revisi_bauak',
            'revisi_warek3',
            'revisi_rektor'
        ];

        return [
            'total'     => (clone $query)->count(),
            'draft'     => (clone $query)->where('status', 'draft')->count(),
            'pending'   => (clone $query)->whereIn('status', $pending)->count(),
            'approved'  => (clone $query)->where('status', 'disetujui')->count(),
            'rejected'  => (clone $query)->where('status', 'ditolak')->count(),
            'revision'  => (clone $query)->whereIn('status', $revisi)->count(),
        ];
    }

    public static function authorizePengajuan($pengajuan)
    {
        $user = Auth::user();

        if ($user->role === 'ormawa') {
            return $pengajuan->ormawa_id == $user->ormawa->id;
        }

        if ($user->role === 'mahasiswa') {
            return $pengajuan->ormawa_id == self::getOrmawaId();
        }

        return true;
    }

    public static function notifyRole(
        string $role,
        string $title,
        string $message,
        string $routeName,
        $pengajuan
    ) {
        $users = User::where('role', $role)
            ->where('is_active', true)
            ->get();

        foreach ($users as $user) {
            /** @phpstan-ignore-next-line */
            sendNotification(
                $user,
                $title,
                $message,
                'info',
                route($routeName, $pengajuan),
                ['telegram', 'email', 'in_app']
            );
        }
    }
}
