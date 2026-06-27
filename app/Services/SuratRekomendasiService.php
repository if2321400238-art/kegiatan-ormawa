<?php

namespace App\Services;

use App\Models\PengajuanKegiatan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SuratRekomendasiService
{
    /**
     * Generate draft surat rekomendasi (tanpa TTD)
     */
    public function generateDraft(PengajuanKegiatan $pengajuan): string
    {
        $data = [
            'nomor_surat' => $this->generateNomorSurat(),
            'tanggal_surat' => now()->format('d F Y'),
            'ormawa' => $pengajuan->ormawa,
            'pengajuan' => $pengajuan,
            'is_draft' => true,
        ];

        $pdf = Pdf::loadView('pdf.surat-rekomendasi', $data);

        $filename = 'surat_draft_' . $pengajuan->id . '_' . time() . '.pdf';
        $path = 'surat_rekomendasi/draft/' . $filename;

        Storage::disk('public')->put($path, $pdf->output());

        return $path;
    }

    /**
     * Generate surat rekomendasi final (dengan TTD Warek3)
     */
    public function generateFinal(PengajuanKegiatan $pengajuan, $ttdPath = null): string
    {
        $data = [
            'nomor_surat' => $pengajuan->suratRekomendasi->nomor_surat,
            'tanggal_surat' => now()->format('d F Y'),
            'tanggal_ttd' => now()->format('d F Y'),
            'ormawa' => $pengajuan->ormawa,
            'pengajuan' => $pengajuan,
            'warek3' => Auth::user(),
            'ttd_path' => $ttdPath,
            'is_draft' => false,
        ];

        $pdf = Pdf::loadView('pdf.surat-rekomendasi', $data)
            ->setPaper('a4', 'portrait');

        $filename = 'surat_final_' . $pengajuan->id . '_' . time() . '.pdf';
        $path = 'surat_rekomendasi/final/' . $filename;

        Storage::disk('public')->put($path, $pdf->output());

        return $path;
    }

    /**
     * Generate QR Code untuk validasi surat
     */
    public function generateQRCode($nomorSurat): string
    {
        $url = route('surat.verify', ['nomor' => $nomorSurat]);

        // Using SimpleSoftwareIO/simple-qrcode package
        $qrCode = QrCode::format('png')
            ->size(200)
            ->generate($url);

        $filename = 'qr_' . md5($nomorSurat) . '.png';
        $path = 'qrcode/' . $filename;

        Storage::disk('public')->put($path, $qrCode);

        return $path;
    }

    /**
     * Add watermark to PDF
     */
    public function addWatermark($pdfPath, $watermarkText = 'COPY'): string
    {
        // Implementation using FPDI
        // This would add "COPY" watermark for non-official documents

        return $pdfPath;
    }

    /**
     * Generate nomor surat
     */
    private function generateNomorSurat(): string
    {
        $year = date('Y');
        $month = date('m');

        $latest = \App\Models\SuratRekomendasi::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count();

        $number = str_pad($latest + 1, 4, '0', STR_PAD_LEFT);

        return "{$number}/BAUAK-SR/{$month}/{$year}";
    }

    /**
     * Verify surat by QR code
     */
    public function verifySurat($nomorSurat)
    {
        return \App\Models\SuratRekomendasi::where('nomor_surat', $nomorSurat)
            ->with(['pengajuanKegiatan.ormawa', 'pengajuanKegiatan.persetujuanWarek3'])
            ->first();
    }

}
