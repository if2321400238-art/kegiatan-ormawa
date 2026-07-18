<?php

namespace App\Http\Controllers;

use App\Helpers\PengajuanHelper;
use App\Models\PengajuanKegiatan;
use App\Services\ExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class PengajuanKegiatanController extends Controller
{
    private const TEMP_UPLOAD_SESSION_KEY = 'pengajuan_temp_uploads';

    public function index(Request $request)
    {
        $query = PengajuanKegiatan::with([
            'proposal',
            'lpj',
            'rab'
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
            'lpj',
            'persetujuanKaprodi.user',
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
        $this->captureTemporaryUploads($request);
        $validated = $this->validatePengajuan($request, true);

        try {

            DB::beginTransaction();

            $ormawa = PengajuanHelper::getOrmawa();

            if (!$ormawa) {
                throw new \Exception(
                    'Silakan pilih organisasi terlebih dahulu.'
                );
            }

            $initialStatus = match (true) {
                $ormawa->isProdi() => 'menunggu_kaprodi',
                $ormawa->isFakultas() => 'menunggu_dekan',
                default => 'menunggu_bauak',
            };

            $pengajuan = PengajuanKegiatan::create([
                'ormawa_id' => $ormawa->id,
                'judul_kegiatan' => $validated['judul_kegiatan'],
                'tujuan_kegiatan' => $validated['tujuan_kegiatan'],
                'lokasi_kegiatan' => $validated['lokasi_kegiatan'],
                'tanggal_mulai' => $validated['tanggal_mulai'],
                'tanggal_selesai' => $validated['tanggal_selesai'],
                'ketua_pelaksana' => $validated['ketua_pelaksana'],
                'nama_pemohon' => $validated['nama_pemohon'],
                'status' => $initialStatus,
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

            $this->sendNotificationByStatus($initialStatus, $pengajuan);

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

        $validated = $this->validatePengajuan($request);

        try {
            DB::beginTransaction();

            $pengajuan->update(
                array_merge(
                    Arr::except($validated, [
                        'file_proposal',
                        'file_rab',
                        'temp_file_proposal',
                        'temp_file_rab',
                    ]),
                    [
                        'updated_by_user_id' => Auth::id()
                    ]
                )
            );

            $this->updateProposal($request, $pengajuan);
            $this->updateRab($request, $pengajuan);

            $nextStatus = match ($pengajuan->status) {
                'revisi_kaprodi',
                'draft',
                'ditolak_kaprodi',
                'ditolak_dekan',
                'ditolak_bauak',
                'ditolak_warek3',
                'ditolak_rektor',
                'ditolak_pp',
                'menunggu_kaprodi'
                => $this->initialStatusFor($pengajuan),

                'revisi_dekan'
                => 'menunggu_dekan',

                'revisi_bauak'
                => 'menunggu_bauak',

                'revisi_warek3'
                => 'menunggu_warek3',

                'revisi_rektor'
                => 'menunggu_rektor',

                default
                => $this->initialStatusFor($pengajuan),
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
        $proposalRequired = $isNew && !$this->hasTemporaryUpload($request, 'file_proposal');
        $rabRequired = $isNew && !$this->hasTemporaryUpload($request, 'file_rab');

        return $request->validate([
            'judul_kegiatan' => 'required|string|max:255',
            'tujuan_kegiatan' => 'required|string',
            'lokasi_kegiatan' => 'required|string|max:255',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'ketua_pelaksana' => 'required|string|max:255',
            'nama_pemohon' => 'required|string|max:255',
            'temp_file_proposal' => 'nullable|string',
            'temp_file_rab' => 'nullable|string',
            'file_proposal' => ($proposalRequired ? 'required' : 'nullable') . '|file|mimes:pdf,doc,docx',
            'file_rab' => ($rabRequired ? 'required' : 'nullable') . '|file|mimes:xls,xlsx,pdf',
        ]);
    }

    private function captureTemporaryUploads(Request $request): void
    {
        $fields = [
            'file_proposal' => 'file|mimes:pdf,doc,docx',
            'file_rab' => 'file|mimes:xls,xlsx,pdf',
        ];

        $uploads = session(self::TEMP_UPLOAD_SESSION_KEY, []);
        $tokens = [];

        foreach ($fields as $field => $rules) {
            if (!$request->hasFile($field)) {
                continue;
            }

            $request->validate([$field => $rules]);

            $file = $request->file($field);
            $token = (string) Str::uuid();
            $oldToken = $request->input("temp_{$field}");

            if (is_string($oldToken) && isset($uploads[$oldToken])) {
                Storage::disk('local')->delete($uploads[$oldToken]['path']);
                unset($uploads[$oldToken]);
            }

            $uploads[$token] = [
                'path' => $file->store('tmp/pengajuan', 'local'),
                'original_name' => $file->getClientOriginalName(),
            ];

            $tokens["temp_{$field}"] = $token;
        }

        if ($tokens !== []) {
            session()->put(self::TEMP_UPLOAD_SESSION_KEY, $uploads);
            $request->merge($tokens);
        }
    }

    private function hasTemporaryUpload(Request $request, string $field): bool
    {
        $token = $request->input("temp_{$field}");
        $uploads = session(self::TEMP_UPLOAD_SESSION_KEY, []);

        return is_string($token)
            && isset($uploads[$token]['path'])
            && Storage::disk('local')->exists($uploads[$token]['path']);
    }

    private function consumeTemporaryUpload(Request $request, string $field): ?array
    {
        $token = $request->input("temp_{$field}");
        $uploads = session(self::TEMP_UPLOAD_SESSION_KEY, []);

        if (!is_string($token) || !isset($uploads[$token])) {
            return null;
        }

        $upload = $uploads[$token];
        unset($uploads[$token]);
        session()->put(self::TEMP_UPLOAD_SESSION_KEY, $uploads);

        return $upload;
    }

    /**
     * Handle proposal file upload
     */
    private function handleProposalUpload(Request $request, PengajuanKegiatan $pengajuan, int $ormawaId): void
    {
        $temporaryUpload = $this->consumeTemporaryUpload($request, 'file_proposal');

        if (!$request->hasFile('file_proposal') && !$temporaryUpload) {
            return;
        }

        $extension = $temporaryUpload
            ? pathinfo($temporaryUpload['original_name'], PATHINFO_EXTENSION)
            : $request->file('file_proposal')->getClientOriginalExtension();

        $filename = 'proposal_' . $pengajuan->id . '_' . time() . '.' . $extension;
        $path = 'pengajuan/proposal/' . $filename;

        if ($temporaryUpload) {
            Storage::disk('public')->put($path, Storage::disk('local')->get($temporaryUpload['path']));
            Storage::disk('local')->delete($temporaryUpload['path']);
        } else {
            $request->file('file_proposal')->storeAs('pengajuan/proposal', $filename, 'public');
        }

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
        $temporaryUpload = $this->consumeTemporaryUpload($request, 'file_rab');

        if (!$request->hasFile('file_rab') && !$temporaryUpload) {
            return;
        }

        $extension = $temporaryUpload
            ? pathinfo($temporaryUpload['original_name'], PATHINFO_EXTENSION)
            : $request->file('file_rab')->getClientOriginalExtension();

        $filename = 'rab_' . $pengajuan->id . '_' . time() . '.' . $extension;
        $path = 'pengajuan/rab/' . $filename;

        if ($temporaryUpload) {
            Storage::disk('public')->put($path, Storage::disk('local')->get($temporaryUpload['path']));
            Storage::disk('local')->delete($temporaryUpload['path']);
        } else {
            $request->file('file_rab')->storeAs('pengajuan/rab', $filename, 'public');
        }

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
                Storage::disk('public')->delete($pengajuan->proposal->file_proposal);
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
                Storage::disk('public')->delete($pengajuan->rab->file_rab);
            }

            $this->handleRabUpload($request, $pengajuan, $pengajuan->ormawa_id);
        }
    }

    /**
     * Send notification based on status
     */
    private function sendNotificationByStatus(string $status, PengajuanKegiatan $pengajuan): void
    {
        $roleMap = [
            'menunggu_kaprodi' => ['role' => 'kaprodi', 'title' => 'Pengajuan Kegiatan Menunggu Persetujuan Kaprodi'],
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
            'kaprodi' => 'kaprodi.persetujuan.show',
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

    private function initialStatusFor(PengajuanKegiatan $pengajuan): string
    {
        return match (true) {
            $pengajuan->ormawa->isProdi() => 'menunggu_kaprodi',
            $pengajuan->ormawa->isFakultas() => 'menunggu_dekan',
            default => 'menunggu_bauak',
        };
    }
}
