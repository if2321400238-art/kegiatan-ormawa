<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proposal extends Model
{
    use HasFactory;

    protected $table = 'proposal';

    protected $fillable = [
        'pengajuan_id',
        'file_proposal',
        'status',
        'versi',
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

    public function getFileUrlAttribute()
    {
        if (!$this->file_proposal) {
            return null;
        }

        return asset('storage/' . $this->file_proposal);
    }

    public function getFileSizeAttribute()
    {
        if (!$this->file_proposal) {
            return null;
        }

        $path = storage_path('app/public/' . $this->file_proposal);

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
