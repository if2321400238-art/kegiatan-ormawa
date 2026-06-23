<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerifikasiDosen extends Model
{
    use HasFactory;

    protected $table = 'verifikasi_dosen';

    protected $fillable = [
        'pengajuan_id',
        'user_dosen_id',
        'catatan',
        'status',
        'tanggal_verifikasi',
    ];

    protected $casts = [
        'tanggal_verifikasi' => 'datetime',
    ];

    public function pengajuanKegiatan()
    {
        return $this->belongsTo(PengajuanKegiatan::class, 'pengajuan_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_dosen_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'disetujui' => 'Disetujui',
            'revisi' => 'Perlu Revisi',
            'ditolak' => 'Ditolak',
            default => $this->status,
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'disetujui' => 'success',
            'revisi' => 'warning',
            'ditolak' => 'danger',
            default => 'secondary',
        };
    }

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
