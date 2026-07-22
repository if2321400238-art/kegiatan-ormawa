<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fakultas extends Model
{
    use HasFactory;

    protected $table = 'fakultas';

    protected $fillable = [
        'nama',
        'dekan_user_id',
    ];

    public function dekan()
    {
        return $this->belongsTo(User::class, 'dekan_user_id');
    }

    public function ormawas()
    {
        return $this->hasMany(Ormawa::class, 'fakultas_id');
    }

    public function programStudi()
    {
        return $this->hasMany(ProgramStudi::class);
    }
}
