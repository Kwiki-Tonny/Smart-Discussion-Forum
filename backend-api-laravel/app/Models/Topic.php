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
}