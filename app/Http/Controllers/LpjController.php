<?php

namespace App\Http\Controllers;

use App\Models\LaporanPertanggungjawaban;
use App\Models\LpjLampiran;
use App\Models\PengajuanKegiatan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LpjController extends Controller
{
    private const NOTIFICATION_CHANNELS = ['telegram', 'email', 'in_app'];

    public function index(Request $request)
    {
        $user = $request->user();

        if (in_array($user->role, ['ormawa', 'mahasiswa'])) {
            $ormawaId = $this->ormawaId($request);

            $query = PengajuanKegiatan::with(['ormawa', 'rab', 'lpj'])
                ->whereIn('status', ['disetujui', 'selesai'])
                ->latest('tanggal_selesai');

            if ($ormawaId) {
                $query->where('ormawa_id', $ormawaId);
            } else {
                // A user without an assigned/active organization has no LPJ data,
                // but should still be able to open the page and see its empty state.
                $query->whereRaw('1 = 0');
            }

            if ($request->filled('status')) {
                if ($request->status === 'belum_lpj') {
                    $query->doesntHave('lpj');
                } else {
                    $query->whereHas('lpj', fn ($lpj) => $lpj->where('status', $request->status));
                }
            }
            if ($request->filled('search')) {
                $query->where('judul_kegiatan', 'like', '%'.$request->string('search').'%');
            }

            return view('lpj.index', [
                'kegiatan' => $query->paginate(12)->withQueryString(),
                'ownerMode' => true,
                'hasOrganizationContext' => (bool) $ormawaId,
            ]);
        }

        $query = LaporanPertanggungjawaban::with('pengajuan.ormawa')->latest();

        if ($user->isDekan()) {
            $query->whereHas('pengajuan.ormawa', fn ($q) => $q->where('fakultas_id', $user->fakultas_id));
        } elseif ($user->isKaprodi()) {
            $query->whereHas('pengajuan.ormawa', fn ($q) => $q->where('prodi_id', $user->prodi_id));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->whereHas('pengajuan', fn ($q) => $q->where('judul_kegiatan', 'like', "%{$search}%")
                ->orWhereHas('ormawa', fn ($o) => $o->where('nama_ormawa', 'like', "%{$search}%")));
        }

        return view('lpj.index', [
            'lpjs' => $query->paginate(12)->withQueryString(),
            'ownerMode' => false,
            'hasOrganizationContext' => true,
        ]);
    }

    public function create(Request $request, PengajuanKegiatan $pengajuan)
    {
        $this->authorizeOwner($request, $pengajuan);
        abort_unless($pengajuan->status === 'disetujui', 422, 'LPJ hanya dapat dibuat untuk kegiatan yang telah disetujui.');
        abort_if($pengajuan->lpj()->exists(), 422, 'LPJ untuk kegiatan ini sudah tersedia.');
        $pengajuan->load('rab');

        return view('lpj.form', compact('pengajuan'));
    }

    public function store(Request $request, PengajuanKegiatan $pengajuan)
    {
        $this->authorizeOwner($request, $pengajuan);
        abort_unless($pengajuan->status === 'disetujui' && ! $pengajuan->lpj()->exists(), 422);
        $data = $this->validateData($request, true);

        $lpj = DB::transaction(function () use ($request, $pengajuan, $data) {
            $path = $request->file('file_laporan')->store('lpj/laporan', 'public');
            $lpj = LaporanPertanggungjawaban::create($this->payload($request, $pengajuan, $data, $path));
            $lpj->versiDokumen()->create([
                'versi' => 1,
                'nama_file' => $request->file('file_laporan')->getClientOriginalName(),
                'file_path' => $path,
                'uploaded_by' => $request->user()->id,
            ]);
            $this->syncItems($lpj, $data);
            $this->storeAttachments($request, $lpj);

            return $lpj;
        });

        $this->notifyIfSubmitted($lpj);

        return redirect()->route('lpj.show', $lpj)->with('success', 'LPJ berhasil '.($lpj->status === 'diajukan' ? 'diajukan ke BAUAK.' : 'disimpan sebagai draft.'));
    }

    public function edit(Request $request, LaporanPertanggungjawaban $lpj)
    {
        $this->authorizeOwner($request, $lpj->pengajuan);
        abort_unless(in_array($lpj->status, ['draft', 'revisi']), 422, 'LPJ yang sedang diverifikasi atau telah selesai tidak dapat diubah.');
        $lpj->load(['pengajuan.rab', 'realisasiAnggaran', 'lampiran']);

        return view('lpj.form', ['pengajuan' => $lpj->pengajuan, 'lpj' => $lpj]);
    }

    public function update(Request $request, LaporanPertanggungjawaban $lpj)
    {
        $this->authorizeOwner($request, $lpj->pengajuan);
        abort_unless(in_array($lpj->status, ['draft', 'revisi']), 422);
        $data = $this->validateData($request, false);

        DB::transaction(function () use ($request, $lpj, $data) {
            $path = $lpj->file_laporan;
            if ($request->hasFile('file_laporan')) {
                $path = $request->file('file_laporan')->store('lpj/laporan', 'public');
                $lpj->versiDokumen()->create([
                    'versi' => ($lpj->versiDokumen()->max('versi') ?? 0) + 1,
                    'nama_file' => $request->file('file_laporan')->getClientOriginalName(),
                    'file_path' => $path,
                    'uploaded_by' => $request->user()->id,
                ]);
            }
            $lpj->update($this->payload($request, $lpj->pengajuan, $data, $path));
            $this->syncItems($lpj, $data);
            $this->storeAttachments($request, $lpj);
        });

        $this->notifyIfSubmitted($lpj->refresh());

        return redirect()->route('lpj.show', $lpj)->with('success', 'LPJ berhasil diperbarui.');
    }

    public function show(Request $request, LaporanPertanggungjawaban $lpj)
    {
        $this->authorizeViewer($request, $lpj);
        $lpj->load(['pengajuan.ormawa', 'pengajuan.rab', 'realisasiAnggaran', 'lampiran', 'versiDokumen.pengunggah', 'riwayatVerifikasi.user', 'verifikator']);

        return view('lpj.show', compact('lpj'));
    }

    public function destroyAttachment(Request $request, LaporanPertanggungjawaban $lpj, LpjLampiran $lampiran)
    {
        $this->authorizeOwner($request, $lpj->pengajuan);
        abort_unless(in_array($lpj->status, ['draft', 'revisi']) && $lampiran->lpj_id === $lpj->id, 403);
        Storage::disk('public')->delete($lampiran->file_path);
        $lampiran->delete();

        return back()->with('success', 'Lampiran dihapus.');
    }

    private function validateData(Request $request, bool $create): array
    {
        return $request->validate([
            'ringkasan_pelaksanaan' => ['required', 'string', 'max:5000'], 'hasil_kegiatan' => ['required', 'string', 'max:5000'],
            'kendala' => ['nullable', 'string', 'max:5000'], 'tanggal_pelaksanaan_mulai' => ['required', 'date'],
            'tanggal_pelaksanaan_selesai' => ['required', 'date', 'after_or_equal:tanggal_pelaksanaan_mulai'],
            'jumlah_peserta' => ['required', 'integer', 'min:0'],
            'file_laporan' => [$create ? 'required' : 'nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
            'uraian' => ['required', 'array', 'min:1'], 'uraian.*' => ['required', 'string', 'max:255'],
            'anggaran_rencana' => ['required', 'array'], 'anggaran_rencana.*' => ['required', 'numeric', 'min:0'],
            'anggaran_realisasi' => ['required', 'array'], 'anggaran_realisasi.*' => ['required', 'numeric', 'min:0'],
            'keterangan' => ['nullable', 'array'], 'keterangan.*' => ['nullable', 'string', 'max:1000'],
            'dokumentasi.*' => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'bukti_transaksi.*' => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'lampiran_lainnya.*' => ['file', 'mimes:jpg,jpeg,png,pdf,doc,docx', 'max:5120'],
            'aksi' => ['required', 'in:draft,ajukan'],
        ]);
    }

    private function payload(Request $request, PengajuanKegiatan $pengajuan, array $data, string $path): array
    {
        $realisasi = collect($data['anggaran_realisasi'])->sum();
        $rencana = $pengajuan->rab?->total_anggaran ?? collect($data['anggaran_rencana'])->sum();

        return ['pengajuan_id' => $pengajuan->id, 'ringkasan_pelaksanaan' => $data['ringkasan_pelaksanaan'],
            'hasil_kegiatan' => $data['hasil_kegiatan'], 'kendala' => $data['kendala'] ?? null,
            'tanggal_pelaksanaan_mulai' => $data['tanggal_pelaksanaan_mulai'], 'tanggal_pelaksanaan_selesai' => $data['tanggal_pelaksanaan_selesai'],
            'jumlah_peserta' => $data['jumlah_peserta'], 'realisasi_anggaran' => $realisasi, 'sisa_anggaran' => $rencana - $realisasi,
            'file_laporan' => $path, 'status' => $data['aksi'] === 'ajukan' ? 'diajukan' : 'draft',
            'catatan_verifikator' => $data['aksi'] === 'ajukan' ? null : ($request->route('lpj')?->catatan_verifikator),
            'created_by' => $request->user()->id, 'submitted_at' => $data['aksi'] === 'ajukan' ? now() : null,
            'verified_by' => null, 'verified_at' => null];
    }

    private function syncItems(LaporanPertanggungjawaban $lpj, array $data): void
    {
        $lpj->realisasiAnggaran()->delete();
        foreach ($data['uraian'] as $i => $uraian) {
            $lpj->realisasiAnggaran()->create([
                'uraian' => $uraian, 'anggaran_rencana' => $data['anggaran_rencana'][$i],
                'anggaran_realisasi' => $data['anggaran_realisasi'][$i], 'keterangan' => $data['keterangan'][$i] ?? null]);
        }
    }

    private function storeAttachments(Request $request, LaporanPertanggungjawaban $lpj): void
    {
        foreach (['dokumentasi' => 'dokumentasi', 'bukti_transaksi' => 'bukti_transaksi', 'lampiran_lainnya' => 'lainnya'] as $field => $type) {
            foreach ($request->file($field, []) as $file) {
                $lpj->lampiran()->create([
                    'jenis' => $type, 'nama_file' => $file->getClientOriginalName(), 'file_path' => $file->store('lpj/lampiran', 'public')]);
            }
        }
    }

    private function notifyIfSubmitted(LaporanPertanggungjawaban $lpj): void
    {
        if ($lpj->status !== 'diajukan') {
            return;
        }

        $lpj->loadMissing('pengajuan.ormawa.user');
        $link = route('lpj.show', $lpj);

        foreach ($this->organizationRecipients($lpj) as $user) {
            sendNotification(
                $user,
                'LPJ Diajukan',
                "LPJ kegiatan '{$lpj->pengajuan->judul_kegiatan}' telah diajukan ke BAUAK dan berstatus Menunggu Verifikasi.",
                'info',
                $link,
                self::NOTIFICATION_CHANNELS
            );
        }

        foreach (User::where('role', User::ROLE_BAUAK)->where('is_active', true)->get() as $user) {
            sendNotification(
                $user,
                'LPJ Menunggu Verifikasi',
                "LPJ kegiatan '{$lpj->pengajuan->judul_kegiatan}' telah diajukan.",
                'info',
                $link,
                self::NOTIFICATION_CHANNELS
            );
        }
    }

    private function organizationRecipients(LaporanPertanggungjawaban $lpj)
    {
        $ormawa = $lpj->pengajuan->ormawa;

        return collect([$ormawa->user])
            ->merge($ormawa->users()
                ->wherePivot('status', true)
                ->where('users.role', User::ROLE_MAHASISWA)
                ->where('users.is_active', true)
                ->get())
            ->filter(fn ($user) => $user && $user->is_active)
            ->unique('id')
            ->values();
    }

    private function ormawaId(Request $request): ?int
    {
        if ($request->user()->isOrmawa()) {
            return $request->user()->ormawa?->id;
        }

        return MahasiswaDashboardController::getActiveOrmawa()?->id;
    }

    private function authorizeOwner(Request $request, PengajuanKegiatan $pengajuan): void
    {
        abort_unless(in_array($request->user()->role, ['ormawa', 'mahasiswa']) && $this->ormawaId($request) === $pengajuan->ormawa_id, 403);
    }

    private function authorizeViewer(Request $request, LaporanPertanggungjawaban $lpj): void
    {
        if (in_array($request->user()->role, ['ormawa', 'mahasiswa'])) {
            $this->authorizeOwner($request, $lpj->pengajuan);
        } else {
            $user = $request->user();
            abort_unless(in_array($user->role, ['bauak', 'kaprodi', 'dekan', 'warek3', 'rektor', 'pp', 'admin']), 403);
            if ($user->isDekan()) {
                abort_unless($lpj->pengajuan->ormawa->fakultas_id === $user->fakultas_id, 403);
            }
            if ($user->isKaprodi()) {
                abort_unless($lpj->pengajuan->ormawa->prodi_id === $user->prodi_id, 403);
            }
        }
    }
}
