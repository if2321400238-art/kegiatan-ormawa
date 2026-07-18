<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PengajuanKegiatan extends Model
{
    use HasFactory, SoftDeletes;

    public const REJECTED_STATUSES = [
        'ditolak_kaprodi',
        'ditolak_dekan',
        'ditolak_bauak',
        'ditolak_warek3',
        'ditolak_rektor',
        'ditolak_pp',
    ];

    protected $table = 'pengajuan_kegiatan';

    protected $fillable = [
        'ormawa_id',
        'created_by_user_id',
        'updated_by_user_id',
        'judul_kegiatan',
        'tujuan_kegiatan',
        'lokasi_kegiatan',
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

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    public function proposal()
    {
        return $this->hasOne(Proposal::class, 'pengajuan_id');
    }

    public function rab()
    {
        return $this->hasOne(Rab::class, 'pengajuan_id');
    }

    public function lpj()
    {
        return $this->hasOne(LaporanPertanggungjawaban::class, 'pengajuan_id');
    }

    public function verifikasiBauak()
    {
        return $this->hasMany(VerifikasiBauak::class, 'pengajuan_id');
    }

    public function latestVerifikasiBauak()
    {
        return $this->hasOne(VerifikasiBauak::class, 'pengajuan_id')->latest();
    }

    public function persetujuanKaprodi()
    {
        return $this->hasMany(PersetujuanKaprodi::class, 'pengajuan_id');
    }

    public function latestPersetujuanKaprodi()
    {
        return $this->hasOne(PersetujuanKaprodi::class, 'pengajuan_id')->latest();
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

    public function persetujuanPp()
    {
        return $this->hasMany(PersetujuanPp::class, 'pengajuan_id');
    }

    public function latestPersetujuanPp()
    {
        return $this->hasOne(PersetujuanPp::class, 'pengajuan_id')->latest();
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopeDiajukan($query)
    {
        return $query->where('status', 'menunggu_kaprodi');
    }

    public function scopeMenungguKaprodi($query)
    {
        return $query->where('status', 'menunggu_kaprodi');
    }

    public function scopeRevisiKaprodi($query)
    {
        return $query->where('status', 'revisi_kaprodi');
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

    public function scopeMenungguPp($query)
    {
        return $query->where('status', 'menunggu_pp');
    }

    public function scopeDisetujui($query)
    {
        return $query->where('status', 'disetujui');
    }

    public function scopeDitolak($query)
    {
        return $query->whereIn('status', self::REJECTED_STATUSES);
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
            'menunggu_kaprodi' => 'warning',
            'menunggu_dekan' => 'warning',
            'menunggu_bauak' => 'warning',
            'menunggu_warek3' => 'warning',
            'menunggu_rektor' => 'warning',
            'menunggu_pp' => 'warning',
            'disetujui' => 'success',
            'revisi_kaprodi' => 'warning',
            'revisi_dekan' => 'warning',
            'revisi_bauak' => 'warning',
            'revisi_warek3' => 'warning',
            'revisi_rektor' => 'warning',
            'ditolak_kaprodi' => 'danger',
            'ditolak_dekan' => 'danger',
            'ditolak_bauak' => 'danger',
            'ditolak_warek3' => 'danger',
            'ditolak_rektor' => 'danger',
            'ditolak_pp' => 'danger',
            'selesai' => 'dark',
        ];

        return $badges[$this->status] ?? 'secondary';
    }

    public function getStatusLabelAttribute(): string
    {
        $labels = [
            'draft' => 'Draft',
            'menunggu_kaprodi' => 'Menunggu Kepala Program Studi',
            'menunggu_dekan' => 'Menunggu Dekan',
            'menunggu_bauak' => 'Menunggu BAUAK',
            'menunggu_warek3' => 'Menunggu Wakil Rektor III',
            'menunggu_rektor' => 'Menunggu Rektor',
            'menunggu_pp' => 'Menunggu Kepala/Wakil PP',
            'disetujui' => 'Disetujui',
            'revisi_kaprodi' => 'Revisi Kepala Program Studi',
            'revisi_dekan' => 'Revisi Dekan',
            'revisi_bauak' => 'Revisi BAUAK',
            'revisi_warek3' => 'Revisi Wakil Rektor III',
            'revisi_rektor' => 'Revisi Rektor',
            'ditolak_kaprodi' => 'Ditolak Kepala Program Studi',
            'ditolak_dekan' => 'Ditolak Dekan',
            'ditolak_bauak' => 'Ditolak BAUAK',
            'ditolak_warek3' => 'Ditolak Wakil Rektor III',
            'ditolak_rektor' => 'Ditolak Rektor',
            'ditolak_pp' => 'Ditolak Kepala/Wakil PP',
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
                'menunggu_kaprodi',
                'revisi_kaprodi',
                'revisi_dekan',
                'revisi_bauak',
                'revisi_warek3',
                'revisi_rektor',
                ...self::REJECTED_STATUSES
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
        return in_array($this->status, self::REJECTED_STATUSES, true);
    }

    public function isPending(): bool
    {
        return in_array($this->status, [
            'menunggu_kaprodi',
            'menunggu_dekan',
            'menunggu_bauak',
            'menunggu_warek3',
            'menunggu_rektor',
            'menunggu_pp',
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
