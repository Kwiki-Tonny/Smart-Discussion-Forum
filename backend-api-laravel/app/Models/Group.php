<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    // Mass assignment protection: allows these fields to be filled programmatically
    protected $fillable = ['name', 'description'];

    /**
     * Relationship: One-to-Many (Groups <-> Topics)
     * A group houses multiple discussion threads.
     */
    public function topics()
    {
        return $this->hasMany(Topic::class);
    }

    /**
     * Relationship: Many-to-Many via Bridge Table (Groups <-> Users)
     * Tracks student memberships and targets the rules onboarding gate.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'group_user')
                    ->withPivot('has_agreed_rules') // Includes the gate column in queries
                    ->withTimestamps();
    }
}
