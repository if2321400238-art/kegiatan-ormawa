<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LpjVersiDokumen extends Model
{
    protected $table = 'lpj_versi_dokumen';

    protected $fillable = ['lpj_id', 'versi', 'nama_file', 'file_path', 'uploaded_by'];

    public function lpj()
    {
        return $this->belongsTo(LaporanPertanggungjawaban::class, 'lpj_id');
    }

    public function pengunggah()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getFileUrlAttribute(): string
    {
        return asset('storage/'.$this->file_path);
    }
}
