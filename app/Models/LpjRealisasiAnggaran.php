<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LpjRealisasiAnggaran extends Model
{
    protected $table = 'lpj_realisasi_anggaran';

    protected $fillable = ['lpj_id', 'uraian', 'anggaran_rencana', 'anggaran_realisasi', 'keterangan'];

    protected $casts = ['anggaran_rencana' => 'decimal:2', 'anggaran_realisasi' => 'decimal:2'];

    public function lpj()
    {
        return $this->belongsTo(LaporanPertanggungjawaban::class, 'lpj_id');
    }
}
