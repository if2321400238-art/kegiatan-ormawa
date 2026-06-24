<?php

namespace App\Http\Controllers;

use App\Models\PengajuanKegiatan;

use App\Models\Ormawa;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    /**
     * Laporan untuk BAUAK
     */
    public function bauak(Request $request)
    {
        $tanggalMulai = $request->input('tanggal_mulai', now()->startOfMonth()->format('Y-m-d'));
        $tanggalSelesai = $request->input('tanggal_selesai', now()->endOfMonth()->format('Y-m-d'));

        $pengajuan = PengajuanKegiatan::with(['ormawa', 'verifikasiBauak'])
            ->whereBetween('created_at', [$tanggalMulai, $tanggalSelesai])
            ->get();

        $statistik = [
            'total' => $pengajuan->count(),
            'menunggu_dosen' => $pengajuan->where('status', 'menunggu_dosen')->count(),
            'disetujui' => $pengajuan->where('status', 'menunggu_warek3')->count(),
            'revisi' => $pengajuan->where('status', 'revisi_bauak')->count(),
            'ditolak' => $pengajuan->where('status', 'ditolak')->count(),
        ];

        // Group by Ormawa
        $perOrmawa = $pengajuan->groupBy('ormawa_id')->map(function($items) {
            return [
                'ormawa' => $items->first()->ormawa->nama_ormawa,
                'total' => $items->count(),
                'disetujui' => $items->where('status', 'menunggu_warek3')->count(),
            ];
        });

        if ($request->has('export')) {
            return $this->exportBauak($pengajuan, $statistik, $tanggalMulai, $tanggalSelesai);
        }

        return view('bauak.laporan', compact('pengajuan', 'statistik', 'perOrmawa', 'tanggalMulai', 'tanggalSelesai'));
    }


    /**
     * Laporan untuk Admin (Comprehensive)
     */
    public function admin(Request $request)
    {
        $tanggalMulai = $request->input('tanggal_mulai', now()->startOfMonth()->format('Y-m-d'));
        $tanggalSelesai = $request->input('tanggal_selesai', now()->endOfMonth()->format('Y-m-d'));

        // Statistik Pengajuan
        $pengajuan = PengajuanKegiatan::whereBetween('created_at', [$tanggalMulai, $tanggalSelesai])->get();
        $statistikPengajuan = [
            'total' => $pengajuan->count(),
            'disetujui' => $pengajuan->where('status', 'disetujui')->count(),
            'ditolak' => $pengajuan->where('status', 'ditolak')->count(),
            'pending' => $pengajuan->whereIn('status', ['menunggu_dosen', 'menunggu_warek3'])->count(),
        ];


        // Ormawa Teraktif
        $ormawaAktif = Ormawa::withCount([
            'pengajuanKegiatan' => function($q) use ($tanggalMulai, $tanggalSelesai) {
                $q->whereBetween('created_at', [$tanggalMulai, $tanggalSelesai]);
            }
        ])
        ->having('pengajuan_kegiatan_count', '>', 0)
        ->orderBy('pengajuan_kegiatan_count', 'desc')
        ->take(10)
        ->get();

        // Trend bulanan (6 bulan terakhir)
        $trendBulanan = $this->getTrendBulanan();

        if ($request->has('export')) {
            return $this->exportAdmin($statistikPengajuan, $ormawaAktif, $tanggalMulai, $tanggalSelesai);
        }

        return view('admin.laporan', compact(
            'statistikPengajuan',

            'ormawaAktif',
            'trendBulanan',
            'tanggalMulai',
            'tanggalSelesai'
        ));
    }

    /**
     * Export PDF BAUAK
     */
    private function exportBauak($pengajuan, $statistik, $tanggalMulai, $tanggalSelesai)
    {
        $pdf = Pdf::loadView('pdf.laporan-bauak', compact('pengajuan', 'statistik', 'tanggalMulai', 'tanggalSelesai'));
        return $pdf->download('laporan-bauak-' . date('Y-m-d') . '.pdf');
    }


    /**
     * Export PDF Admin
     */
    private function exportAdmin($statistikPengajuan, $ormawaAktif, $tanggalMulai, $tanggalSelesai)
    {
        $pdf = Pdf::loadView('pdf.laporan-admin', compact('statistikPengajuan', 'ormawaAktif', 'tanggalMulai', 'tanggalSelesai'));
        return $pdf->download('laporan-admin-' . date('Y-m-d') . '.pdf');
    }

    /**
     * Export Excel
     */
    public function export(Request $request)
    {
        $jenis = $request->input('jenis', 'pengajuan');
        $tanggalMulai = $request->input('tanggal_mulai', now()->startOfMonth()->format('Y-m-d'));
        $tanggalSelesai = $request->input('tanggal_selesai', now()->endOfMonth()->format('Y-m-d'));

        if ($jenis === 'pengajuan') {
            return $this->exportPengajuanExcel($tanggalMulai, $tanggalSelesai);
        }
    }

    private function exportPengajuanExcel($tanggalMulai, $tanggalSelesai)
    {
        $pengajuan = PengajuanKegiatan::with('ormawa')
            ->whereBetween('created_at', [$tanggalMulai, $tanggalSelesai])
            ->get();

        $filename = 'pengajuan-' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($pengajuan) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['No', 'Ormawa', 'Judul Kegiatan', 'Tanggal', 'Status', 'Dibuat']);

            foreach ($pengajuan as $index => $item) {
                fputcsv($file, [
                    $index + 1,
                    $item->ormawa->nama_ormawa,
                    $item->judul_kegiatan,
                    $item->tanggal_mulai->format('d/m/Y'),
                    $item->status_label,
                    $item->created_at->format('d/m/Y H:i'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }


    /**
     * Get trend bulanan
     */
    private function getTrendBulanan()
    {
        $trend = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $trend[] = [
                'bulan' => $date->format('M Y'),
                'pengajuan' => PengajuanKegiatan::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),

            ];
        }
        return $trend;
    }
}
