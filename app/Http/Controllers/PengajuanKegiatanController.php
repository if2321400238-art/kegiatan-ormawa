<?php

namespace App\Http\Controllers;

use App\Models\PengajuanKegiatan;
use App\Models\Proposal;
use App\Models\Rab;
use App\Models\SuratRekomendasi;
use App\Services\ExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PengajuanKegiatanController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = PengajuanKegiatan::with(['proposal', 'rab', 'suratRekomendasi']);

        // Filter by role
        if ($user->role === 'ormawa') {
            // ORMAWA hanya lihat pengajuan mereka sendiri
            $query->where('ormawa_id', $user->ormawa->id);
        }
        // BAUAK dan admin bisa lihat semua pengajuan (no filter)

        // Search by judul kegiatan or ormawa
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('judul_kegiatan', 'like', "%$search%")
                  ->orWhere('ketua_pelaksana', 'like', "%$search%")
                  ->orWhere('lokasi_kegiatan', 'like', "%$search%");
            });
        }

        // Filter by status
        if ($request->filled('status') && $request->input('status') !== '') {
            $query->where('status', $request->input('status'));
        }

        // Filter by date range
        if ($request->filled('tanggal_dari')) {
            $query->where('tanggal_mulai', '>=', $request->input('tanggal_dari'));
        }

        if ($request->filled('tanggal_sampai')) {
            $query->where('tanggal_selesai', '<=', $request->input('tanggal_sampai'));
        }

        // Pagination with custom per_page
        $perPage = $request->input('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100]) ? $perPage : 10;

        $pengajuan = $query->latest()->paginate($perPage)->appends($request->query());

        // Statistics
        if ($user->role === 'ormawa') {
            $stats = [
                'total' => PengajuanKegiatan::where('ormawa_id', $user->ormawa->id)->count(),
                'draft' => PengajuanKegiatan::where('ormawa_id', $user->ormawa->id)->where('status', 'draft')->count(),
                'pending' => PengajuanKegiatan::where('ormawa_id', $user->ormawa->id)->whereIn('status', ['diajukan', 'disetujui_bauak'])->count(),
                'approved' => PengajuanKegiatan::where('ormawa_id', $user->ormawa->id)->where('status', 'disetujui_warek3')->count(),
                'rejected' => PengajuanKegiatan::where('ormawa_id', $user->ormawa->id)->where('status', 'ditolak')->count(),
                'revision' => PengajuanKegiatan::where('ormawa_id', $user->ormawa->id)->where('status', 'revisi_bauak')->count(),
            ];
        } else {
            // BAUAK dan admin lihat statistik semua pengajuan
            $stats = [
                'total' => PengajuanKegiatan::count(),
                'draft' => PengajuanKegiatan::where('status', 'draft')->count(),
                'pending' => PengajuanKegiatan::whereIn('status', ['diajukan', 'disetujui_bauak'])->count(),
                'approved' => PengajuanKegiatan::where('status', 'disetujui_warek3')->count(),
                'rejected' => PengajuanKegiatan::where('status', 'ditolak')->count(),
                'revision' => PengajuanKegiatan::where('status', 'revisi_bauak')->count(),
            ];
        }

        return view('pengajuan.index', compact('pengajuan', 'stats'));
    }

    public function create()
    {
        return view('pengajuan.create');
    }

    public function store(Request $request)
    {
        try {
            // Validate input
            $validated = $request->validate([
                'judul_kegiatan' => 'required|string|max:255',
                'tujuan_kegiatan' => 'required|string',
                'lokasi_kegiatan' => 'required|string|max:255',
                'tempat_pesantren' => 'nullable|string|max:255',
                'tanggal_mulai' => 'required|date',
                'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
                'ketua_pelaksana' => 'required|string|max:255',
                'nama_pemohon' => 'required|string|max:255',
                'file_proposal' => 'required|file|mimes:pdf|max:5120',
                'file_rab' => 'required|file|mimes:pdf|max:5120',
            ], [
                'judul_kegiatan.required' => 'Judul kegiatan harus diisi',
                'tujuan_kegiatan.required' => 'Tujuan kegiatan harus diisi',
                'lokasi_kegiatan.required' => 'Lokasi kegiatan harus diisi',
                'tanggal_mulai.required' => 'Tanggal mulai harus diisi',
                'tanggal_selesai.required' => 'Tanggal selesai harus diisi',
                'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai',
                'ketua_pelaksana.required' => 'Ketua pelaksana harus diisi',
                'nama_pemohon.required' => 'Nama pemohon harus diisi',
                'file_proposal.required' => 'File proposal harus diupload',
                'file_proposal.mimes' => 'File proposal harus berformat PDF',
                'file_proposal.max' => 'File proposal maksimal 5MB',
                'file_rab.required' => 'File RAB harus diupload',
                'file_rab.mimes' => 'File RAB harus berformat PDF',
                'file_rab.max' => 'File RAB maksimal 5MB',
            ]);

            DB::beginTransaction();

            $ormawa = auth()->user()->ormawa;

            if (!$ormawa) {
                throw new \Exception('Data Ormawa tidak ditemukan. Silakan lengkapi profile terlebih dahulu.');
            }

            // Create Pengajuan
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
                'status' => 'diajukan',
            ]);

            // Upload Proposal
            if ($request->hasFile('file_proposal')) {
                $proposalFile = $request->file('file_proposal');
                $proposalPath = $proposalFile->store('proposal/' . $ormawa->id, 'public');

                Proposal::create([
                    'pengajuan_id' => $pengajuan->id,
                    'file_proposal' => $proposalPath,
                    'status' => 'draft',
                ]);
            }

            // Upload RAB
            if ($request->hasFile('file_rab')) {
                $rabFile = $request->file('file_rab');
                $rabPath = $rabFile->store('rab/' . $ormawa->id, 'public');

                Rab::create([
                    'pengajuan_id' => $pengajuan->id,
                    'file_rab' => $rabPath,
                    'status' => 'draft',
                ]);
            }

            // Generate nomor surat
            $nomorSurat = $this->generateNomorSurat();

            // Create Draft Surat Rekomendasi (without generating PDF yet)
            SuratRekomendasi::create([
                'pengajuan_id' => $pengajuan->id,
                'nomor_surat' => $nomorSurat,
                'status' => 'draft',
            ]);

            // Create notification for BAUAK
            $this->createNotificationForBauak($pengajuan);

            DB::commit();

            return redirect()
                ->route('pengajuan.show', $pengajuan)
                ->with('success', 'Pengajuan kegiatan berhasil diajukan!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Validation error. Silakan periksa form Anda.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating pengajuan: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function show(PengajuanKegiatan $pengajuan)
    {
        // Check authorization
        if (auth()->user()->isOrmawa() && $pengajuan->ormawa_id !== auth()->user()->ormawa->id) {
            abort(403, 'Anda tidak memiliki akses ke pengajuan ini.');
        }


        $pengajuan->load([
            'ormawa',
            'proposal',
            'rab',
            'suratRekomendasi',
            'verifikasiBauak.user',
            'persetujuanWarek3.user',
        ]);

        return view('pengajuan.show', compact('pengajuan'));
    }

    public function edit(PengajuanKegiatan $pengajuan)
    {
        // Check authorization
        if (!$pengajuan->canBeEditedBy(auth()->user())) {
            abort(403, 'Anda tidak dapat mengedit pengajuan ini.');
        }

        return view('pengajuan.edit', compact('pengajuan'));
    }

    public function update(Request $request, PengajuanKegiatan $pengajuan)
    {
        // Check authorization
        if (!$pengajuan->canBeEditedBy(auth()->user())) {
            abort(403, 'Anda tidak dapat mengedit pengajuan ini.');
        }

        try {
            $validated = $request->validate([
                'judul_kegiatan' => 'required|string|max:255',
                'tujuan_kegiatan' => 'required|string',
                'lokasi_kegiatan' => 'required|string|max:255',
                'tempat_pesantren' => 'nullable|string|max:255',
                'tanggal_mulai' => 'required|date',
                'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
                'ketua_pelaksana' => 'required|string|max:255',
                'nama_pemohon' => 'required|string|max:255',
                'file_proposal' => 'nullable|file|mimes:pdf|max:5120',
                'file_rab' => 'nullable|file|mimes:pdf|max:5120',
            ]);

            DB::beginTransaction();

            $pengajuan->update($validated);

            // Update Proposal if new file uploaded
            if ($request->hasFile('file_proposal')) {
                // Delete old file
                if ($pengajuan->proposal && $pengajuan->proposal->file_proposal) {
                    Storage::disk('public')->delete($pengajuan->proposal->file_proposal);
                }

                $proposalPath = $request->file('file_proposal')->store(
                    'proposal/' . $pengajuan->ormawa_id,
                    'public'
                );

                $pengajuan->proposal()->update([
                    'file_proposal' => $proposalPath,
                    'versi' => ($pengajuan->proposal->versi ?? 0) + 1,
                ]);
            }

            // Update RAB if new file uploaded
            if ($request->hasFile('file_rab')) {
                // Delete old file
                if ($pengajuan->rab && $pengajuan->rab->file_rab) {
                    Storage::disk('public')->delete($pengajuan->rab->file_rab);
                }

                $rabPath = $request->file('file_rab')->store(
                    'rab/' . $pengajuan->ormawa_id,
                    'public'
                );

                $pengajuan->rab()->update([
                    'file_rab' => $rabPath,
                    'versi' => ($pengajuan->rab->versi ?? 0) + 1,
                ]);
            }


            // Update status back to diajukan and clear previous BAUAK notes
            $pengajuan->update(['status' => 'diajukan', 'catatan' => null]);

            // Notify BAUAK that pengajuan telah diajukan ulang
            $this->createNotificationForBauak($pengajuan);

            DB::commit();

            return redirect()
                ->route('pengajuan.show', $pengajuan)
                ->with('success', 'Pengajuan kegiatan berhasil diperbarui!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating pengajuan: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Export pengajuan to CSV
     */
    public function exportCSV(Request $request)
    {
        $user = auth()->user();

        $query = PengajuanKegiatan::where('ormawa_id', $user->ormawa->id);

        // Apply same filters as index
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('judul_kegiatan', 'like', "%$search%")
                  ->orWhere('ketua_pelaksana', 'like', "%$search%")
                  ->orWhere('lokasi_kegiatan', 'like', "%$search%");
            });
        }

        if ($request->filled('status') && $request->input('status') !== '') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('tanggal_dari')) {
            $query->where('tanggal_mulai', '>=', $request->input('tanggal_dari'));
        }

        if ($request->filled('tanggal_sampai')) {
            $query->where('tanggal_selesai', '<=', $request->input('tanggal_sampai'));
        }

        $pengajuan = $query->latest()->get();

        $headers = ['Judul Kegiatan', 'Lokasi', 'Tanggal Mulai', 'Tanggal Selesai', 'Ketua Pelaksana', 'Status', 'Dibuat'];

        $data = [];
        foreach ($pengajuan as $item) {
            $data[] = [
                $item->judul_kegiatan,
                $item->lokasi_kegiatan,
                $item->tanggal_mulai->format('Y-m-d'),
                $item->tanggal_selesai->format('Y-m-d'),
                $item->ketua_pelaksana,
                ExportService::getStatusLabel($item->status),
                $item->created_at->format('Y-m-d H:i'),
            ];
        }

        ExportService::toCSV($headers, collect($data), 'pengajuan-kegiatan-' . now()->format('Y-m-d'));
    }

    /**
     * Print pengajuan in PDF-friendly format
     */
    public function printView(Request $request)
    {
        $user = auth()->user();

        $query = PengajuanKegiatan::with(['proposal', 'rab', 'suratRekomendasi'])
            ->where('ormawa_id', $user->ormawa->id);

        // Apply same filters as index
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('judul_kegiatan', 'like', "%$search%")
                  ->orWhere('ketua_pelaksana', 'like', "%$search%")
                  ->orWhere('lokasi_kegiatan', 'like', "%$search%");
            });
        }

        if ($request->filled('status') && $request->input('status') !== '') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('tanggal_dari')) {
            $query->where('tanggal_mulai', '>=', $request->input('tanggal_dari'));
        }

        if ($request->filled('tanggal_sampai')) {
            $query->where('tanggal_selesai', '<=', $request->input('tanggal_sampai'));
        }

        $pengajuan = $query->latest()->get();

        return view('pengajuan.print', compact('pengajuan'));
    }

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

    private function createNotificationForBauak($pengajuan)
    {
        try {
            // Get all BAUAK users
            $bauakUsers = \App\Models\User::where('role', 'bauak')
                ->where('is_active', true)
                ->get();

            foreach ($bauakUsers as $bauak) {
                sendNotification(
                    $bauak,
                    'Pengajuan Kegiatan Baru',
                    "Pengajuan kegiatan '{$pengajuan->judul_kegiatan}' dari {$pengajuan->ormawa->nama_ormawa} menunggu verifikasi Anda.",
                    'info',
                    route('bauak.verifikasi.show', $pengajuan),
                    ['telegram', 'email', 'in_app']
                );
            }
        } catch (\Exception $e) {
            Log::error('Error creating BAUAK notification: ' . $e->getMessage());
            // Don't throw error, just log it
        }
    }
}
