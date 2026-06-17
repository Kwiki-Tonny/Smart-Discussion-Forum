<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    protected $fillable = ['group_id', 'title', 'creator_id', 'ml_category'];

    /**
     * Relationship: Inverse One-to-Many (Topics -> Group)
     * Tells Laravel that this thread belongs strictly to an isolated group node.
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Relationship: Inverse One-to-Many (Topics -> User)
     * Links the thread to the author profile anchor.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Relationship: One-to-Many (Topics <-> Posts)
     * Pulls the entire underlying cascade conversation feed.
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}