<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizAnswer extends Model
{
    protected $fillable = [
        'submission_id',
        'question_id',
        'answer',
        'is_correct',
        'points_earned',
    ];
}