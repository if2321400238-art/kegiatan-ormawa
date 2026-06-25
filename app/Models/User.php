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
        'fakultas_id',
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

    public function ormawas()
    {
        return $this->belongsToMany(
            Ormawa::class,
            'ormawa_users',
            'user_id',
            'ormawa_id'
        )->withPivot('jabatan', 'aktif')
            ->withTimestamps();
    }

    public const ROLE_ORMAWA = 'ormawa';
    public const ROLE_BAUAK = 'bauak';
    public const ROLE_WAREK3 = 'warek3';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_DOSEN = 'dosen';
    public const ROLE_DEKAN = 'dekan';
    public const ROLE_REKTOR = 'rektor';
    public const ROLE_PP = 'pp';

    public static function allowedRoles(): array
    {
        return [
            self::ROLE_ORMAWA,
            self::ROLE_BAUAK,
            self::ROLE_WAREK3,
            self::ROLE_ADMIN,
            self::ROLE_DOSEN,
            self::ROLE_DEKAN,
            self::ROLE_REKTOR,
            self::ROLE_PP,
        ];
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    public function isOrmawa(): bool
    {
        return $this->role === self::ROLE_ORMAWA;
    }

    public function isBauak(): bool
    {
        return $this->role === self::ROLE_BAUAK;
    }

    public function isWarek3(): bool
    {
        return $this->role === self::ROLE_WAREK3;
    }

    public function isDosen(): bool
    {
        return $this->role === self::ROLE_DOSEN;
    }

    public function isDekan(): bool
    {
        return $this->role === self::ROLE_DEKAN;
    }

    public function isRektor(): bool
    {
        return $this->role === self::ROLE_REKTOR;
    }

    public function isPP(): bool
    {
        return $this->role === self::ROLE_PP;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function fakultas()
    {
        return $this->belongsTo(\App\Models\Fakultas::class);
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
            self::ROLE_ORMAWA => 'Ormawa',
            self::ROLE_BAUAK => 'BAUAK',
            self::ROLE_WAREK3 => 'Wakil Rektor III',
            self::ROLE_ADMIN => 'Administrator',
            self::ROLE_DOSEN => 'Dosen Pembina',
            self::ROLE_DEKAN => 'Dekan',
            self::ROLE_REKTOR => 'Rektor',
            self::ROLE_PP => 'Kepala/Wakil PP',
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
