<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'topic_id', 
        'parent_id',    // <-- NEW
        'user_id', 
        'content', 
        'is_private'
    ];

    protected $casts = [
        'is_private' => 'boolean',
    ];

    // Relationships
    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function excludedUsers()
    {
        return $this->belongsToMany(User::class, 'post_exclusions', 'post_id', 'excluded_user_id')
                    ->withTimestamps();
    }

    // NEW: Parent post (for threaded replies)
    public function parent()
    {
        return $this->belongsTo(Post::class, 'parent_id');
    }

    // NEW: Children replies
    public function children()
    {
        return $this->hasMany(Post::class, 'parent_id')->with('children', 'author');
    }

    // NEW: Likes
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

    // Privacy Filter Scope (existing)
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