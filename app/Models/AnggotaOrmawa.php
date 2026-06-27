<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnggotaOrmawa extends Model
{
    use HasFactory;

    protected $table = 'anggota_ormawa';

    protected $fillable = [
        'ormawa_id',
        'user_id',
        'jabatan',
        'status',
    ];

    public function ormawa()
    {
        return $this->belongsTo(Ormawa::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
