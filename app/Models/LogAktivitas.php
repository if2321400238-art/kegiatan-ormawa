<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogAktivitas extends Model
{
    use HasFactory;

    protected $table = 'log_aktivitas';

    protected $fillable = [
        'user_id',
        'aktivitas',
        'modul',
        'subjek_type',
        'subjek_id',
        'deskripsi',
        'ip_address',
        'user_agent',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Polymorphic relationship to any model
     */
    public function subjek()
    {
        return $this->morphTo();
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByModul($query, string $modul)
    {
        return $query->where('modul', $modul);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereYear('created_at', now()->year)
                    ->whereMonth('created_at', now()->month);
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    public function getModulLabelAttribute(): string
    {
        $labels = [
            'pengajuan' => 'Pengajuan Kegiatan',
            'verifikasi' => 'Verifikasi',
            'persetujuan' => 'Persetujuan',
            'sistem' => 'Sistem',
        ];

        return $labels[$this->modul] ?? $this->modul;
    }

    public function getBrowserAttribute(): string
    {
        $userAgent = $this->user_agent ?? '';

        if (stripos($userAgent, 'Chrome') !== false) return 'Chrome';
        if (stripos($userAgent, 'Firefox') !== false) return 'Firefox';
        if (stripos($userAgent, 'Safari') !== false) return 'Safari';
        if (stripos($userAgent, 'Edge') !== false) return 'Edge';
        if (stripos($userAgent, 'Opera') !== false) return 'Opera';

        return 'Unknown';
    }

    public function getWaktuAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    public function getTanggalLengkapAttribute(): string
    {
        return $this->created_at->format('d F Y, H:i:s');
    }

    // ==========================================
    // STATIC METHODS
    // ==========================================

    /**
     * Create log with simpler syntax
     */
    public static function log(string $aktivitas, string $modul, $subjek = null, string $deskripsi = null): void
    {
        try {
            self::create([
                'user_id' => auth()->id(),
                'aktivitas' => $aktivitas,
                'modul' => $modul,
                'subjek_type' => $subjek ? get_class($subjek) : null,
                'subjek_id' => $subjek?->id,
                'deskripsi' => $deskripsi,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            // Silent fail
            \Log::error('Failed to create log: ' . $e->getMessage());
        }
    }
}
