<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shortener extends Model
{
    protected $fillable = [
        'long_url',
        'short_url',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
