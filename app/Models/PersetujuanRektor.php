<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersetujuanRektor extends Model
{
    use HasFactory;

    protected $table = 'persetujuan_rektor';

    protected $fillable = [
        'pengajuan_id',
        'user_rektor_id',
        'catatan',
        'status',
        'tanggal_acc',
    ];

    protected $casts = [
        'tanggal_acc' => 'datetime',
    ];

    public function pengajuanKegiatan()
    {
        return $this->belongsTo(PengajuanKegiatan::class, 'pengajuan_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_rektor_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'disetujui' => 'Disetujui',
            'ditolak' => 'Ditolak',
            default => $this->status,
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'disetujui' => 'success',
            'ditolak' => 'danger',
            default => 'secondary',
        };
    }

    public function isApproved(): bool
    {
        return $this->status === 'disetujui';
    }

    public function isRejected(): bool
    {
        return $this->status === 'ditolak';
    }
}
