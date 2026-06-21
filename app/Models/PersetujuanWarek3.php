<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersetujuanWarek3 extends Model
{
    use HasFactory;

    protected $table = 'persetujuan_warek3';

    protected $fillable = [
        'pengajuan_id',
        'user_warek3_id',
        'catatan',
        'status',
        'tanggal_acc',
        'signature_path',
    ];

    protected $casts = [
        'tanggal_acc' => 'datetime',
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
        return $this->belongsTo(User::class, 'user_warek3_id');
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    public function getStatusLabelAttribute(): string
    {
        $labels = [
            'disetujui' => 'Disetujui',
            'ditolak' => 'Ditolak',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    public function getStatusBadgeAttribute(): string
    {
        $badges = [
            'disetujui' => 'success',
            'ditolak' => 'danger',
        ];

        return $badges[$this->status] ?? 'secondary';
    }

    public function getSignatureUrlAttribute()
    {
        if (!$this->signature_path) {
            return null;
        }

        return asset('storage/' . $this->signature_path);
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    public function isApproved(): bool
    {
        return $this->status === 'disetujui';
    }

    public function isRejected(): bool
    {
        return $this->status === 'ditolak';
    }

    public function hasSignature(): bool
    {
        return !empty($this->signature_path);
    }
}
