<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VerifikasiLpj extends Model
{
    protected $table = 'verifikasi_lpj';

    protected $fillable = ['lpj_id', 'user_bauak_id', 'status', 'catatan', 'tanggal_verifikasi'];

    protected $casts = ['tanggal_verifikasi' => 'datetime'];

    public function lpj()
    {
        return $this->belongsTo(LaporanPertanggungjawaban::class, 'lpj_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_bauak_id');
    }
}
