<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'topic_id',
        'parent_id',
        'user_id',
        'content',
        'is_private',
        'is_pinned',
        'attachments',
        'created_at',   // ← Added to preserve client timestamps during sync
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'is_private' => 'boolean',
        'is_pinned' => 'boolean',
        'attachments' => 'array',
    ];

    /**
     * The attributes that should be appended to JSON responses.
     */
    protected $appends = [
        'likes_count',
        'is_liked',
    ];

    // ─── RELATIONSHIPS ─────────────────────────────────────────────

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function parent()
    {
        return $this->belongsTo(Post::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Post::class, 'parent_id')->with('children', 'author');
    }

    public function likes()
    {
        return $this->hasMany(PostLike::class);
    }

    public function likedByUsers()
    {
        return $this->belongsToMany(User::class, 'post_likes');
    }

    public function excludedUsers()
    {
        return $this->belongsToMany(User::class, 'post_exclusions', 'post_id', 'excluded_user_id')
                    ->withTimestamps();
    }

    // ─── COMPUTED ATTRIBUTES ──────────────────────────────────────

    public function getLikesCountAttribute()
    {
        return $this->likes()->count();
    }

    public function getIsLikedAttribute()
    {
        // This will be set dynamically in the controller when the user is known.
        // By default, it returns false.
        return false;
    }

    // ─── HELPERS ───────────────────────────────────────────────────

    public function isLikedByUser($userId)
    {
        return $this->likes()->where('user_id', $userId)->exists();
    }

    // ─── SCOPES ────────────────────────────────────────────────────

    /**
     * Privacy filter: only show posts visible to a specific user.
     */
    public function scopeVisibleToUser($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('is_private', false)
              ->orWhere('user_id', $userId);
        })->whereDoesntHave('excludedUsers', function ($q) use ($userId) {
            $q->where('excluded_user_id', $userId);
        });
    }

    /**
     * Eager‑load author and likes for API responses.
     */
    public function scopeWithAuthorAndLikes($query, $userId)
    {
        return $query->with('author:id,name,email,role')
                     ->withCount('likes')
                     ->with(['likes' => function ($q) use ($userId) {
                         $q->where('user_id', $userId);
                     }]);
    }
}