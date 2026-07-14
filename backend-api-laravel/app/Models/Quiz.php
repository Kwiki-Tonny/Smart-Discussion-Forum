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

    /**
     * Get the group that owns the quiz.
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Get the questions for the quiz.
     */
    public function questions()
    {
        return $this->hasMany(QuizQuestion::class)->orderBy('order');
    }

    /**
     * Get the submissions for the quiz.
     */
    public function submissions()
    {
        return $this->hasMany(QuizSubmission::class);
    }

    /**
     * Check if the quiz is currently active.
     * Conditions: is_active = true, started, and not ended.
     */
    public function isActive()
    {
        return $this->is_active &&
               $this->starts_at <= now() &&
               $this->ends_at >= now();
    }

    /**
     * Check if the quiz has ended.
     * Ends if the end time is past OR the quiz is inactive.
     */
    public function hasEnded()
    {
        return $this->ends_at < now() || !$this->is_active;
    }

    /**
     * Check if the quiz has started.
     */
    public function hasStarted()
    {
        return $this->starts_at <= now();
    }

    /**
     * Get the remaining time in seconds (if active).
     * Returns 0 if ended or not started.
     */
    public function getRemainingSeconds()
    {
        if (!$this->isActive()) {
            return 0;
        }
        return now()->diffInSeconds($this->ends_at, false);
    }

    /**
     * Get the quiz status label.
     */
    public function getStatusLabel()
    {
        if ($this->hasEnded()) {
            return 'Ended';
        } elseif (!$this->hasStarted()) {
            return 'Upcoming';
        } else {
            return 'Active';
        }
    }

    /**
     * Get the status color class.
     */
    public function getStatusColor()
    {
        if ($this->hasEnded()) {
            return 'text-[#666666] border-[#E5E5E5]';
        } elseif (!$this->hasStarted()) {
            return 'text-[#16A34A] border-[#16A34A]';
        } else {
            return 'text-[#D97706] border-[#D97706]';
        }
    }
}