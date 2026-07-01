<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlacklistLog extends Model
{
    protected $fillable = ['user_id', 'reason', 'action_type', 'expires_at'];
    
    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}