<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;

class ExportService
{
    /**
     * Export data to CSV format
     */
    public static function toCSV(array $headers, Collection $data, string $filename)
    {
        $output = fopen('php://output', 'w');

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Write headers
        fputcsv($output, $headers);

        // Write data rows
        foreach ($data as $row) {
            if (is_object($row)) {
                $row = $row->toArray();
            }
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }

    /**
     * Format data array for export
     */
    public static function formatPengajuanForExport($pengajuan)
    {
        return $pengajuan->map(function ($item) {
            return [
                'Judul Kegiatan' => $item->judul_kegiatan,
                'Lokasi' => $item->lokasi_kegiatan,
                'Tanggal Mulai' => $item->tanggal_mulai->format('Y-m-d'),
                'Tanggal Selesai' => $item->tanggal_selesai->format('Y-m-d'),
                'Ketua Pelaksana' => $item->ketua_pelaksana,
                'Status' => self::getStatusLabel($item->status),
                'Dibuat' => $item->created_at->format('Y-m-d H:i'),
            ];
        });
    }

    /**
     * Get readable status label
     */
    public static function getStatusLabel($status)
    {
        $labels = [
            'draft' => 'Draft',
            'menunggu_dosen' => 'Pending',
            'menunggu_warek3' => 'Disetujui BAUAK',
            'disetujui' => 'Disetujui Warek III',
            'revisi_bauak' => 'Revisi',
            'ditolak' => 'Ditolak',
        ];

        return $labels[$status] ?? $status;
    }
}
