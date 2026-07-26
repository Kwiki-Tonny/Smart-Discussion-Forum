<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    protected $fillable = [
        'group_id',
        'title',
        'description',
        'creator_id',
        'ml_category',
        'is_private',        // <-- added
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Users who are allowed to see this private topic.
     */
    public function includedUsers()
    {
        return $this->belongsToMany(User::class, 'topic_user');
    }

    /**
     * Scope to filter topics visible to a given user.
     */
    public function scopeVisibleToUser($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('is_private', false)
              ->orWhere('creator_id', $userId)
              ->orWhereHas('includedUsers', function ($sub) use ($userId) {
                  $sub->where('user_id', $userId);
              });
        });
    }
}