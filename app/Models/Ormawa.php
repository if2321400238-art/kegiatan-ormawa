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
        'periode',
        'pembina',
        'pembina_user_id',
        'kategori_organisasi',
        'tingkat_organisasi',
        'fakultas_id',
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

    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'anggota_ormawa',
            'ormawa_id',
            'user_id'
        )->withPivot('jabatan', 'status')
            ->withTimestamps();
    }

    public function ketua()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function anggota()
    {
        return $this->hasMany(AnggotaOrmawa::class, 'ormawa_id');
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

    public function isKetua(User $user): bool
    {
        return $this->user_id === $user->id;
    }

    public function isInternal(): bool
    {
        return $this->kategori_organisasi === 'internal';
    }

    public function isEksternal(): bool
    {
        return $this->kategori_organisasi === 'eksternal';
    }

    public function isFakultas(): bool
    {
        return $this->kategori_organisasi === 'internal' && $this->tingkat_organisasi === 'fakultas';
    }

    public function fakultas()
    {
        return $this->belongsTo(\App\Models\Fakultas::class, 'fakultas_id');
    }

    public function pembinaUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'pembina_user_id');
    }

    public function isUniversitas(): bool
    {
        return $this->kategori_organisasi === 'internal' && $this->tingkat_organisasi === 'universitas';
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
                $q->where('status', 'disetujui');
            }
        ]);
    }
}
