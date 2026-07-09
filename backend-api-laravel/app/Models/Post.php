<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'topic_id', 
        'parent_id',    // Threaded reply support
        'user_id', 
        'content', 
        'is_private'
    ];

    protected $casts = [
        'is_private' => 'boolean',
    ];

    // --- Relationships ---

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function user()
    {
        return $this->author(); // Simply forwards the request to your author relationship
    }

    /**
     * Relationship: Many-to-Many (Content Security Privacy Filter)
     * Maps out exactly which restricted users are blocked from seeing this post row.
     */
    public function excludedUsers()
    {
        return $this->belongsToMany(User::class, 'post_exclusions', 'post_id', 'excluded_user_id')
                    ->withTimestamps();
    }

    // Parent post (for threaded replies)
    public function parent()
    {
        return $this->belongsTo(Post::class, 'parent_id');
    }

    // Children replies
    public function children()
    {
        return $this->hasMany(Post::class, 'parent_id')->with('children', 'author');
    }

    // Likes & Analytics Trackers
    public function likes()
    {
        return $this->hasMany(PostLike::class);
    }

    public function likedByUsers()
    {
        return $this->belongsToMany(User::class, 'post_likes');
    }

    public function getLikesCountAttribute()
    {
        return $this->likes()->count();
    }

    public function isLikedByUser($userId)
    {
        return $this->likes()->where('user_id', $userId)->exists();
    }

    // Privacy Filter Scope
    public function scopeVisibleToUser($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('is_private', false)
              ->orWhere('user_id', $userId);
        })->whereDoesntHave('excludedUsers', function ($q) use ($userId) {
            $q->where('excluded_user_id', $userId);
        });
    }
}