<?php

namespace App\Http\Controllers;

use App\Helpers\PengajuanHelper;
use App\Models\PengajuanKegiatan;
use App\Models\SuratRekomendasi;
use App\Services\ExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PengajuanKegiatanController extends Controller
{
    public function index(Request $request)
    {
        $query = PengajuanKegiatan::with([
            'proposal',
            'rab',
            'suratRekomendasi'
        ]);

        PengajuanHelper::applyRoleFilter($query);
        PengajuanHelper::applyFilters($query, $request);

        $perPage = in_array(
            $request->per_page,
            [10, 25, 50, 100]
        ) ? $request->per_page : 10;

        $pengajuan = $query
            ->latest()
            ->paginate($perPage)
            ->appends($request->query());

        $stats = PengajuanHelper::getStats();

        return view(
            'pengajuan.index',
            compact('pengajuan', 'stats')
        );
    }

    public function create()
    {
        return view('pengajuan.create');
    }

    public function show(PengajuanKegiatan $pengajuan)
    {
        abort_unless(
            PengajuanHelper::authorizePengajuan($pengajuan),
            403
        );

        $pengajuan->load([
            'ormawa',
            'proposal',
            'rab',
            'suratRekomendasi',
            'verifikasiBauak.user',
            'persetujuanWarek3.user',
        ]);

        return view(
            'pengajuan.show',
            compact('pengajuan')
        );
    }

    public function edit(PengajuanKegiatan $pengajuan)
    {
        abort_unless(
            $pengajuan->canBeEditedBy(Auth::user()),
            403
        );

        return view(
            'pengajuan.edit',
            compact('pengajuan')
        );
    }

    public function store(Request $request)
    {
        try {

            $validated = $this->validatePengajuan($request, true);

            DB::beginTransaction();

            $ormawa = PengajuanHelper::getOrmawa();

            if (!$ormawa) {
                throw new \Exception(
                    'Silakan pilih organisasi terlebih dahulu.'
                );
            }

            $pengajuan = PengajuanKegiatan::create([
                'ormawa_id' => $ormawa->id,
                'judul_kegiatan' => $validated['judul_kegiatan'],
                'tujuan_kegiatan' => $validated['tujuan_kegiatan'],
                'lokasi_kegiatan' => $validated['lokasi_kegiatan'],
                'tempat_pesantren' => $validated['tempat_pesantren'],
                'tanggal_mulai' => $validated['tanggal_mulai'],
                'tanggal_selesai' => $validated['tanggal_selesai'],
                'ketua_pelaksana' => $validated['ketua_pelaksana'],
                'nama_pemohon' => $validated['nama_pemohon'],
                'status' => 'menunggu_dosen',
                'created_by_user_id' => Auth::id(),
            ]);

            $this->handleProposalUpload(
                $request,
                $pengajuan,
                $ormawa->id
            );

            $this->handleRabUpload(
                $request,
                $pengajuan,
                $ormawa->id
            );

            SuratRekomendasi::create([
                'pengajuan_id' => $pengajuan->id,
                'nomor_surat' => $this->generateNomorSurat(),
                'status' => 'draft',
            ]);

            PengajuanHelper::notifyRole(
                'dosen',
                'Pengajuan Kegiatan Menunggu Verifikasi Dosen Pembina',
                "Pengajuan kegiatan '{$pengajuan->judul_kegiatan}' dari {$pengajuan->ormawa->nama_ormawa} menunggu verifikasi Anda.",
                'dosen.verifikasi.show',
                $pengajuan
            );

            DB::commit();

            return redirect()
                ->route('pengajuan.show', $pengajuan)
                ->with(
                    'success',
                    'Pengajuan kegiatan berhasil dibuat.'
                );
        } catch (\Exception $e) {

            DB::rollBack();

            Log::error($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }

    public function update(
        Request $request,
        PengajuanKegiatan $pengajuan
    ) {
        abort_unless(
            $pengajuan->canBeEditedBy(Auth::user()),
            403
        );

        try {

            $validated = $this->validatePengajuan($request);

            DB::beginTransaction();

            $pengajuan->update(
                array_merge(
                    $validated,
                    [
                        'updated_by_user_id' => Auth::id()
                    ]
                )
            );

            $this->updateProposal($request, $pengajuan);
            $this->updateRab($request, $pengajuan);

            $nextStatus = match ($pengajuan->status) {
                'revisi_dosen',
                'draft',
                'ditolak',
                'menunggu_dosen'
                => 'menunggu_dosen',

                'revisi_dekan'
                => 'menunggu_dekan',

                'revisi_bauak'
                => 'menunggu_bauak',

                'revisi_warek3'
                => 'menunggu_warek3',

                'revisi_rektor'
                => 'menunggu_rektor',

                default
                => 'menunggu_dosen',
            };

            $pengajuan->update([
                'status' => $nextStatus,
                'catatan' => null
            ]);

            $this->sendNotificationByStatus(
                $nextStatus,
                $pengajuan
            );

            DB::commit();

            return redirect()
                ->route(
                    'pengajuan.show',
                    $pengajuan
                )
                ->with(
                    'success',
                    'Pengajuan berhasil diperbarui.'
                );
        } catch (\Exception $e) {

            DB::rollBack();

            Log::error($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }

    public function exportCSV(Request $request)
    {
        $ormawaId = PengajuanHelper::getOrmawaId();

        abort_if(!$ormawaId, 403);

        $query = PengajuanKegiatan::where(
            'ormawa_id',
            $ormawaId
        );

        PengajuanHelper::applyFilters(
            $query,
            $request
        );

        $pengajuan = $query->latest()->get();

        $headers = [
            'Judul Kegiatan',
            'Lokasi',
            'Tanggal Mulai',
            'Tanggal Selesai',
            'Ketua Pelaksana',
            'Status',
            'Dibuat'
        ];

        $data = $pengajuan->map(fn($item) => [
            $item->judul_kegiatan,
            $item->lokasi_kegiatan,
            $item->tanggal_mulai->format('Y-m-d'),
            $item->tanggal_selesai->format('Y-m-d'),
            $item->ketua_pelaksana,
            ExportService::getStatusLabel(
                $item->status
            ),
            $item->created_at->format('Y-m-d H:i'),
        ]);

        ExportService::toCSV(
            $headers,
            $data,
            'pengajuan-kegiatan-' .
                now()->format('Y-m-d')
        );
    }

    public function printView(Request $request)
    {
        $ormawaId = PengajuanHelper::getOrmawaId();

        abort_if(!$ormawaId, 403);

        $query = PengajuanKegiatan::where(
            'ormawa_id',
            $ormawaId
        );

        PengajuanHelper::applyFilters(
            $query,
            $request
        );

        $pengajuan = $query
            ->latest()
            ->get();

        return view(
            'pengajuan.print',
            compact('pengajuan')
        );
    }

    /**
     * Validate pengajuan data
     */
    private function validatePengajuan(Request $request, bool $isNew = false): array
    {
        return $request->validate([
            'judul_kegiatan' => 'required|string|max:255',
            'tujuan_kegiatan' => 'required|string',
            'lokasi_kegiatan' => 'required|string|max:255',
            'tempat_pesantren' => 'nullable|string|max:255',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
            'ketua_pelaksana' => 'required|string|max:255',
            'nama_pemohon' => 'required|string|max:255',
            'file_proposal' => $isNew ? 'required|file|mimes:pdf,doc,docx' : 'nullable|file|mimes:pdf,doc,docx',
            'file_rab' => $isNew ? 'required|file|mimes:xls,xlsx,pdf' : 'nullable|file|mimes:xls,xlsx,pdf',
        ]);
    }

    /**
     * Handle proposal file upload
     */
    private function handleProposalUpload(Request $request, PengajuanKegiatan $pengajuan, int $ormawaId): void
    {
        if (!$request->hasFile('file_proposal')) {
            return;
        }

        $file = $request->file('file_proposal');
        $filename = 'proposal_' . $pengajuan->id . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path = 'pengajuan/proposal/' . $filename;

        $file->storeAs('pengajuan/proposal', $filename, 'public');

        $pengajuan->proposal()->updateOrCreate(
            ['pengajuan_id' => $pengajuan->id],
            [
                'file_proposal' => $path,
            ]
        );
    }

    /**
     * Handle RAB file upload
     */
    private function handleRabUpload(Request $request, PengajuanKegiatan $pengajuan, int $ormawaId): void
    {
        if (!$request->hasFile('file_rab')) {
            return;
        }

        $file = $request->file('file_rab');
        $filename = 'rab_' . $pengajuan->id . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path = 'pengajuan/rab/' . $filename;

        $file->storeAs('pengajuan/rab', $filename, 'public');

        $pengajuan->rab()->updateOrCreate(
            ['pengajuan_id' => $pengajuan->id],
            [
                'file_rab' => $path,
            ]
        );
    }

    /**
     * Update proposal file
     */
    private function updateProposal(Request $request, PengajuanKegiatan $pengajuan): void
    {
        if ($request->hasFile('file_proposal')) {
            // Delete old file if exists
            if ($pengajuan->proposal?->file_proposal) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($pengajuan->proposal->file_proposal);
            }

            $this->handleProposalUpload($request, $pengajuan, $pengajuan->ormawa_id);
        }
    }

    /**
     * Update RAB file
     */
    private function updateRab(Request $request, PengajuanKegiatan $pengajuan): void
    {
        if ($request->hasFile('file_rab')) {
            // Delete old file if exists
            if ($pengajuan->rab?->file_rab) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($pengajuan->rab->file_rab);
            }

            $this->handleRabUpload($request, $pengajuan, $pengajuan->ormawa_id);
        }
    }

    /**
     * Generate nomor surat
     */
    private function generateNomorSurat(): string
    {
        $year = date('Y');
        $month = date('m');

        $latest = SuratRekomendasi::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count();

        $number = str_pad($latest + 1, 4, '0', STR_PAD_LEFT);

        return "{$number}/BAUAK-SR/{$month}/{$year}";
    }

    /**
     * Send notification based on status
     */
    private function sendNotificationByStatus(string $status, PengajuanKegiatan $pengajuan): void
    {
        $roleMap = [
            'menunggu_dosen' => ['role' => 'dosen', 'title' => 'Pengajuan Kegiatan Menunggu Verifikasi Dosen Pembina'],
            'menunggu_dekan' => ['role' => 'dekan', 'title' => 'Pengajuan Kegiatan Menunggu Persetujuan Dekan'],
            'menunggu_bauak' => ['role' => 'bauak', 'title' => 'Pengajuan Kegiatan Menunggu Verifikasi BAUAK'],
            'menunggu_warek3' => ['role' => 'warek3', 'title' => 'Pengajuan Kegiatan Menunggu Persetujuan Warek III'],
            'menunggu_rektor' => ['role' => 'rektor', 'title' => 'Pengajuan Kegiatan Menunggu Persetujuan Rektor'],
        ];

        if (!isset($roleMap[$status])) {
            return;
        }

        $config = $roleMap[$status];
        $message = "Pengajuan kegiatan '{$pengajuan->judul_kegiatan}' dari {$pengajuan->ormawa->nama_ormawa} {$status}.";
        $routeName = match ($config['role']) {
            'dosen' => 'dosen.verifikasi.show',
            'dekan' => 'dekan.persetujuan.show',
            'bauak' => 'bauak.verifikasi.show',
            'warek3' => 'warek3.persetujuan.show',
            'rektor' => 'rektor.persetujuan.show',
        };

        PengajuanHelper::notifyRole(
            $config['role'],
            $config['title'],
            $message,
            $routeName,
            $pengajuan
        );
    }
}
