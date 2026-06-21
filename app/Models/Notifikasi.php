<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    use HasFactory;

    protected $table = 'notifikasi';

    protected $fillable = [
        'user_id',
        'telegram_id',
        'judul',
        'pesan',
        'link',
        'tipe',
        'dibaca',
        'dibaca_pada',
        'delivery_channels',
        'delivery_status',
        'read_at',
    ];

    protected $casts = [
        'dibaca' => 'boolean',
        'dibaca_pada' => 'datetime',
        'delivery_channels' => 'json',
        'read_at' => 'datetime',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopeUnread($query)
    {
        return $query->where('dibaca', false);
    }

    public function scopeRead($query)
    {
        return $query->where('dibaca', true);
    }

    public function scopeByType($query, string $tipe)
    {
        return $query->where('tipe', $tipe);
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    public function getTipeBadgeAttribute(): string
    {
        $badges = [
            'info' => 'info',
            'success' => 'success',
            'warning' => 'warning',
            'error' => 'danger',
        ];

        return $badges[$this->tipe] ?? 'secondary';
    }

    public function getTipeIconAttribute(): string
    {
        $icons = [
            'info' => 'info-circle',
            'success' => 'check-circle',
            'warning' => 'exclamation-triangle',
            'error' => 'times-circle',
        ];

        return $icons[$this->tipe] ?? 'bell';
    }

    public function getWaktuAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    public function markAsRead(): void
    {
        $this->update([
            'dibaca' => true,
            'dibaca_pada' => now(),
            'read_at' => now(),
        ]);
    }

    public function getDeliveryStatusBadgeAttribute(): string
    {
        $badges = [
            'pending' => 'warning',
            'sent' => 'success',
            'failed' => 'danger',
            'delivered' => 'info',
        ];

        return $badges[$this->delivery_status] ?? 'secondary';
    }

    public function getDeliveryStatusLabelAttribute(): string
    {
        $labels = [
            'pending' => 'Sedang Dikirim',
            'sent' => 'Terkirim',
            'failed' => 'Gagal',
            'delivered' => 'Diterima',
        ];

        return $labels[$this->delivery_status] ?? ucfirst($this->delivery_status);
    }

    public function getChannelSummaryAttribute(): string
    {
        if (!$this->delivery_channels) {
            return 'In-app';
        }

        $channels = array_keys((array) $this->delivery_channels);
        return implode(', ', array_map('ucfirst', $channels));
    }

    public function isSentVia(string $channel): bool
    {
        if (!$this->delivery_channels) {
            return false;
        }

        return isset($this->delivery_channels[$channel]) && $this->delivery_channels[$channel] === 'sent';
    }

    public function isUnread(): bool
    {
        return !$this->dibaca;
    }

    public function isRead(): bool
    {
        return $this->dibaca;
    }
}
