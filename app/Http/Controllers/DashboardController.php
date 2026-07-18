<?php

namespace App\Http\Controllers;

use App\Models\PengajuanKegiatan;
use App\Models\PersetujuanDekan;
use App\Models\PersetujuanKaprodi;
use App\Models\PersetujuanWarek3;
use App\Models\VerifikasiBauak;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Dashboard mengikuti role/permission RBAC aktif. Urutan ini menjadi
        // prioritas ketika user memiliki lebih dari satu role.
        $dashboards = [
            ['roles' => ['admin'], 'permissions' => ['rbac.manage', 'akademik.manage', 'mahasiswa.manage', 'ormawa.manage'], 'handler' => 'dashboardAdmin'],
            ['roles' => ['kaprodi'], 'permissions' => ['approval.kaprodi'], 'handler' => 'dashboardKaprodi'],
            ['roles' => ['dekan'], 'permissions' => ['approval.dekan'], 'handler' => 'dashboardDekan'],
            ['roles' => ['bauak'], 'permissions' => ['approval.bauak', 'lpj.verify'], 'handler' => 'dashboardBauak'],
            ['roles' => ['warek3'], 'permissions' => ['approval.warek3'], 'handler' => 'dashboardWarek3'],
            ['roles' => ['rektor'], 'permissions' => ['approval.rektor'], 'handler' => 'dashboardRektor'],
            ['roles' => ['pp'], 'permissions' => ['approval.pp'], 'handler' => 'dashboardPp'],
            ['roles' => ['ormawa'], 'permissions' => ['proposal.manage'], 'handler' => 'dashboardOrmawa'],
            ['roles' => ['mahasiswa'], 'permissions' => [], 'route' => 'mahasiswa.dashboard'],
        ];

        foreach ($dashboards as $dashboard) {
            if ($user->hasAnyRole($dashboard['roles']) || $this->hasAnyPermission($user, $dashboard['permissions'])) {
                if (isset($dashboard['route'])) {
                    return redirect()->route($dashboard['route']);
                }

                return $this->{$dashboard['handler']}();
            }
        }

        abort(403, 'Role atau permission dashboard tidak dikenali');
    }

    private function hasAnyPermission($user, array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($user->hasPermissionTo($permission)) {
                return true;
            }
        }

        return false;
    }

    private function dashboardOrmawa()
    {
        $ormawa = Auth::user()->ormawa;

        if (! $ormawa) {
            $stats = [
                'total_pengajuan' => 0,
                'draft' => 0,
                'menunggu_verifikasi' => 0,
                'disetujui' => 0,
                'ditolak' => 0,
                'revisi' => 0,
            ];
            $recentPengajuan = collect();
            $upcomingEvents = collect();

            return view('dashboard.ormawa', compact('stats', 'recentPengajuan', 'upcomingEvents'))->with('warning', 'Profil Ormawa Anda belum lengkap atau belum terhubung dengan data master Ormawa. Silakan hubungi Admin.');
        }

        $stats = [
            'total_pengajuan' => PengajuanKegiatan::where('ormawa_id', $ormawa->id)->count(),
            'draft' => PengajuanKegiatan::where('ormawa_id', $ormawa->id)->where('status', 'draft')->count(),
            'menunggu_verifikasi' => PengajuanKegiatan::where('ormawa_id', $ormawa->id)
                ->whereIn('status', ['menunggu_kaprodi', 'menunggu_dekan', 'menunggu_bauak', 'menunggu_warek3', 'menunggu_rektor', 'menunggu_pp'])
                ->count(),
            'disetujui' => PengajuanKegiatan::where('ormawa_id', $ormawa->id)
                ->where('status', 'disetujui')
                ->count(),
            'ditolak' => PengajuanKegiatan::where('ormawa_id', $ormawa->id)->ditolak()->count(),
            'revisi' => PengajuanKegiatan::where('ormawa_id', $ormawa->id)->whereIn('status', ['revisi_kaprodi', 'revisi_dekan', 'revisi_bauak', 'revisi_warek3', 'revisi_rektor'])->count(),

        ];

        $recentPengajuan = PengajuanKegiatan::where('ormawa_id', $ormawa->id)
            ->latest()
            ->take(5)
            ->get();

        $upcomingEvents = PengajuanKegiatan::where('ormawa_id', $ormawa->id)
            ->where('status', 'disetujui')
            ->where('tanggal_mulai', '>=', now())
            ->orderBy('tanggal_mulai')
            ->take(5)
            ->get();

        return view('dashboard.ormawa', compact('stats', 'recentPengajuan', 'upcomingEvents'));
    }

    private function dashboardBauak()
    {
        $stats = [
            'menunggu_verifikasi' => PengajuanKegiatan::where('status', 'menunggu_bauak')->count(),
            'diverifikasi_hari_ini' => VerifikasiBauak::whereDate('tanggal_verifikasi', today())
                ->where('user_bauak_id', Auth::id())
                ->count(),
            'total_disetujui' => PengajuanKegiatan::where('status', 'menunggu_warek3')->count(),
            'perlu_revisi' => PengajuanKegiatan::where('status', 'revisi_bauak')->count(),
            'total_pengajuan' => PengajuanKegiatan::count(),
            'pengajuan_ditolak' => PengajuanKegiatan::ditolak()->count(),
            'pengajuan_draft' => PengajuanKegiatan::where('status', 'draft')->count(),
        ];

        $pengajuanMenunggu = PengajuanKegiatan::with('ormawa')
            ->where('status', 'menunggu_bauak')
            ->latest()
            ->paginate(10);

        $riwayatVerifikasi = VerifikasiBauak::with('pengajuanKegiatan.ormawa')
            ->where('user_bauak_id', Auth::id())
            ->latest()
            ->paginate(5);

        return view('dashboard.bauak', compact(
            'stats',
            'pengajuanMenunggu',
            'riwayatVerifikasi'
        ));
    }

    private function dashboardWarek3()
    {
        $stats = [
            'menunggu_persetujuan' => PengajuanKegiatan::where('status', 'menunggu_warek3')->count(),
            'disetujui_hari_ini' => PersetujuanWarek3::whereDate('tanggal_acc', today())
                ->where('user_warek3_id', Auth::id())
                ->count(),
            'total_disetujui' => PengajuanKegiatan::where('status', 'disetujui')->count(),
            'ditolak' => PengajuanKegiatan::ditolak()->count(),
        ];

        // FIX: Use paginate() instead of get()
        $pengajuanMenunggu = PengajuanKegiatan::with(['ormawa', 'verifikasiBauak'])
            ->where('status', 'menunggu_warek3')
            ->latest()
            ->paginate(10);

        $riwayatPersetujuan = PersetujuanWarek3::with('pengajuanKegiatan.ormawa')
            ->where('user_warek3_id', Auth::id())
            ->latest()
            ->paginate(5);

        $statistikBulanan = $this->getMonthlyStatistics();

        return view('dashboard.warek3', compact('stats', 'pengajuanMenunggu', 'riwayatPersetujuan', 'statistikBulanan'));
    }

    private function dashboardDekan()
    {
        $fakultasId = Auth::user()->fakultas_id;

        // Dekan melihat pengajuan Ormawa tingkat fakultas di area mereka
        $stats = [
            'menunggu_persetujuan' => PengajuanKegiatan::where('status', 'menunggu_dekan')
                ->whereHas('ormawa', function ($query) use ($fakultasId) {
                    $query->where('fakultas_id', $fakultasId);
                })
                ->count(),
            'disetujui_hari_ini' => PersetujuanDekan::whereDate('tanggal_acc', today())
                ->where('user_dekan_id', Auth::id())
                ->count(),
            'total_disetujui' => PersetujuanDekan::where('status', 'disetujui')
                ->where('user_dekan_id', Auth::id())
                ->count(),
            'perlu_revisi' => PengajuanKegiatan::where('status', 'revisi_dekan')
                ->whereHas('ormawa', function ($query) use ($fakultasId) {
                    $query->where('fakultas_id', $fakultasId);
                })
                ->count(),
        ];

        $pengajuanMenunggu = PengajuanKegiatan::with('ormawa')
            ->whereIn('status', ['menunggu_dekan', 'revisi_dekan'])
            ->whereHas('ormawa', function ($query) use ($fakultasId) {
                $query->where('fakultas_id', $fakultasId);
            })
            ->latest()
            ->paginate(10);

        return view('dashboard.dekan', compact('stats', 'pengajuanMenunggu'));
    }

    private function dashboardKaprodi()
    {
        $user = Auth::user();
        $prodiId = $user->programStudiKaprodi?->kaprodi_user_id === $user->id ? $user->prodi_id : 0;
        $owned = fn ($query) => $query->whereHas('ormawa', fn ($q) => $q->where('prodi_id', $prodiId));

        $stats = [
            'menunggu_persetujuan' => PengajuanKegiatan::whereIn('status', ['menunggu_kaprodi', 'revisi_kaprodi'])->where($owned)->count(),
            'disetujui_hari_ini' => PersetujuanKaprodi::where('user_kaprodi_id', $user->id)->where('status', 'disetujui')->whereDate('tanggal_acc', today())->count(),
            'total_disetujui' => PersetujuanKaprodi::where('user_kaprodi_id', $user->id)->where('status', 'disetujui')->count(),
            'total_ormawa' => \App\Models\Ormawa::where('prodi_id', $prodiId)->count(),
        ];

        $pengajuanMenunggu = PengajuanKegiatan::with('ormawa')
            ->whereIn('status', ['menunggu_kaprodi', 'revisi_kaprodi'])
            ->where($owned)->latest()->paginate(5, ['*'], 'antrean_page');
        $riwayatPersetujuan = PersetujuanKaprodi::with('pengajuanKegiatan.ormawa')
            ->where('user_kaprodi_id', $user->id)->latest('tanggal_acc')->paginate(5, ['*'], 'riwayat_page');

        return view('dashboard.kaprodi', compact('stats', 'pengajuanMenunggu', 'riwayatPersetujuan'));
    }

    private function dashboardRektor()
    {
        // Rektor melihat semua pengajuan yang menunggu persetujuan akhir
        $stats = [
            'menunggu_persetujuan' => PengajuanKegiatan::where('status', 'menunggu_rektor')->count(),
            'disetujui' => PengajuanKegiatan::where('status', 'disetujui')->count(),
            'ditolak' => PengajuanKegiatan::ditolak()->count(),
            'perlu_revisi' => PengajuanKegiatan::where('status', 'revisi_rektor')->count(),
        ];

        $pengajuanMenunggu = PengajuanKegiatan::with('ormawa')
            ->whereIn('status', ['menunggu_rektor', 'revisi_rektor'])
            ->latest()
            ->paginate(10);

        $statistikBulanan = $this->getMonthlyStatistics();

        return view('dashboard.rektor', compact('stats', 'pengajuanMenunggu', 'statistikBulanan'));
    }

    private function dashboardPp()
    {
        // PP (Kepala/Wakil PP) melihat monitoring seluruh pengajuan
        $stats = [
            'total_pengajuan' => PengajuanKegiatan::count(),
            'menunggu_persetujuan' => PengajuanKegiatan::where('status', 'menunggu_pp')->count(),
            'disetujui' => PengajuanKegiatan::where('status', 'disetujui')->count(),
            'ditolak' => PengajuanKegiatan::ditolak()->count(),
            'perlu_revisi' => PengajuanKegiatan::whereIn('status', ['revisi_kaprodi', 'revisi_dekan', 'revisi_bauak', 'revisi_warek3', 'revisi_rektor'])->count(),
        ];

        $pengajuanTerbaru = PengajuanKegiatan::with('ormawa')
            ->where('status', 'menunggu_pp')
            ->latest()
            ->take(15)
            ->get();

        $statistikBulanan = $this->getMonthlyStatistics();

        return view('dashboard.pp', compact('stats', 'pengajuanTerbaru', 'statistikBulanan'));
    }

    private function dashboardAdmin()
    {
        // Main Statistics
        $stats = [
            'total_ormawa' => \App\Models\Ormawa::count(),
            'total_pengajuan' => PengajuanKegiatan::count(),

            'pengajuan_pending' => PengajuanKegiatan::whereIn('status', ['menunggu_kaprodi', 'menunggu_dekan', 'menunggu_bauak', 'menunggu_warek3', 'menunggu_rektor', 'menunggu_pp'])->count(),
            'pengajuan_disetujui' => PengajuanKegiatan::where('status', 'disetujui')->count(),
            'pengajuan_revisi' => PengajuanKegiatan::whereIn('status', ['revisi_kaprodi', 'revisi_dekan', 'revisi_bauak', 'revisi_warek3', 'revisi_rektor'])->count(),
            'pengajuan_ditolak' => PengajuanKegiatan::ditolak()->count(),

        ];

        $pengajuanTerbaru = PengajuanKegiatan::with('ormawa')
            ->latest()
            ->take(10)
            ->get();

        $ormawaAktif = \App\Models\Ormawa::withCount('pengajuanKegiatan')
            ->orderBy('pengajuan_kegiatan_count', 'desc')
            ->take(5)
            ->get();

        return view('dashboard.admin', compact(
            'stats',
            'pengajuanTerbaru',
            'ormawaAktif'
        ));
    }

    private function getMonthlyStatistics()
    {
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = [
                'bulan' => $date->format('M Y'),
                'pengajuan' => PengajuanKegiatan::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),

            ];
        }

        return $months;
    }
}
