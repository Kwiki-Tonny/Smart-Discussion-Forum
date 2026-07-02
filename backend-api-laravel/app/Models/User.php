<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // We will install this in Phase 2, but add it now
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'last_communicated_at',   
        'blacklist_expires_at',   
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

        protected $casts = [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_communicated_at' => 'datetime',
            'blacklist_expires_at' => 'datetime',
            'role' => 'string',
            'status' => 'string',
        ];

    // --- Relationships ---
    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_user')
                    ->withPivot('has_agreed_rules')
                    ->withTimestamps();
    }

    public function topics()
    {
        return $this->hasMany(Topic::class, 'creator_id');
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function excludedPosts()
    {
        return $this->belongsToMany(Post::class, 'post_exclusions', 'excluded_user_id', 'post_id');
    }

    public function interactions()
    {
        return $this->hasMany(UserInteraction::class);
    }

    public function quizSubmissions()
    {
        return $this->hasMany(QuizSubmission::class);
    }

    public function blacklistLogs()
    {
        return $this->hasMany(BlacklistLog::class);
    }
}