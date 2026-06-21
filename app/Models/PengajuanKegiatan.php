<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PengajuanKegiatan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pengajuan_kegiatan';

    protected $fillable = [
        'ormawa_id',
        'judul_kegiatan',
        'tujuan_kegiatan',
        'lokasi_kegiatan',
        'tempat_pesantren',
        'tanggal_mulai',
        'tanggal_selesai',
        'ketua_pelaksana',
        'nama_pemohon',
        'status',
        'catatan',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function ormawa()
    {
        return $this->belongsTo(Ormawa::class);
    }

    public function proposal()
    {
        return $this->hasOne(Proposal::class, 'pengajuan_id');
    }

    public function rab()
    {
        return $this->hasOne(Rab::class, 'pengajuan_id');
    }

    public function suratRekomendasi()
    {
        return $this->hasOne(SuratRekomendasi::class, 'pengajuan_id');
    }

    public function verifikasiBauak()
    {
        return $this->hasMany(VerifikasiBauak::class, 'pengajuan_id');
    }

    public function latestVerifikasiBauak()
    {
        return $this->hasOne(VerifikasiBauak::class, 'pengajuan_id')->latest();
    }

    public function persetujuanWarek3()
    {
        return $this->hasMany(PersetujuanWarek3::class, 'pengajuan_id');
    }

    public function latestPersetujuanWarek3()
    {
        return $this->hasOne(PersetujuanWarek3::class, 'pengajuan_id')->latest();
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopeDiajukan($query)
    {
        return $query->where('status', 'diajukan');
    }

    public function scopeMenungguVerifikasi($query)
    {
        return $query->whereIn('status', ['diajukan', 'disetujui_bauak']);
    }

    public function scopeDisetujui($query)
    {
        return $query->where('status', 'disetujui_warek3');
    }

    public function scopeDitolak($query)
    {
        return $query->where('status', 'ditolak');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('tanggal_mulai', '>=', now())
                    ->where('status', 'disetujui_warek3')
                    ->orderBy('tanggal_mulai');
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    public function getStatusBadgeAttribute(): string
    {
        $badges = [
            'draft' => 'secondary',
            'diajukan' => 'info',
            'revisi_bauak' => 'warning',
            'disetujui_bauak' => 'primary',
            'revisi_warek3' => 'warning',
            'disetujui_warek3' => 'success',
            'ditolak' => 'danger',
            'selesai' => 'dark',
        ];

        return $badges[$this->status] ?? 'secondary';
    }

    public function getStatusLabelAttribute(): string
    {
        $labels = [
            'draft' => 'Draft',
            'diajukan' => 'Diajukan',
            'revisi_bauak' => 'Revisi BAUAK',
            'disetujui_bauak' => 'Disetujui BAUAK',
            'revisi_warek3' => 'Revisi Warek III',
            'disetujui_warek3' => 'Disetujui Warek III',
            'ditolak' => 'Ditolak',
            'selesai' => 'Selesai',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    public function getDurasiKegiatanAttribute(): int
    {
        return $this->tanggal_mulai->diffInDays($this->tanggal_selesai) + 1;
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    public function canBeEditedBy($user): bool
    {
        if ($user->isOrmawa()) {
            // Allow edit when draft, revision requested, or rejected so Ormawa can resubmit
            return in_array($this->status, ['draft', 'revisi_bauak', 'revisi_warek3', 'ditolak'])
                   && $this->ormawa->user_id === $user->id;
        }
        return false;
    }

    public function canBeVerifiedByBauak(): bool
    {
        return $this->status === 'diajukan';
    }

    public function canBeApprovedByWarek3(): bool
    {
        return $this->status === 'disetujui_bauak';
    }

    public function isApproved(): bool
    {
        return $this->status === 'disetujui_warek3';
    }

    public function isRejected(): bool
    {
        return $this->status === 'ditolak';
    }

    public function isPending(): bool
    {
        return in_array($this->status, ['diajukan', 'disetujui_bauak']);
    }

    public function isUpcoming(): bool
    {
        return $this->tanggal_mulai->isFuture() && $this->isApproved();
    }

    public function isOngoing(): bool
    {
        return $this->tanggal_mulai->isPast()
            && $this->tanggal_selesai->isFuture()
            && $this->isApproved();
    }

    public function isCompleted(): bool
    {
        return $this->tanggal_selesai->isPast() && $this->isApproved();
    }
}
