<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ormawa extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ormawa';

    protected $fillable = [
        'user_id',
        'nama_ormawa',
        'ketua',
        'pembina',
        'jenis_ormawa',
        'kop_surat',
        'kontak',
        'deskripsi',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pengajuanKegiatan()
    {
        return $this->hasMany(PengajuanKegiatan::class);
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    public function getKopSuratUrlAttribute()
    {
        if (!$this->kop_surat) {
            return null;
        }

        return asset('storage/' . $this->kop_surat);
    }

    public function hasKopSurat(): bool
    {
        return !empty($this->kop_surat);
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    public function isProfileComplete(): bool
    {
        return !empty($this->nama_ormawa)
            && !empty($this->ketua)
            && !empty($this->kop_surat);
    }

    public function isFakultas(): bool
    {
        return $this->jenis_ormawa === 'fakultas';
    }

    public function isUniversitas(): bool
    {
        return $this->jenis_ormawa === 'universitas';
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopeActive($query)
    {
        return $query->whereHas('user', function($q) {
            $q->where('is_active', true);
        });
    }

    public function scopeWithStats($query)
    {
        return $query->withCount([
            'pengajuanKegiatan',
            'pengajuanKegiatan as pengajuan_disetujui' => function($q) {
                $q->where('status', 'disetujui_warek3');
            }
        ]);
    }
}
