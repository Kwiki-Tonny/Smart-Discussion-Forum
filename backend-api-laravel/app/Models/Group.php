<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    /**
     * Relationship: One-to-Many (Groups <-> Topics)
     */
    public function topics()
    {
        return $this->hasMany(Topic::class);
    }

    /**
     * Relationship: Many-to-Many via Bridge Table (Groups <-> Users)
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'group_user')
                    ->withPivot('has_agreed_rules')
                    ->withTimestamps();
    }
}