<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'created_by'];

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

    /**
     * Relationship: The lecturer who created this group
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all students in this group
     */
    public function students()
    {
        return $this->users()->where('role', 'student');
    }

    /**
     * Check if a user is the creator/admin of this group
     */
    public function isCreatedBy($userId)
    {
        return $this->created_by == $userId;
    }
}