<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerifikasiBauak extends Model
{
    use HasFactory;

    protected $table = 'verifikasi_bauak';

    protected $fillable = [
        'pengajuan_id',
        'user_bauak_id',
        'catatan',
        'status',
        'tanggal_verifikasi',
    ];

    protected $casts = [
        'tanggal_verifikasi' => 'datetime',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function pengajuanKegiatan()
    {
        return $this->belongsTo(PengajuanKegiatan::class, 'pengajuan_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_bauak_id');
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    public function getStatusLabelAttribute(): string
    {
        $labels = [
            'disetujui' => 'Disetujui',
            'revisi' => 'Perlu Revisi',
            'ditolak' => 'Ditolak',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    public function getStatusBadgeAttribute(): string
    {
        $badges = [
            'disetujui' => 'success',
            'revisi' => 'warning',
            'ditolak' => 'danger',
        ];

        return $badges[$this->status] ?? 'secondary';
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    public function isApproved(): bool
    {
        return $this->status === 'disetujui';
    }

    public function needsRevision(): bool
    {
        return $this->status === 'revisi';
    }

    public function isRejected(): bool
    {
        return $this->status === 'ditolak';
    }
}
