<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = ['topic_id', 'user_id', 'content', 'is_private'];

    // Cast boolean attributes natively out of the tinyint column
    protected $casts = [
        'is_private' => 'boolean',
    ];

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
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
}