<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramConnectionCode extends Model
{
    protected $fillable = ['user_id', 'code_hash', 'code_digest', 'attempts', 'expires_at'];

    protected $casts = ['expires_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
