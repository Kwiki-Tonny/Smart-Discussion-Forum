<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $fillable = [
        'title',
        'group_id',
        'duration',
        'allowed_categories',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected $casts = [
        'allowed_categories' => 'array',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function questions()
    {
        return $this->hasMany(QuizQuestion::class)->orderBy('order');
    }

    public function submissions()
    {
        return $this->hasMany(QuizSubmission::class);
    }

    // Check if quiz is currently active
    public function isActive()
    {
        return $this->is_active &&
               $this->starts_at <= now() &&
               $this->ends_at >= now();
    }

    // Check if quiz has ended
    public function hasEnded()
    {
        return $this->ends_at < now() || !$this->is_active;
    }

    // Check if quiz has started
    public function hasStarted()
    {
        return $this->starts_at <= now();
    }
}