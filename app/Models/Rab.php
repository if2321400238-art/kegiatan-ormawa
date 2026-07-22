<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rab extends Model
{
    use HasFactory;

    protected $table = 'rab';

    protected $fillable = [
        'pengajuan_id',
        'file_rab',
        'total_anggaran',
        'status',
        'versi',
    ];

    protected $casts = [
        'total_anggaran' => 'decimal:2',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function pengajuanKegiatan()
    {
        return $this->belongsTo(PengajuanKegiatan::class, 'pengajuan_id');
    }

    public function items()
    {
        return $this->hasMany(RabItem::class);
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    public function getFileUrlAttribute()
    {
        if (!$this->file_rab) {
            return null;
        }

        return asset('storage/' . $this->file_rab);
    }

    public function getFileSizeAttribute()
    {
        if (!$this->file_rab) {
            return null;
        }

        $path = storage_path('app/public/' . $this->file_rab);

        if (!file_exists($path)) {
            return null;
        }

        $bytes = filesize($path);
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getTotalAnggaranFormattedAttribute()
    {
        if (!$this->total_anggaran) {
            return 'Rp 0';
        }

        return 'Rp ' . number_format($this->total_anggaran, 0, ',', '.');
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    public function isFinal(): bool
    {
        return $this->status === 'final';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }
}
