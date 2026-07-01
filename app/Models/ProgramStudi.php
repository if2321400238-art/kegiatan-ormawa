<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramStudi extends Model
{
    protected $table = 'program_studi';

    protected $fillable = ['fakultas_id', 'kaprodi_user_id', 'nama', 'kode', 'profile_url', 'is_lainnya'];

    protected $casts = ['is_lainnya' => 'boolean'];

    public function fakultas()
    {
        return $this->belongsTo(Fakultas::class);
    }

    public function kaprodi()
    {
        return $this->belongsTo(User::class, 'kaprodi_user_id');
    }

    public function ormawas()
    {
        return $this->hasMany(Ormawa::class, 'prodi_id');
    }
}
