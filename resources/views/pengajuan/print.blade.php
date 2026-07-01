<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pengajuan Kegiatan</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }

        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 12px;
            color: #666;
        }

        .print-date {
            text-align: right;
            font-size: 12px;
            color: #666;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        thead {
            background-color: #f3f4f6;
        }

        th {
            padding: 12px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #e5e7eb;
            font-size: 12px;
        }

        td {
            padding: 10px 12px;
            border: 1px solid #e5e7eb;
            font-size: 11px;
        }

        tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }

        .status-draft {
            background-color: #f3f4f6;
            color: #1f2937;
        }

        .status-waiting {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-bauak {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .status-warek3 {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .status-rektor {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .status-approved {
            background-color: #dcfce7;
            color: #166534;
        }

        .status-revision {
            background-color: #fed7aa;
            color: #92400e;
        }

        .status-rejected {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 11px;
            color: #666;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            .container {
                padding: 0;
            }

            table {
                page-break-inside: avoid;
            }

            .no-print {
                display: none;
            }
        }

        .actions {
            text-align: center;
            margin-bottom: 20px;
            gap: 10px;
        }

        .actions button {
            padding: 10px 20px;
            background-color: #3b82f6;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        .actions button:hover {
            background-color: #2563eb;
        }

        .summary {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f0f9ff;
            border-left: 4px solid #3b82f6;
            border-radius: 4px;
        }

        .summary p {
            font-size: 12px;
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        {{-- Header --}}
        <div class="header">
            <h1>Laporan Pengajuan Kegiatan</h1>
            <p>Sistem Manajemen Pengajuan Kegiatan Ormawa</p>
        </div>

        {{-- Print Date --}}
        <div class="print-date">
            Dicetak pada: {{ now()->locale('id')->format('d F Y H:i:s') }}
        </div>

        {{-- Actions --}}
        <div class="actions no-print">
            <button onclick="window.print()">🖨️ Cetak</button>
            <button onclick="history.back()">← Kembali</button>
        </div>

        {{-- Summary --}}
        <div class="summary">
            <p><strong>Total Pengajuan:</strong> {{ count($pengajuan) }} item</p>
            <p><strong>Periode:</strong> {{ request('tanggal_dari', 'Semua') }} s/d {{ request('tanggal_sampai', 'Semua') }}</p>
            @if(request('status'))
                <p><strong>Status Filter:</strong>
                    @php
                        $filterStatus = request('status');
                        $filterLabelMap = [
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
                            'ditolak' => 'Ditolak',
                            'ditolak_kaprodi' => 'Ditolak Kepala Program Studi',
                            'ditolak_dekan' => 'Ditolak Dekan',
                            'ditolak_bauak' => 'Ditolak BAUAK',
                            'ditolak_warek3' => 'Ditolak Wakil Rektor III',
                            'ditolak_rektor' => 'Ditolak Rektor',
                            'ditolak_pp' => 'Ditolak Kepala/Wakil PP',
                        ];
                    @endphp
                    {{ $filterLabelMap[$filterStatus] ?? ucfirst(str_replace('_', ' ', $filterStatus)) }}
                </p>
            @endif
        </div>

        {{-- Table --}}
        <table>
            <thead>
                <tr>
                    <th width="5%">No.</th>
                    <th width="20%">Judul Kegiatan</th>
                    <th width="15%">Lokasi</th>
                    <th width="12%">Tanggal Mulai</th>
                    <th width="12%">Tanggal Selesai</th>
                    <th width="15%">Ketua Pelaksana</th>
                    <th width="12%">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pengajuan as $key => $item)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td><strong>{{ $item->judul_kegiatan }}</strong></td>
                        <td>{{ $item->lokasi_kegiatan }}</td>
                        <td>{{ $item->tanggal_mulai->format('d/m/Y') }}</td>
                        <td>{{ $item->tanggal_selesai->format('d/m/Y') }}</td>
                        <td>{{ $item->ketua_pelaksana }}</td>
                        <td>
                            @php
                                $statusClass = match($item->status) {
                                    'draft' => 'status-draft',
                                    'menunggu_kaprodi',
                                    'menunggu_dekan' => 'status-waiting',
                                    'menunggu_bauak' => 'status-bauak',
                                    'menunggu_warek3' => 'status-warek3',
                                    'menunggu_rektor' => 'status-rektor',
                                    'menunggu_pp' => 'status-rektor',
                                    'disetujui' => 'status-approved',
                                    'revisi_kaprodi',
                                    'revisi_dekan',
                                    'revisi_bauak',
                                    'revisi_warek3',
                                    'revisi_rektor' => 'status-revision',
                                    'ditolak_kaprodi',
                                    'ditolak_dekan',
                                    'ditolak_bauak',
                                    'ditolak_warek3',
                                    'ditolak_rektor' => 'status-rejected',
                                    'ditolak_pp' => 'status-rejected',
                                    default => 'status-draft'
                                };
                            @endphp
                            <span class="status-badge {{ $statusClass }}">{{ $item->status_label }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 20px;">Tidak ada data</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Footer --}}
        <div class="footer">
            <p>&copy; {{ now()->year }} Sistem Manajemen Pengajuan Kegiatan Ormawa. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
