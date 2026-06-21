<?php

namespace App\Http\Controllers;

use App\Models\PengajuanKegiatan;
use App\Models\VerifikasiBauak;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VerifikasiBauakController extends Controller
{
    public function index()
    {
        // Use paginate instead of get
        $pengajuanMenunggu = PengajuanKegiatan::with(['ormawa', 'proposal', 'rab'])
            ->where('status', 'diajukan')
            ->latest()
            ->paginate(10);

        $riwayatVerifikasi = VerifikasiBauak::with(['pengajuanKegiatan.ormawa', 'user'])
            ->where('user_bauak_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('bauak.verifikasi.index', compact('pengajuanMenunggu', 'riwayatVerifikasi'));
    }

    public function show(PengajuanKegiatan $pengajuan)
    {
        $pengajuan->load([
            'ormawa.user',
            'proposal',
            'rab',
            'suratRekomendasi',
            'verifikasiBauak.user'
        ]);

        return view('bauak.verifikasi.show', compact('pengajuan'));
    }

    public function verify(Request $request, PengajuanKegiatan $pengajuan)
    {
        $validated = $request->validate([
            'status' => 'required|in:disetujui,revisi,ditolak',
            'catatan' => 'required|string',
        ], [
            'status.required' => 'Status harus dipilih',
            'status.in' => 'Status tidak valid',
            'catatan.required' => 'Catatan harus diisi',
        ]);

        DB::beginTransaction();
        try {
            // Create verification record
            VerifikasiBauak::create([
                'pengajuan_id' => $pengajuan->id,
                'user_bauak_id' => auth()->id(),
                'catatan' => $validated['catatan'],
                'status' => $validated['status'],
                'tanggal_verifikasi' => now(),
            ]);

            // Update pengajuan status
            $newStatus = match($validated['status']) {
                'disetujui' => 'disetujui_bauak',
                'revisi' => 'revisi_bauak',
                'ditolak' => 'ditolak',
            };

            $pengajuan->update([
                'status' => $newStatus,
                'catatan' => $validated['catatan'],
            ]);

            // Update proposal & RAB status
            if ($validated['status'] === 'disetujui') {
                if ($pengajuan->proposal) {
                    $pengajuan->proposal()->update(['status' => 'final']);
                }
                if ($pengajuan->rab) {
                    $pengajuan->rab()->update(['status' => 'final']);
                }
            }

            // Send notification to Ormawa
            $this->notifyOrmawa($pengajuan, $validated['status'], $validated['catatan']);

            // If approved, notify Warek3
            if ($validated['status'] === 'disetujui') {
                $this->notifyWarek3($pengajuan);
            }

            DB::commit();

            $message = match($validated['status']) {
                'disetujui' => 'Pengajuan berhasil disetujui dan diteruskan ke Warek III',
                'revisi' => 'Pengajuan dikembalikan untuk revisi',
                'ditolak' => 'Pengajuan ditolak',
            };

            return redirect()
                ->route('bauak.verifikasi.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    private function notifyOrmawa($pengajuan, $status, $catatan)
    {
        $judul = match($status) {
            'disetujui' => '✅ Pengajuan Disetujui BAUAK',
            'revisi' => '⚠️ Pengajuan Perlu Revisi',
            'ditolak' => '❌ Pengajuan Ditolak',
        };

        $pesan = match($status) {
            'disetujui' => "Pengajuan kegiatan '{$pengajuan->judul_kegiatan}' telah disetujui BAUAK dan diteruskan ke Warek III untuk persetujuan akhir.",
            'revisi' => "Pengajuan kegiatan '{$pengajuan->judul_kegiatan}' perlu direvisi. Catatan: {$catatan}",
            'ditolak' => "Pengajuan kegiatan '{$pengajuan->judul_kegiatan}' ditolak. Alasan: {$catatan}",
        };

        $tipe = match($status) {
            'disetujui' => 'success',
            'revisi' => 'warning',
            'ditolak' => 'error',
        };

        sendNotification(
            $pengajuan->ormawa->user,
            $judul,
            $pesan,
            $tipe,
            route('pengajuan.show', $pengajuan),
            ['telegram', 'email', 'in_app']
        );
    }

    private function notifyWarek3($pengajuan)
    {
        // Get all Warek3 users
        $warek3Users = \App\Models\User::where('role', 'warek3')
            ->where('is_active', true)
            ->get();

        foreach ($warek3Users as $warek3) {
            sendNotification(
                $warek3,
                '📋 Pengajuan Siap Direview',
                "Pengajuan kegiatan '{$pengajuan->judul_kegiatan}' dari {$pengajuan->ormawa->nama_ormawa} telah diverifikasi BAUAK dan menunggu persetujuan Anda.",
                'info',
                route('warek3.persetujuan.show', $pengajuan),
                ['telegram', 'email', 'in_app']
            );
        }
    }
}
