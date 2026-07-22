<?php

namespace App\Http\Controllers;

use App\Helpers\PengajuanHelper;
use App\Models\PengajuanKegiatan;
use App\Models\PersetujuanKaprodi;
use App\Models\Ormawa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PersetujuanKaprodiController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $prodiId = $user->programStudiKaprodi?->kaprodi_user_id === $user->id
            ? ($user->prodi_id ?? 0)
            : 0;
        $pengajuanMenunggu = PengajuanKegiatan::with('ormawa')
            ->whereIn('status', ['menunggu_kaprodi', 'revisi_kaprodi'])
            ->whereHas('ormawa', fn ($q) => $q->where('prodi_id', $prodiId))
            ->latest()->paginate(10, ['*'], 'antrean_page');

        $riwayatPersetujuan = PersetujuanKaprodi::with('pengajuanKegiatan.ormawa')
            ->where('user_kaprodi_id', $user->id)
            ->latest('tanggal_acc')
            ->paginate(10, ['*'], 'riwayat_page');

        return view('kaprodi.persetujuan.index', compact('pengajuanMenunggu', 'riwayatPersetujuan'));
    }

    public function ormawaIndex(Request $request)
    {
        $prodiId = $this->authorizedProdiId();
        $ormawa = Ormawa::withCount('pengajuanKegiatan')
            ->where('prodi_id', $prodiId)
            ->when($request->filled('search'), fn ($q) => $q->where('nama_ormawa', 'like', '%'.$request->search.'%'))
            ->orderBy('nama_ormawa')
            ->paginate(10)
            ->withQueryString();

        return view('kaprodi.ormawa.index', compact('ormawa'));
    }

    public function ormawaShow(Ormawa $ormawa)
    {
        abort_unless($ormawa->prodi_id === $this->authorizedProdiId(), 403);
        $ormawa->load(['programStudi', 'user']);
        $pengajuan = $ormawa->pengajuanKegiatan()->latest()->paginate(10);

        return view('kaprodi.ormawa.show', compact('ormawa', 'pengajuan'));
    }

    public function show(PengajuanKegiatan $pengajuan)
    {
        $pengajuan->load(['ormawa', 'proposal', 'rab']);
        $this->authorizeProdi($pengajuan);
        return view('kaprodi.persetujuan.show', compact('pengajuan'));
    }

    public function decide(Request $request, PengajuanKegiatan $pengajuan)
    {
        $this->authorizeProdi($pengajuan);
        abort_unless(in_array($pengajuan->status, ['menunggu_kaprodi', 'revisi_kaprodi']), 422);

        $validated = $request->validate([
            'status' => 'required|in:disetujui,revisi,ditolak',
            'catatan' => 'nullable|required_unless:status,disetujui|string',
        ]);

        DB::transaction(function () use ($pengajuan, $validated) {
            PersetujuanKaprodi::create([
                'pengajuan_id' => $pengajuan->id,
                'user_kaprodi_id' => auth()->id(),
                'status' => $validated['status'],
                'catatan' => $validated['catatan'] ?? null,
                'tanggal_acc' => now(),
            ]);

            $nextStatus = match ($validated['status']) {
                'disetujui' => 'menunggu_dekan',
                'revisi' => 'revisi_kaprodi',
                'ditolak' => 'ditolak_kaprodi',
            };
            $pengajuan->update(['status' => $nextStatus, 'catatan' => $validated['catatan'] ?? null, 'updated_by_user_id' => auth()->id()]);

            if ($nextStatus === 'menunggu_dekan') {
                PengajuanHelper::notifyRole('dekan', 'Pengajuan Menunggu Persetujuan Dekan',
                    "Pengajuan '{$pengajuan->judul_kegiatan}' telah disetujui Kaprodi.", 'dekan.persetujuan.show', $pengajuan);
            }
        });

        $this->notifyPemohon($pengajuan->fresh(), $validated['status'], $validated['catatan'] ?? null);

        return redirect()->route('kaprodi.persetujuan.index')->with('success', 'Keputusan Kaprodi berhasil disimpan.');
    }

    private function notifyPemohon(PengajuanKegiatan $pengajuan, string $status, ?string $catatan): void
    {
        $judul = match ($status) {
            'disetujui' => '✅ Pengajuan Disetujui Kaprodi',
            'revisi' => '📝 Pengajuan Perlu Direvisi',
            'ditolak' => '❌ Pengajuan Ditolak Kaprodi',
        };

        $pesan = match ($status) {
            'disetujui' => "Pengajuan kegiatan '{$pengajuan->judul_kegiatan}' telah disetujui Kaprodi dan diteruskan ke Dekan.",
            'revisi' => "Pengajuan kegiatan '{$pengajuan->judul_kegiatan}' perlu direvisi. Catatan Kaprodi: {$catatan}",
            'ditolak' => "Pengajuan kegiatan '{$pengajuan->judul_kegiatan}' ditolak Kaprodi. Alasan: {$catatan}",
        };

        $user = $pengajuan->ormawa->user;
        if (! $user) {
            return;
        }

        sendNotification(
            $user,
            $judul,
            $pesan,
            $status === 'disetujui' ? 'success' : ($status === 'revisi' ? 'warning' : 'error'),
            route('pengajuan.show', $pengajuan),
            ['telegram', 'email', 'in_app']
        );
    }

    private function authorizeProdi(PengajuanKegiatan $pengajuan): void
    {
        $user = auth()->user();
        abort_unless(
            $user->prodi_id !== null
            && $pengajuan->ormawa->prodi_id === $user->prodi_id
            && $user->programStudiKaprodi?->kaprodi_user_id === $user->id,
            403,
            'Anda hanya dapat memproses pengajuan dari program studi di bawah naungan Anda.'
        );
    }

    private function authorizedProdiId(): int
    {
        $user = auth()->user();
        abort_unless(
            $user->prodi_id !== null && $user->programStudiKaprodi?->kaprodi_user_id === $user->id,
            403,
            'Akun Kaprodi Anda belum terhubung dengan program studi yang valid.'
        );

        return (int) $user->prodi_id;
    }
}
