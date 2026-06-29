<?php

namespace App\Http\Controllers;

use App\Models\PengajuanKegiatan;
use App\Models\PersetujuanPp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PersetujuanPpController extends Controller
{
    public function index(Request $request)
    {
        $query = PengajuanKegiatan::with('ormawa')->where('status', 'menunggu_pp');

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('judul_kegiatan', 'like', "%{$search}%")
                    ->orWhereHas('ormawa', fn ($ormawa) => $ormawa->where('nama_ormawa', 'like', "%{$search}%"));
            });
        }

        $pengajuanMenunggu = $query->latest()->paginate(10);
        $riwayatPersetujuan = PersetujuanPp::with('pengajuanKegiatan.ormawa')
            ->where('user_pp_id', auth()->id())->latest()->paginate(10);

        return view('pp.persetujuan.index', compact('pengajuanMenunggu', 'riwayatPersetujuan'));
    }

    public function show(PengajuanKegiatan $pengajuan)
    {
        abort_unless($pengajuan->status === 'menunggu_pp', 404);
        $pengajuan->load(['ormawa', 'proposal', 'rab', 'latestPersetujuanRektor.user']);

        return view('pp.persetujuan.show', compact('pengajuan'));
    }

    public function approve(Request $request, PengajuanKegiatan $pengajuan)
    {
        return $this->decide($request, $pengajuan, 'disetujui');
    }

    public function reject(Request $request, PengajuanKegiatan $pengajuan)
    {
        return $this->decide($request, $pengajuan, 'ditolak');
    }

    private function decide(Request $request, PengajuanKegiatan $pengajuan, string $decision)
    {
        abort_unless($pengajuan->status === 'menunggu_pp', 422);
        $validated = $request->validate([
            'catatan' => [$decision === 'ditolak' ? 'required' : 'nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($pengajuan, $decision, $validated) {
            PersetujuanPp::create([
                'pengajuan_id' => $pengajuan->id,
                'user_pp_id' => auth()->id(),
                'catatan' => $validated['catatan'] ?? null,
                'status' => $decision,
                'tanggal_acc' => now(),
            ]);

            $pengajuan->update([
                'status' => $decision === 'disetujui' ? 'disetujui' : 'ditolak_pp',
                'catatan' => $validated['catatan'] ?? null,
                'updated_by_user_id' => auth()->id(),
            ]);

            sendNotification(
                $pengajuan->ormawa->user,
                $decision === 'disetujui' ? '✅ Pengajuan Disetujui Kepala/Wakil PP' : '❌ Pengajuan Ditolak Kepala/Wakil PP',
                $decision === 'disetujui'
                    ? "Pengajuan kegiatan '{$pengajuan->judul_kegiatan}' telah mendapat persetujuan akhir Kepala/Wakil PP."
                    : "Pengajuan kegiatan '{$pengajuan->judul_kegiatan}' ditolak Kepala/Wakil PP. Alasan: {$validated['catatan']}",
                $decision === 'disetujui' ? 'success' : 'error',
                route('pengajuan.show', $pengajuan),
                ['telegram', 'email', 'in_app']
            );
        });

        return redirect()->route('pp.persetujuan.index')
            ->with('success', $decision === 'disetujui' ? 'Persetujuan akhir berhasil diberikan.' : 'Pengajuan berhasil ditolak.');
    }
}
