<?php

/**
 * Namespace: App\Models
 * 
 * This namespace contains the Eloquent models for the application. 
 * Each class here represents a database table and defines how the 
 * application interacts with that data, including relationships and business logic.
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Group Model
 * 
 * Represents a discussion group, course, or community space within the application.
 * A Group serves as the primary container for Topics and defines the boundary 
 * for user membership and permissions.
 * 
 * Key Architectural Features:
 * 1. Membership Management: Utilizes a Many-to-Many relationship with Users, 
 *    tracking not just membership, but also compliance (e.g., whether the user 
 *    has agreed to the group's specific rules).
 * 2. Hierarchical Content: Acts as the parent entity for all Topics within it, 
 *    ensuring content is logically organized and scoped.
 * 3. Ownership & Authorization: Tracks the creator of the group, enabling 
 *    straightforward authorization checks for administrative actions (editing, 
 *    deleting, or managing the group).
 * 
 * @property int $id The unique identifier for this group.
 * @property string $name The display name of the group.
 * @property string|null $description A brief summary or description of the group's purpose.
 * @property int $created_by The ID of the user (typically a lecturer/admin) who created the group.
 * @property \Illuminate\Support\Carbon $created_at Timestamp of group creation.
 * @property \Illuminate\Support\Carbon $updated_at Timestamp of last modification.
 */
class Group extends Model
{
    use HasFactory;

    /**
     * The attributes that can be safely mass-assigned.
     * 
     * Security Note: By explicitly defining these fields, we protect the 
     * application from Mass Assignment Vulnerabilities. This ensures that 
     * malicious users cannot forge requests to modify sensitive fields 
     * (like `id` or `created_at`) when creating or updating a group.
     * 
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'created_by',
    ];

    // =========================================================================
    //  RELATIONSHIPS
    //  Defines how this Group connects to other entities in the database.
    // =========================================================================

    /**
     * Get all topics that belong to this group.
     * 
     * This is a One-to-Many relationship. It allows us to easily fetch all 
     * discussions within a specific group (e.g., `$group->topics`) or eager-load 
     * them to prevent N+1 query issues when rendering a group directory.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function topics()
    {
        return $this->hasMany(Topic::class);
    }

    /**
     * Get all users who are members of this group.
     * 
     * This is a Many-to-Many relationship utilizing the `group_user` bridge table.
     * 
     * Architectural Note: We use `withPivot('has_agreed_rules')` to track whether 
     * a specific user has accepted the group's terms of participation. This is 
     * critical for the application's onboarding flow, ensuring users cannot 
     * interact with group content until they have explicitly agreed to the rules.
     * `withTimestamps()` automatically manages `created_at` and `updated_at` 
     * for the membership record in the pivot table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'group_user')
                    ->withPivot('has_agreed_rules')
                    ->withTimestamps();
    }

    /**
     * Get the user who created this group.
     * 
     * This is an inverse One-to-Many (BelongsTo) relationship. It links the 
     * `created_by` foreign key back to the `users` table, allowing us to easily 
     * display the creator's name or verify their administrative privileges.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // =========================================================================
    //  HELPER METHODS & SCOPES
    //  Utility functions for common business logic and query filtering.
    // =========================================================================

    /**
     * Get only the users who are students within this group.
     * 
     * This scopes the existing `users()` relationship to filter by the 'student' 
     * role. It is highly useful for generating class rosters, sending student-only 
     * announcements, or calculating student-specific engagement metrics.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function students()
    {
        return $this->users()->where('role', 'student');
    }

    /**
     * Check if a specific user is the creator (and thus, the admin) of this group.
     * 
     * This is a simple, efficient authorization helper used in controllers or 
     * policies to determine if a user has the right to modify or delete the group.
     *
     * @param int $userId The ID of the user to check.
     * @return bool True if the user created the group, false otherwise.
     */
    public function isCreatedBy($userId)
    {
        return $this->created_by == $userId;
    }
}