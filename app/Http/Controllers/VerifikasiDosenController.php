<?php

namespace App\Http\Controllers;

use App\Models\PengajuanKegiatan;
use App\Models\User;
use App\Models\VerifikasiDosen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VerifikasiDosenController extends Controller
{
    public function index(Request $request)
    {
        // Hanya tampilkan pengajuan untuk Ormawa yang dibina oleh dosen saat ini
        $query = PengajuanKegiatan::with(['ormawa', 'proposal', 'rab'])
            ->whereIn('status', ['menunggu_dosen', 'revisi_dosen'])
            ->whereHas('ormawa', function ($q) {
                $q->where(function ($q2) {
                    $q2->where('pembina_user_id', auth()->id())
                        ->orWhere('pembina', auth()->user()->nama);
                });
            });

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

        $riwayatVerifikasi = VerifikasiDosen::with(['pengajuanKegiatan.ormawa'])
            ->where('user_dosen_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('dosen.verifikasi.index', compact('pengajuanMenunggu', 'riwayatVerifikasi'));
    }

    public function show(PengajuanKegiatan $pengajuan)
    {
        $pengajuan->load(['ormawa.user', 'proposal', 'rab', 'suratRekomendasi']);

        // Pastikan dosen hanya dapat melihat pengajuan Ormawa yang dia bina
        $pembinaUserId = $pengajuan->ormawa->pembina_user_id ?? null;
        if ($pembinaUserId) {
            if ($pembinaUserId !== auth()->id()) {
                abort(403, 'Anda tidak berwenang mengakses pengajuan ini.');
            }
        } else {
            if ((($pengajuan->ormawa->pembina ?? null) !== auth()->user()->nama)) {
                abort(403, 'Anda tidak berwenang mengakses pengajuan ini.');
            }
        }

        return view('dosen.verifikasi.show', compact('pengajuan'));
    }

    public function verify(Request $request, PengajuanKegiatan $pengajuan)
    {
        // Guard: hanya pembina Ormawa terkait yang boleh memverifikasi
        $pembinaUserId = $pengajuan->ormawa->pembina_user_id ?? null;
        if ($pembinaUserId) {
            if ($pembinaUserId !== auth()->id()) {
                return back()->with('error', 'Anda tidak berwenang memverifikasi pengajuan ini.');
            }
        } else {
            if ((($pengajuan->ormawa->pembina ?? null) !== auth()->user()->nama)) {
                return back()->with('error', 'Anda tidak berwenang memverifikasi pengajuan ini.');
            }
        }

        $validated = $request->validate([
            'status' => 'required|in:disetujui,revisi,ditolak',
            'catatan' => 'nullable|string',
        ], [
            'status.required' => 'Status harus dipilih',
            'status.in' => 'Status tidak valid',
        ]);

        DB::beginTransaction();
        try {
            VerifikasiDosen::create([
                'pengajuan_id' => $pengajuan->id,
                'user_dosen_id' => auth()->id(),
                'catatan' => $validated['catatan'],
                'status' => $validated['status'],
                'tanggal_verifikasi' => now(),
            ]);

            $newStatus = match ($validated['status']) {
                'disetujui' => $pengajuan->ormawa->isFakultas() ? 'menunggu_dekan' : 'menunggu_bauak',
                'revisi' => 'revisi_dosen',
                'ditolak' => 'ditolak',
            };

            $pengajuan->update([
                'status' => $newStatus,
                'catatan' => $validated['catatan'],
                'updated_by_user_id' => auth()->id(),
            ]);

            $this->notifyOrmawa($pengajuan, $validated['status'], $validated['catatan']);

            if ($validated['status'] === 'disetujui') {
                if ($pengajuan->ormawa->isFakultas()) {
                    $this->notifyDekan($pengajuan);
                } else {
                    $this->notifyBauak($pengajuan);
                }
            }

            DB::commit();

            $message = match ($validated['status']) {
                'disetujui' => 'Pengajuan berhasil disetujui Dosen Pembina dan diteruskan ke tahap berikutnya.',
                'revisi' => 'Pengajuan dikembalikan ke Ormawa untuk revisi.',
                'ditolak' => 'Pengajuan ditolak oleh Dosen Pembina.',
            };

            return redirect()->route('dosen.verifikasi.index')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    private function notifyOrmawa(PengajuanKegiatan $pengajuan, string $status, string $catatan)
    {
        $judul = match ($status) {
            'disetujui' => '✅ Pengajuan Disetujui Dosen Pembina',
            'revisi' => '⚠️ Pengajuan Perlu Revisi Dosen Pembina',
            'ditolak' => '❌ Pengajuan Ditolak Dosen Pembina',
        };

        $pesan = match ($status) {
            'disetujui' => "Pengajuan kegiatan '{$pengajuan->judul_kegiatan}' telah disetujui Dosen Pembina.",
            'revisi' => "Pengajuan kegiatan '{$pengajuan->judul_kegiatan}' perlu direvisi. Catatan: {$catatan}",
            'ditolak' => "Pengajuan kegiatan '{$pengajuan->judul_kegiatan}' ditolak. Alasan: {$catatan}",
        };

        sendNotification(
            $pengajuan->ormawa->user,
            $judul,
            $pesan,
            $status === 'ditolak' ? 'error' : ($status === 'revisi' ? 'warning' : 'success'),
            route('pengajuan.show', $pengajuan),
            ['telegram', 'email', 'in_app']
        );
    }

    private function notifyDekan(PengajuanKegiatan $pengajuan)
    {
        $message = "Pengajuan kegiatan '{$pengajuan->judul_kegiatan}' dari {$pengajuan->ormawa->nama_ormawa} telah disetujui Dosen Pembina dan menunggu persetujuan Dekan.";

        $dekan = $pengajuan->ormawa->fakultas?->dekan;

        if ($dekan && $dekan->is_active) {
            sendNotification(
                $dekan,
                'Pengajuan Menunggu Persetujuan Dekan',
                $message,
                'info',
                route('dekan.persetujuan.show', $pengajuan),
                ['telegram', 'email', 'in_app']
            );
        }
    }

    private function notifyBauak(PengajuanKegiatan $pengajuan)
    {
        $bauakUsers = \App\Models\User::where('role', 'bauak')->where('is_active', true)->get();

        foreach ($bauakUsers as $bauak) {
            sendNotification(
                $bauak,
                'Pengajuan Menunggu Verifikasi BAUAK',
                "Pengajuan kegiatan '{$pengajuan->judul_kegiatan}' dari {$pengajuan->ormawa->nama_ormawa} telah disetujui Dosen Pembina dan menunggu verifikasi BAUAK.",
                'info',
                route('bauak.verifikasi.show', $pengajuan),
                ['telegram', 'email', 'in_app']
            );
        }
    }
}
