<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratRekomendasi extends Model
{
    use HasFactory;

    protected $table = 'surat_rekomendasi';

    protected $fillable = [
        'pengajuan_id',
        'nomor_surat',
        'file_surat_draft',
        'file_surat_final',
        'status',
        'tanggal_ttd',
    ];

    protected $casts = [
        'tanggal_ttd' => 'datetime',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function pengajuanKegiatan()
    {
        return $this->belongsTo(PengajuanKegiatan::class, 'pengajuan_id');
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    public function getDraftUrlAttribute()
    {
        if (!$this->file_surat_draft) {
            return null;
        }

        return asset('storage/' . $this->file_surat_draft);
    }

    public function getFinalUrlAttribute()
    {
        if (!$this->file_surat_final) {
            return null;
        }

        return asset('storage/' . $this->file_surat_final);
    }

    public function getStatusLabelAttribute(): string
    {
        $labels = [
            'draft' => 'Draft',
            'menunggu_warek' => 'Menunggu Warek III',
            'ttd_warek3' => 'Ditandatangani',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    public function getStatusBadgeAttribute(): string
    {
        $badges = [
            'draft' => 'secondary',
            'menunggu_warek' => 'warning',
            'ttd_warek3' => 'success',
        ];

        return $badges[$this->status] ?? 'secondary';
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isWaitingWarek3(): bool
    {
        return $this->status === 'menunggu_warek';
    }

    public function isSigned(): bool
    {
        return $this->status === 'ttd_warek3';
    }

    public function hasFinalFile(): bool
    {
        return !empty($this->file_surat_final);
    }
}
