<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $fillable = ['group_id', 'title', 'duration', 'allowed_categories'];
    
    protected $casts = [
        'allowed_categories' => 'array', // JSON cast
        'duration' => 'integer',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function submissions()
    {
        return $this->hasMany(QuizSubmission::class);
    }
}