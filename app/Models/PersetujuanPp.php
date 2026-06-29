<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersetujuanPp extends Model
{
    use HasFactory;

    protected $table = 'persetujuan_pp';

    protected $fillable = [
        'pengajuan_id', 'user_pp_id', 'catatan', 'status', 'tanggal_acc',
    ];

    protected $casts = ['tanggal_acc' => 'datetime'];

    public function pengajuanKegiatan()
    {
        return $this->belongsTo(PengajuanKegiatan::class, 'pengajuan_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_pp_id');
    }
}
