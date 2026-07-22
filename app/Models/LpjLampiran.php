<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LpjLampiran extends Model
{
    protected $table = 'lpj_lampiran';

    protected $fillable = ['lpj_id', 'jenis', 'nama_file', 'file_path'];

    public function lpj()
    {
        return $this->belongsTo(LaporanPertanggungjawaban::class, 'lpj_id');
    }

    public function getFileUrlAttribute(): string
    {
        return asset('storage/'.$this->file_path);
    }
}
