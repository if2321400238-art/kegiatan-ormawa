<?php

namespace App\Http\Controllers;

use App\Models\PengajuanKegiatan;
use App\Models\PersetujuanRektor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PersetujuanRektorController extends Controller
{
    public function index(Request $request)
    {
        $query = PengajuanKegiatan::with(['ormawa', 'proposal', 'rab'])
            ->whereIn('status', ['menunggu_rektor', 'revisi_rektor']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('judul_kegiatan', 'like', "%{$search}%")
                    ->orWhereHas('ormawa', function ($query) use ($search) {
                        $query->where('nama_ormawa', 'like', "%{$search}%");
                    });
            });
        }

        $pengajuanMenunggu = $query->latest()->paginate(10);

        $riwayatVerifikasi = PersetujuanRektor::with(['pengajuanKegiatan.ormawa'])
            ->where('user_rektor_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('rektor.persetujuan.index', compact('pengajuanMenunggu', 'riwayatVerifikasi'));
    }

    public function show(PengajuanKegiatan $pengajuan)
    {
        $pengajuan->load(['ormawa.user', 'proposal', 'rab']);

        return view('rektor.persetujuan.show', compact('pengajuan'));
    }

    public function approve(Request $request, PengajuanKegiatan $pengajuan)
    {
        $validated = $request->validate([
            'catatan' => 'nullable|string',
        ]);

        if (!in_array($pengajuan->status, ['menunggu_rektor', 'revisi_rektor'])) {
            return back()->with('error', 'Pengajuan tidak dapat diproses pada status ini.');
        }

        DB::beginTransaction();
        try {
            PersetujuanRektor::create([
                'pengajuan_id' => $pengajuan->id,
                'user_rektor_id' => auth()->id(),
                'catatan' => $validated['catatan'] ?? null,
                'status' => 'disetujui',
                'tanggal_acc' => now(),
            ]);

            $pengajuan->update([
                'status' => 'menunggu_pp',
                'catatan' => $validated['catatan'] ?? null,
                'updated_by_user_id' => auth()->id(),
            ]);

            $this->notifyOrmawa($pengajuan, 'disetujui', $validated['catatan'] ?? null);
            $this->notifyPp($pengajuan);

            DB::commit();

            return redirect()->route('rektor.persetujuan.index')->with('success', 'Pengajuan disetujui Rektor dan diteruskan ke Kepala/Wakil PP.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, PengajuanKegiatan $pengajuan)
    {
        $validated = $request->validate([
            'catatan' => 'required|string',
        ]);

        if (!in_array($pengajuan->status, ['menunggu_rektor', 'revisi_rektor'])) {
            return back()->with('error', 'Pengajuan tidak dapat diproses pada status ini.');
        }

        DB::beginTransaction();
        try {
            PersetujuanRektor::create([
                'pengajuan_id' => $pengajuan->id,
                'user_rektor_id' => auth()->id(),
                'catatan' => $validated['catatan'],
                'status' => 'ditolak',
                'tanggal_acc' => now(),
            ]);

            $pengajuan->update([
                'status' => 'ditolak_rektor',
                'catatan' => $validated['catatan'],
                'updated_by_user_id' => auth()->id(),
            ]);

            DB::commit();

            return redirect()->route('rektor.persetujuan.index')->with('success', 'Pengajuan berhasil ditolak.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    private function notifyOrmawa(PengajuanKegiatan $pengajuan, string $status, ?string $catatan)
    {
        $judul = $status === 'disetujui'
            ? '✅ Pengajuan Disetujui Rektor'
            : '❌ Pengajuan Ditolak Rektor';

        $pesan = $status === 'disetujui'
            ? "Pengajuan kegiatan '{$pengajuan->judul_kegiatan}' telah disetujui Rektor dan diteruskan kepada Kepala/Wakil PP untuk persetujuan akhir."
            : "Pengajuan kegiatan '{$pengajuan->judul_kegiatan}' ditolak oleh Rektor. Alasan: {$catatan}";

        sendNotification(
            $pengajuan->ormawa->user,
            $judul,
            $pesan,
            $status === 'ditolak' ? 'error' : 'success',
            route('pengajuan.show', $pengajuan),
            ['telegram', 'email', 'in_app']
        );
    }

    private function notifyPp(PengajuanKegiatan $pengajuan): void
    {
        foreach (\App\Models\User::where('role', 'pp')->where('is_active', true)->get() as $user) {
            sendNotification(
                $user,
                'Pengajuan Menunggu Persetujuan Akhir',
                "Pengajuan kegiatan '{$pengajuan->judul_kegiatan}' telah disetujui Rektor dan menunggu keputusan Kepala/Wakil PP.",
                'info',
                route('pp.persetujuan.show', $pengajuan),
                ['telegram', 'email', 'in_app']
            );
        }
    }
}
