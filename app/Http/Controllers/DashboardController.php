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
            case 'dosen':
                return $this->dashboardDosen();
            case 'dekan':
                return $this->dashboardDekan();
            case 'bauak':
                return $this->dashboardBauak();
            case 'warek3':
                return $this->dashboardWarek3();
            case 'rektor':
                return $this->dashboardRektor();
            case 'pp':
                return $this->dashboardPp();
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
                ->whereIn('status', ['menunggu_dosen', 'menunggu_dekan', 'menunggu_bauak', 'menunggu_warek3', 'menunggu_rektor'])
                ->count(),
            'disetujui' => PengajuanKegiatan::where('ormawa_id', $ormawa->id)
                ->where('status', 'disetujui')
                ->count(),
            'ditolak' => PengajuanKegiatan::where('ormawa_id', $ormawa->id)->where('status', 'ditolak')->count(),
            'revisi' => PengajuanKegiatan::where('ormawa_id', $ormawa->id)->whereIn('status', ['revisi_dosen','revisi_dekan','revisi_bauak','revisi_warek3','revisi_rektor'])->count(),

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
            'pengajuan_ditolak' => PengajuanKegiatan::where('status', 'ditolak')->count(),
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
            'ditolak' => PengajuanKegiatan::where('status', 'ditolak')->count(),
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

    private function dashboardDosen()
    {
        // Dosen pembina melihat pengajuan Ormawa yang dia bina
        $stats = [
            'menunggu_persetujuan' => PengajuanKegiatan::whereIn('status', ['menunggu_dosen', 'revisi_dosen'])
                ->whereHas('ormawa', function ($query) {
                    $query->where('pembina', Auth::user()->nama);
                })
                ->count(),
            'disetujui' => PengajuanKegiatan::where('status', 'disetujui')
                ->whereHas('ormawa', function ($query) {
                    $query->where('pembina', Auth::user()->nama);
                })
                ->count(),
            'ditolak' => PengajuanKegiatan::where('status', 'ditolak')
                ->whereHas('ormawa', function ($query) {
                    $query->where('pembina', Auth::user()->nama);
                })
                ->count(),
            'revisi' => PengajuanKegiatan::where('status', 'revisi_dosen')
                ->whereHas('ormawa', function ($query) {
                    $query->where('pembina', Auth::user()->nama);
                })
                ->count(),
        ];

        $pengajuanMenunggu = PengajuanKegiatan::with('ormawa')
            ->whereIn('status', ['menunggu_dosen', 'revisi_dosen'])
            ->whereHas('ormawa', function ($query) {
                $query->where('pembina', Auth::user()->nama);
            })
            ->latest()
            ->paginate(10);

        return view('dashboard.dosen', compact('stats', 'pengajuanMenunggu'));
    }

    private function dashboardDekan()
    {
        // Dekan melihat pengajuan Ormawa tingkat fakultas di area mereka
        $stats = [
            'menunggu_persetujuan' => PengajuanKegiatan::where('status', 'menunggu_dekan')->count(),
            'disetujui_hari_ini' => PengajuanKegiatan::where('status', 'disetujui')
                ->whereDate('updated_at', today())
                ->count(),
            'total_disetujui' => PengajuanKegiatan::where('status', 'disetujui')->count(),
            'perlu_revisi' => PengajuanKegiatan::where('status', 'revisi_dekan')->count(),
        ];

        $pengajuanMenunggu = PengajuanKegiatan::with('ormawa')
            ->whereIn('status', ['menunggu_dekan', 'revisi_dekan'])
            ->latest()
            ->paginate(10);

        return view('dashboard.dekan', compact('stats', 'pengajuanMenunggu'));
    }

    private function dashboardRektor()
    {
        // Rektor melihat semua pengajuan yang menunggu persetujuan akhir
        $stats = [
            'menunggu_persetujuan' => PengajuanKegiatan::where('status', 'menunggu_rektor')->count(),
            'disetujui' => PengajuanKegiatan::where('status', 'disetujui')->count(),
            'ditolak' => PengajuanKegiatan::where('status', 'ditolak')->count(),
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
            'menunggu_persetujuan' => PengajuanKegiatan::whereIn('status', ['menunggu_dosen', 'menunggu_dekan', 'menunggu_bauak', 'menunggu_warek3', 'menunggu_rektor'])->count(),
            'disetujui' => PengajuanKegiatan::where('status', 'disetujui')->count(),
            'ditolak' => PengajuanKegiatan::where('status', 'ditolak')->count(),
            'perlu_revisi' => PengajuanKegiatan::whereIn('status', ['revisi_dosen', 'revisi_dekan', 'revisi_bauak', 'revisi_warek3', 'revisi_rektor'])->count(),
        ];

        $pengajuanTerbaru = PengajuanKegiatan::with('ormawa')
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

            'pengajuan_pending' => PengajuanKegiatan::whereIn('status', ['menunggu_dosen', 'menunggu_dekan', 'menunggu_bauak', 'menunggu_warek3', 'menunggu_rektor'])->count(),
            'pengajuan_disetujui' => PengajuanKegiatan::where('status', 'disetujui')->count(),
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
