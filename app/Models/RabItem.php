<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RabItem extends Model
{
    protected $fillable = ['rab_id', 'uraian', 'anggaran_rencana', 'keterangan'];

    protected $casts = ['anggaran_rencana' => 'decimal:2'];

    public function rab()
    {
        return $this->belongsTo(Rab::class);
    }
}
