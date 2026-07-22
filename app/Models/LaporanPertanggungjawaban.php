<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanPertanggungjawaban extends Model
{
    use HasFactory;

    protected $table = 'laporan_pertanggungjawaban';

    protected $fillable = ['pengajuan_id', 'ringkasan_pelaksanaan', 'hasil_kegiatan', 'kendala',
        'tanggal_pelaksanaan_mulai', 'tanggal_pelaksanaan_selesai', 'jumlah_peserta',
        'realisasi_anggaran', 'sisa_anggaran', 'file_laporan', 'status', 'catatan_verifikator',
        'created_by', 'verified_by', 'submitted_at', 'verified_at'];

    protected $casts = [
        'tanggal_pelaksanaan_mulai' => 'date', 'tanggal_pelaksanaan_selesai' => 'date',
        'realisasi_anggaran' => 'decimal:2', 'sisa_anggaran' => 'decimal:2',
        'submitted_at' => 'datetime', 'verified_at' => 'datetime',
    ];

    public function pengajuan()
    {
        return $this->belongsTo(PengajuanKegiatan::class, 'pengajuan_id');
    }

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function verifikator()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function realisasiAnggaran()
    {
        return $this->hasMany(LpjRealisasiAnggaran::class, 'lpj_id');
    }

    public function lampiran()
    {
        return $this->hasMany(LpjLampiran::class, 'lpj_id');
    }

    public function versiDokumen()
    {
        return $this->hasMany(LpjVersiDokumen::class, 'lpj_id')->latest('versi');
    }

    public function riwayatVerifikasi()
    {
        return $this->hasMany(VerifikasiLpj::class, 'lpj_id')->latest('tanggal_verifikasi');
    }

    public function getFileUrlAttribute(): string
    {
        return asset('storage/'.$this->file_laporan);
    }

    public function getStatusLabelAttribute(): string
    {
        return ['draft' => 'Draft', 'diajukan' => 'Menunggu Verifikasi BAUAK', 'revisi' => 'Perlu Revisi', 'diterima' => 'Diterima', 'ditolak' => 'Ditolak'][$this->status] ?? $this->status;
    }
}
