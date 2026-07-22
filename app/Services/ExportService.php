<?php

namespace App\Services;

use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExportService
{
    /**
     * Export data to CSV format
     */
    public static function toCSV(array $headers, Collection $data, string $filename)
    {
        $output = fopen('php://output', 'w');

        header('Content-Type: text/csv; charset=utf-8');
        header(chr(67).chr(111).chr(110).chr(116).chr(101).chr(110).chr(116).chr(45).chr(68).chr(105).chr(115).chr(112).chr(111).chr(115).chr(105).chr(116).chr(105).chr(111).chr(110).chr(58).chr(32).chr(97).chr(116).chr(116).chr(97).chr(99).chr(104).chr(109).chr(101).chr(110).chr(116).chr(59).chr(32).chr(102).chr(105).chr(108).chr(101).chr(110).chr(97).chr(109).chr(101).chr(61).chr(34) . $filename . chr(46).chr(120).chr(108).chr(115).chr(120).chr(34));
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

    public static function toExcel(array $headers, Collection $data, string $filename)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($headers, null, "A1");

        $rowNumber = 2;
        foreach ($data as $row) {
            $sheet->fromArray(is_object($row) ? $row->toArray() : $row, null, "A".$rowNumber++);
        }

        foreach (range(1, count($headers)) as $column) {
            $sheet->getColumnDimensionByColumn($column)->setAutoSize(true);
        }

        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header(chr(67).chr(111).chr(110).chr(116).chr(101).chr(110).chr(116).chr(45).chr(68).chr(105).chr(115).chr(112).chr(111).chr(115).chr(105).chr(116).chr(105).chr(111).chr(110).chr(58).chr(32).chr(97).chr(116).chr(116).chr(97).chr(99).chr(104).chr(109).chr(101).chr(110).chr(116).chr(59).chr(32).chr(102).chr(105).chr(108).chr(101).chr(110).chr(97).chr(109).chr(101).chr(61).chr(34) . $filename . chr(46).chr(120).chr(108).chr(115).chr(120).chr(34));
        header("Cache-Control: max-age=0");

        (new Xlsx($spreadsheet))->save("php://output");
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
            'menunggu_kaprodi' => 'Menunggu Kepala Program Studi',
            'menunggu_dekan' => 'Menunggu Dekan',
            'menunggu_bauak' => 'Menunggu BAUAK',
            'menunggu_warek3' => 'Menunggu Wakil Rektor III',
            'menunggu_rektor' => 'Menunggu Rektor',
            'menunggu_pp' => 'Menunggu Kepala/Wakil PP',
            'disetujui' => 'Disetujui',
            'revisi_kaprodi' => 'Revisi Kepala Program Studi',
            'revisi_dekan' => 'Revisi Dekan',
            'revisi_bauak' => 'Revisi BAUAK',
            'revisi_warek3' => 'Revisi Wakil Rektor III',
            'revisi_rektor' => 'Revisi Rektor',
            'ditolak_kaprodi' => 'Ditolak Kepala Program Studi',
            'ditolak_dekan' => 'Ditolak Dekan',
            'ditolak_bauak' => 'Ditolak BAUAK',
            'ditolak_warek3' => 'Ditolak Wakil Rektor III',
            'ditolak_rektor' => 'Ditolak Rektor',
            'ditolak_pp' => 'Ditolak Kepala/Wakil PP',
        ];

        return $labels[$status] ?? $status;
    }
}
