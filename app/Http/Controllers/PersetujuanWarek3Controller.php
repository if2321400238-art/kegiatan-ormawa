<?php

namespace App\Http\Controllers;

use App\Models\PengajuanKegiatan;
use App\Models\PersetujuanWarek3;
use App\Models\SuratRekomendasi;
use App\Services\SuratRekomendasiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PersetujuanWarek3Controller extends Controller
{
    // protected $suratService;

    // public function __construct(SuratRekomendasiService $suratService)
    // {
    //     $this->suratService = $suratService;
    // }

    /**
     * Display a listing of pending approvals.
     */
    public function index(Request $request)
    {
        $query = PengajuanKegiatan::with(['ormawa', 'proposal', 'rab', 'verifikasiBauak']);

        // Filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', 'disetujui_bauak');
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('judul_kegiatan', 'like', '%' . $request->search . '%')
                  ->orWhereHas('ormawa', function($query) use ($request) {
                      $query->where('nama_ormawa', 'like', '%' . $request->search . '%');
                  });
            });
        }

        $pengajuan = $query->latest()->paginate(15);

        // Statistics
        $stats = [
            'menunggu' => PengajuanKegiatan::where('status', 'disetujui_bauak')->count(),
            'disetujui' => PengajuanKegiatan::where('status', 'disetujui_warek3')->count(),
            'ditolak' => PengajuanKegiatan::where('status', 'ditolak')->count(),
        ];

        return view('warek3.persetujuan.index', compact('pengajuan', 'stats'));
    }

    /**
     * Display the specified pengajuan.
     */
    public function show(PengajuanKegiatan $pengajuan)
    {
        $pengajuan->load([
            'ormawa.user',
            'proposal',
            'rab',
            'suratRekomendasi',
            'verifikasiBauak.user',
            'persetujuanWarek3.user'
        ]);

        return view('warek3.persetujuan.show', compact('pengajuan'));
    }

    /**
     * Approve pengajuan and generate final surat rekomendasi.
     */
    public function approve(Request $request, PengajuanKegiatan $pengajuan)
    {
        $validated = $request->validate([
            'catatan' => 'nullable|string|max:500',
        ]);

        // Check if already approved or rejected
        if (!in_array($pengajuan->status, ['disetujui_bauak'])) {
            return back()->with('error', 'Pengajuan tidak dapat disetujui pada status ini!');
        }

        DB::beginTransaction();
        try {
            // Create persetujuan record
            $persetujuan = PersetujuanWarek3::create([
                'pengajuan_id' => $pengajuan->id,
                'user_warek3_id' => Auth::id(),
                'catatan' => $validated['catatan'] ?? null,
                'status' => 'disetujui',
                'tanggal_acc' => now(),
            ]);

            // Update pengajuan status
            $pengajuan->update([
                'status' => 'disetujui_warek3',
                'catatan' => $validated['catatan'] ?? null,
            ]);

            // Generate surat rekomendasi final dengan TTD
            // $suratFinal = $this->suratService->generateFinal($pengajuan);

            // Update surat rekomendasi
            // $pengajuan->suratRekomendasi()->update([
            //     'file_surat_final' => $suratFinal,
            //     'status' => 'ttd_warek3',
            //     'tanggal_ttd' => now(),
            // ]);

            // Send notification to Ormawa
            $this->notifyOrmawa($pengajuan, 'disetujui');

            DB::commit();

            return redirect()
                ->route('warek3.persetujuan.show', $pengajuan)
                ->with('success', 'Pengajuan berhasil disetujui dan surat rekomendasi telah ditandatangani!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Reject pengajuan.
     */
    public function reject(Request $request, PengajuanKegiatan $pengajuan)
    {
        $validated = $request->validate([
            'catatan' => 'required|string|max:500',
        ]);

        // Check if already approved or rejected
        if (!in_array($pengajuan->status, ['disetujui_bauak'])) {
            return back()->with('error', 'Pengajuan tidak dapat ditolak pada status ini!');
        }

        DB::beginTransaction();
        try {
            // Create persetujuan record
            PersetujuanWarek3::create([
                'pengajuan_id' => $pengajuan->id,
                'user_warek3_id' => Auth::id(),
                'catatan' => $validated['catatan'],
                'status' => 'ditolak',
                'tanggal_acc' => now(),
            ]);

            // Update pengajuan status
            $pengajuan->update([
                'status' => 'ditolak',
                'catatan' => $validated['catatan'],
            ]);

            // Send notification to Ormawa
            $this->notifyOrmawa($pengajuan, 'ditolak');

            DB::commit();

            return redirect()
                ->route('warek3.persetujuan.index')
                ->with('success', 'Pengajuan berhasil ditolak.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Sign document digitally.
     */
    public function sign(Request $request, PengajuanKegiatan $pengajuan)
    {
        $request->validate([
            'signature' => 'required|string', // Base64 signature image
        ]);

        // Save signature to storage
        $signatureData = $request->signature;
        $signaturePath = $this->saveSignature($signatureData, $pengajuan->id);

        // Update persetujuan with signature path
        $persetujuan = $pengajuan->persetujuanWarek3()->latest()->first();
        if ($persetujuan) {
            $persetujuan->update([
                'signature_path' => $signaturePath,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tanda tangan berhasil disimpan',
        ]);
    }

    /**
     * Monitoring all approved activities.
     */
    public function monitoring(Request $request)
    {
        $query = PengajuanKegiatan::with(['ormawa'])
            ->where('status', 'disetujui_warek3');

        // Filter by date
        if ($request->filled('bulan')) {
            $date = \Carbon\Carbon::createFromFormat('Y-m', $request->bulan);
            $query->whereYear('tanggal_mulai', $date->year)
                  ->whereMonth('tanggal_mulai', $date->month);
        }

        // Filter by ormawa
        if ($request->filled('ormawa_id')) {
            $query->where('ormawa_id', $request->ormawa_id);
        }

        $pengajuan = $query->orderBy('tanggal_mulai', 'desc')->paginate(20);

        $ormawa = \App\Models\Ormawa::orderBy('nama_ormawa')->get();

        return view('warek3.monitoring', compact('pengajuan', 'ormawa'));
    }

    /**
     * Save signature image.
     */
    private function saveSignature($base64Data, $pengajuanId)
    {
        // Remove data:image/png;base64, part
        $image = str_replace('data:image/png;base64,', '', $base64Data);
        $image = str_replace(' ', '+', $image);
        $imageData = base64_decode($image);

        $filename = 'signature_' . $pengajuanId . '_' . time() . '.png';
        $path = 'signatures/' . $filename;

        \Storage::disk('public')->put($path, $imageData);

        return $path;
    }

    /**
     * Send notification to Ormawa.
     */
    private function notifyOrmawa($pengajuan, $status)
    {
        $judul = $status === 'disetujui'
            ? '✅ Pengajuan Disetujui Warek III'
            : '❌ Pengajuan Ditolak Warek III';

        $pesan = $status === 'disetujui'
            ? "Selamat! Pengajuan kegiatan '{$pengajuan->judul_kegiatan}' telah disetujui oleh Warek III. Surat rekomendasi dapat diunduh dan Anda dapat melanjutkan pelaksanaan kegiatan."
            : "Pengajuan kegiatan '{$pengajuan->judul_kegiatan}' ditolak oleh Warek III. Hubungi BAUAK untuk detail dan kemungkinan revisi.";

        $tipe = $status === 'disetujui' ? 'success' : 'error';

        sendNotification(
            $pengajuan->ormawa->user,
            $judul,
            $pesan,
            $tipe,
            route('pengajuan.show', $pengajuan),
            ['telegram', 'email', 'in_app']
        );
    }
}
