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

    public function verifikasiDosen()
    {
        return $this->hasMany(VerifikasiDosen::class, 'pengajuan_id');
    }

    public function latestVerifikasiDosen()
    {
        return $this->hasOne(VerifikasiDosen::class, 'pengajuan_id')->latest();
    }

    public function persetujuanDekan()
    {
        return $this->hasMany(PersetujuanDekan::class, 'pengajuan_id');
    }

    public function latestPersetujuanDekan()
    {
        return $this->hasOne(PersetujuanDekan::class, 'pengajuan_id')->latest();
    }

    public function persetujuanWarek3()
    {
        return $this->hasMany(PersetujuanWarek3::class, 'pengajuan_id');
    }

    public function latestPersetujuanWarek3()
    {
        return $this->hasOne(PersetujuanWarek3::class, 'pengajuan_id')->latest();
    }

    public function persetujuanRektor()
    {
        return $this->hasMany(PersetujuanRektor::class, 'pengajuan_id');
    }

    public function latestPersetujuanRektor()
    {
        return $this->hasOne(PersetujuanRektor::class, 'pengajuan_id')->latest();
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopeDiajukan($query)
    {
        return $query->where('status', 'menunggu_dosen');
    }

    public function scopeMenungguDosen($query)
    {
        return $query->where('status', 'menunggu_dosen');
    }

    public function scopeRevisiDosen($query)
    {
        return $query->where('status', 'revisi_dosen');
    }

    public function scopeMenungguDekan($query)
    {
        return $query->where('status', 'menunggu_dekan');
    }

    public function scopeRevisiDekan($query)
    {
        return $query->where('status', 'revisi_dekan');
    }

    public function scopeMenungguBauak($query)
    {
        return $query->where('status', 'menunggu_bauak');
    }

    public function scopeRevisiBauak($query)
    {
        return $query->where('status', 'revisi_bauak');
    }

    public function scopeMenungguWarek3($query)
    {
        return $query->where('status', 'menunggu_warek3');
    }

    public function scopeRevisiWarek3($query)
    {
        return $query->where('status', 'revisi_warek3');
    }

    public function scopeMenungguRektor($query)
    {
        return $query->where('status', 'menunggu_rektor');
    }

    public function scopeRevisiRektor($query)
    {
        return $query->where('status', 'revisi_rektor');
    }

    public function scopeDisetujui($query)
    {
        return $query->where('status', 'disetujui');
    }

    public function scopeDitolak($query)
    {
        return $query->where('status', 'ditolak');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('tanggal_mulai', '>=', now())
                    ->where('status', 'disetujui')
                    ->orderBy('tanggal_mulai');
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    public function getStatusBadgeAttribute(): string
    {
        $badges = [
            'draft' => 'secondary',
            'menunggu_dosen' => 'warning',
            'menunggu_dekan' => 'warning',
            'menunggu_bauak' => 'warning',
            'menunggu_warek3' => 'warning',
            'menunggu_rektor' => 'warning',
            'disetujui' => 'success',
            'revisi_dosen' => 'warning',
            'revisi_dekan' => 'warning',
            'revisi_bauak' => 'warning',
            'revisi_warek3' => 'warning',
            'revisi_rektor' => 'warning',
            'ditolak' => 'danger',
            'selesai' => 'dark',
        ];

        return $badges[$this->status] ?? 'secondary';
    }

    public function getStatusLabelAttribute(): string
    {
        $labels = [
            'draft' => 'Draft',
            'menunggu_dosen' => 'Menunggu Dosen Pembina',
            'menunggu_dekan' => 'Menunggu Dekan',
            'menunggu_bauak' => 'Menunggu BAUAK',
            'menunggu_warek3' => 'Menunggu Wakil Rektor III',
            'menunggu_rektor' => 'Menunggu Rektor',
            'disetujui' => 'Disetujui',
            'revisi_dosen' => 'Revisi Dosen Pembina',
            'revisi_dekan' => 'Revisi Dekan',
            'revisi_bauak' => 'Revisi BAUAK',
            'revisi_warek3' => 'Revisi Wakil Rektor III',
            'revisi_rektor' => 'Revisi Rektor',
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
            return in_array($this->status, [
                'draft',
                'menunggu_dosen',
                'revisi_dosen',
                'revisi_dekan',
                'revisi_bauak',
                'revisi_warek3',
                'revisi_rektor',
                'ditolak'
            ]) && $this->ormawa->user_id === $user->id;
        }

        return false;
    }

    public function canBeVerifiedByBauak(): bool
    {
        return $this->status === 'menunggu_bauak';
    }

    public function canBeApprovedByWarek3(): bool
    {
        return $this->status === 'menunggu_warek3';
    }

    public function canBeReviewedByRektor(): bool
    {
        return $this->status === 'menunggu_rektor';
    }

    public function isApproved(): bool
    {
        return $this->status === 'disetujui';
    }

    public function isRejected(): bool
    {
        return $this->status === 'ditolak';
    }

    public function isPending(): bool
    {
        return in_array($this->status, [
            'menunggu_dosen',
            'menunggu_dekan',
            'menunggu_bauak',
            'menunggu_warek3',
            'menunggu_rektor'
        ]);
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
