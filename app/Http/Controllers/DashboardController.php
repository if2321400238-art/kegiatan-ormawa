<?php

namespace App\Http\Controllers;

use App\Models\PengajuanKegiatan;

use App\Models\VerifikasiBauak;
use App\Models\PersetujuanWarek3;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Route ke dashboard sesuai role
        switch ($user->role) {
            case 'ormawa':
                return $this->dashboardOrmawa();
            case 'bauak':
                return $this->dashboardBauak();
            case 'warek3':
                return $this->dashboardWarek3();
            case 'admin':
                return $this->dashboardAdmin();
            default:
                abort(403, 'Role tidak dikenali');
        }
    }

    private function dashboardOrmawa()
    {
        $ormawa = Auth::user()->ormawa;

        $stats = [
            'total_pengajuan' => PengajuanKegiatan::where('ormawa_id', $ormawa->id)->count(),
            'draft' => PengajuanKegiatan::where('ormawa_id', $ormawa->id)->where('status', 'draft')->count(),
            'menunggu_verifikasi' => PengajuanKegiatan::where('ormawa_id', $ormawa->id)
                ->whereIn(['menunggu_dosen', 'menunggu_dekan', 'menunggu_bauak', 'menunggu_warek3', 'menunggu_rektor'])
                ->count(),
            'disetujui' => PengajuanKegiatan::where('ormawa_id', $ormawa->id)
                ->where('status', 'disetujui')
                ->count(),
            'ditolak' => PengajuanKegiatan::where('ormawa_id', $ormawa->id)->where('status', 'ditolak')->count(),
            'revisi' => PengajuanKegiatan::where('ormawa_id', $ormawa->id)->whereIn(['revisi_dosen','revisi_dekan','revisi_bauak','revisi_warek3','revisi_rektor'])->count(),

        ];

        $recentPengajuan = PengajuanKegiatan::where('ormawa_id', $ormawa->id)
            ->latest()
            ->take(5)
            ->get();

        $upcomingEvents = PengajuanKegiatan::where('ormawa_id', $ormawa->id)
            ->where('status', 'disetujui_warek3')
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
            'total_disetujui' => PengajuanKegiatan::where('status', 'disetujui_bauak')->count(),
            'perlu_revisi' => PengajuanKegiatan::where('status', 'revisi_bauak')->count(),
            'total_pengajuan' => PengajuanKegiatan::count(),
            'pengajuan_ditolak' => PengajuanKegiatan::where('status', 'ditolak')->count(),
            'pengajuan_draft' => PengajuanKegiatan::where('status', 'draft')->count(),
        ];

        $pengajuanMenunggu = PengajuanKegiatan::with('ormawa')
            ->where('status', 'diajukan')
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
            'menunggu_approval' => PengajuanKegiatan::where('status', 'menunggu_warek3')->count(),
            'disetujui_hari_ini' => PersetujuanWarek3::whereDate('tanggal_acc', today())
                ->where('user_warek3_id', Auth::id())
                ->count(),
            'total_disetujui' => PengajuanKegiatan::where('status', 'disetujui_warek3')->count(),
            'ditolak' => PengajuanKegiatan::where('status', 'ditolak')->count(),
        ];

        // FIX: Use paginate() instead of get()
        $pengajuanMenunggu = PengajuanKegiatan::with(['ormawa', 'verifikasiBauak'])
            ->where('status', 'disetujui_bauak')
            ->latest()
            ->paginate(10);

        $riwayatPersetujuan = PersetujuanWarek3::with('pengajuanKegiatan.ormawa')
            ->where('user_warek3_id', Auth::id())
            ->latest()
            ->paginate(5);

        $statistikBulanan = $this->getMonthlyStatistics();

        return view('dashboard.warek3', compact('stats', 'pengajuanMenunggu', 'riwayatPersetujuan', 'statistikBulanan'));
    }



    private function dashboardAdmin()
    {
        // Main Statistics
        $stats = [
            'total_ormawa' => \App\Models\Ormawa::count(),
            'total_pengajuan' => PengajuanKegiatan::count(),

            'pengajuan_pending' => PengajuanKegiatan::whereIn('status', ['menunggu_dosen', 'menunggu_dekan', 'menunggu_bauak', 'menunggu_warek3', 'menunggu_rektor'])->count(),
            'pengajuan_disetujui' => PengajuanKegiatan::where('status', 'disetujui_warek3')->count(),
            'pengajuan_revisi' => PengajuanKegiatan::whereIn('status', ['revisi_dosen', 'revisi_dekan', 'revisi_bauak', 'revisi_warek3', 'revisi_rektor'])->count(),
            'pengajuan_ditolak' => PengajuanKegiatan::where('status', 'ditolak')->count(),

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
