<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'username',
        'email',
        'password',
        'role',
        'nama',
        'no_hp',
        'telegram_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function ormawa()
    {
        return $this->hasOne(Ormawa::class);
    }

    public function notifikasi()
    {
        return $this->hasMany(Notifikasi::class);
    }

    public function logAktivitas()
    {
        return $this->hasMany(LogAktivitas::class);
    }

    public function verifikasiBauak()
    {
        return $this->hasMany(VerifikasiBauak::class, 'user_bauak_id');
    }

    public function persetujuanWarek3()
    {
        return $this->hasMany(PersetujuanWarek3::class, 'user_warek3_id');
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    public function isOrmawa(): bool
    {
        return $this->role === 'ormawa';
    }

    public function isBauak(): bool
    {
        return $this->role === 'bauak';
    }

    public function isWarek3(): bool
    {
        return $this->role === 'warek3';
    }

    

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function hasTelegram(): bool
    {
        return !empty($this->telegram_id);
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    public function getRoleLabelAttribute(): string
    {
        $labels = [
            'ormawa' => 'Ormawa',
            'bauak' => 'BAUAK',
            'warek3' => 'Wakil Rektor III',
            'admin' => 'Administrator',
        ];

        return $labels[$this->role] ?? $this->role;
    }

    public function getAvatarAttribute(): string
    {
        // Generate avatar from name initials
        $name = $this->nama ?? $this->username;
        $initials = collect(explode(' ', $name))
            ->map(fn($word) => strtoupper(substr($word, 0, 1)))
            ->take(2)
            ->join('');

        return "https://ui-avatars.com/api/?name={$initials}&background=random";
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }

}
