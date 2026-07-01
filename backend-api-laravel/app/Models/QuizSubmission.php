<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizSubmission extends Model
{
    protected $fillable = ['quiz_id', 'user_id', 'score', 'answers_payload', 'is_auto_submitted'];
    
    protected $casts = [
        'answers_payload' => 'array',
        'score' => 'decimal:2',
        'is_auto_submitted' => 'boolean',
    ];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}