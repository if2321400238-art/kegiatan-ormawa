<?php

namespace App\Http\Controllers;

use App\Models\PengajuanKegiatan;
use App\Models\PersetujuanDekan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PersetujuanDekanController extends Controller
{
    public function index(Request $request)
    {
        $query = PengajuanKegiatan::with(['ormawa', 'proposal', 'rab'])
            ->whereIn('status', ['menunggu_dekan', 'revisi_dekan']);

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

        return view('dekan.persetujuan.index', compact('pengajuanMenunggu'));
    }

    public function show(PengajuanKegiatan $pengajuan)
    {
        $pengajuan->load(['ormawa.user', 'proposal', 'rab', 'suratRekomendasi']);

        return view('dekan.persetujuan.show', compact('pengajuan'));
    }

    public function approve(Request $request, PengajuanKegiatan $pengajuan)
    {
        $validated = $request->validate([
            'catatan' => 'nullable|string',
        ]);

        if (!in_array($pengajuan->status, ['menunggu_dekan', 'revisi_dekan'])) {
            return back()->with('error', 'Pengajuan tidak dapat diproses pada status ini.');
        }

        DB::beginTransaction();
        try {
            PersetujuanDekan::create([
                'pengajuan_id' => $pengajuan->id,
                'user_dekan_id' => auth()->id(),
                'catatan' => $validated['catatan'] ?? null,
                'status' => 'disetujui',
                'tanggal_acc' => now(),
            ]);

            $pengajuan->update([
                'status' => 'menunggu_bauak',
                'catatan' => $validated['catatan'] ?? null,
            ]);

            $this->notifyOrmawa($pengajuan, 'disetujui', $validated['catatan'] ?? null);
            $this->notifyBauak($pengajuan);

            DB::commit();

            return redirect()->route('dekan.persetujuan.index')->with('success', 'Pengajuan berhasil disetujui dan diteruskan ke BAUAK.');
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

        if (!in_array($pengajuan->status, ['menunggu_dekan', 'revisi_dekan'])) {
            return back()->with('error', 'Pengajuan tidak dapat diproses pada status ini.');
        }

        DB::beginTransaction();
        try {
            PersetujuanDekan::create([
                'pengajuan_id' => $pengajuan->id,
                'user_dekan_id' => auth()->id(),
                'catatan' => $validated['catatan'],
                'status' => 'ditolak',
                'tanggal_acc' => now(),
            ]);

            $pengajuan->update([
                'status' => 'ditolak',
                'catatan' => $validated['catatan'],
            ]);

            $this->notifyOrmawa($pengajuan, 'ditolak', $validated['catatan']);

            DB::commit();

            return redirect()->route('dekan.persetujuan.index')->with('success', 'Pengajuan berhasil ditolak.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    private function notifyOrmawa(PengajuanKegiatan $pengajuan, string $status, ?string $catatan)
    {
        $judul = $status === 'disetujui'
            ? '✅ Pengajuan Disetujui Dekan'
            : '❌ Pengajuan Ditolak Dekan';

        $pesan = $status === 'disetujui'
            ? "Pengajuan kegiatan '{$pengajuan->judul_kegiatan}' telah disetujui Dekan dan diteruskan ke BAUAK."
            : "Pengajuan kegiatan '{$pengajuan->judul_kegiatan}' ditolak Dekan. Alasan: {$catatan}";

        sendNotification(
            $pengajuan->ormawa->user,
            $judul,
            $pesan,
            $status === 'ditolak' ? 'error' : 'success',
            route('pengajuan.show', $pengajuan),
            ['telegram', 'email', 'in_app']
        );
    }

    private function notifyBauak(PengajuanKegiatan $pengajuan)
    {
        $bauakUsers = \App\Models\User::where('role', 'bauak')->where('is_active', true)->get();

        foreach ($bauakUsers as $bauak) {
            sendNotification(
                $bauak,
                'Pengajuan Menunggu Verifikasi BAUAK',
                "Pengajuan kegiatan '{$pengajuan->judul_kegiatan}' dari {$pengajuan->ormawa->nama_ormawa} menunggu verifikasi BAUAK.",
                'info',
                route('bauak.verifikasi.show', $pengajuan),
                ['telegram', 'email', 'in_app']
            );
        }
    }
}
