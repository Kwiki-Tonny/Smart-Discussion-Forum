<?php

/**
 * Namespace: App\Models
 * 
 * This namespace contains the Eloquent models for the application. 
 * Each class here represents a database table and defines how the 
 * application interacts with that data, including relationships, 
 * business logic, and data transformation rules.
 */
namespace App\Models;

/**
 * The base authentication model provided by Laravel.
 * Extending this class gives the User model built-in authentication capabilities,
 * including password verification, session management, and authorization gates.
 */
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Enables the model to receive and manage notifications (e.g., email, database).
 * This is essential for features like password reset emails, account verification, 
 * and system alerts.
 */
use Illuminate\Notifications\Notifiable;

/**
 * Laravel Sanctum trait for API token management.
 * Allows the user to generate and manage personal access tokens for 
 * authenticating API requests (e.g., from the desktop JavaFX client).
 */
use Laravel\Sanctum\HasApiTokens;

/**
 * Provides factory support for generating dummy data during testing and seeding.
 */
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * User Model
 * 
 * Represents the core identity and authentication entity within the application.
 * This model is the central hub for all user-related data, permissions, and 
 * interactions across the forum and quiz systems.
 * 
 * Key Architectural Features:
 * 1. Authentication & Authorization: Serves as the primary subject for Laravel's 
 *    built-in auth guards, Sanctum API tokens, and role-based access control (RBAC).
 * 2. Moderation & Compliance: Tracks moderation states via `status` and 
 *    `blacklist_expires_at`, allowing for temporary suspensions or permanent bans.
 * 3. Engagement Tracking: The `last_communicated_at` field helps identify active 
 *    vs. dormant users, which can be used for cleanup jobs or re-engagement campaigns.
 * 4. Comprehensive Relationships: Connects to almost every major entity in the 
 *    system (Groups, Topics, Posts, Quizzes, Moderation Logs), enabling rich 
 *    profile views and granular permission checks.
 * 
 * @property int $id The unique identifier for this user.
 * @property string $name The user's display name.
 * @property string $email The user's unique email address (used for login).
 * @property string $password The hashed password string.
 * @property string $role The user's system role (e.g., 'admin', 'lecturer', 'student').
 * @property string $status The account's operational state (e.g., 'active', 'suspended').
 * @property \Illuminate\Support\Carbon|null $email_verified_at Timestamp of email verification.
 * @property \Illuminate\Support\Carbon|null $last_communicated_at Timestamp of the user's last activity/communication.
 * @property \Illuminate\Support\Carbon|null $blacklist_expires_at Timestamp when a temporary ban/suspension lifts.
 * @property string|null $remember_token Token for "Remember Me" session persistence.
 */
class User extends Authenticatable
{
    /**
     * Traits included in the User model to provide core framework functionality.
     */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that can be safely mass-assigned.
     * 
     * Security Note: By explicitly defining these fields, we protect the 
     * application from Mass Assignment Vulnerabilities. This ensures that 
     * malicious users cannot forge requests to modify sensitive fields 
     * (like `id` or hypothetical admin flags) during registration or profile updates.
     * 
     * Field Breakdown:
     * - `name`: The user's display name.
     * - `email`: The unique login credential.
     * - `password`: The hashed authentication secret.
     * - `role`: Determines system-wide permissions (RBAC).
     * - `status`: Determines if the account is active, pending, or suspended.
     * - `last_communicated_at`: Tracks recent activity for engagement metrics.
     * - `blacklist_expires_at`: Stores the expiration time for temporary suspensions.
     * 
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'last_communicated_at',   
        'blacklist_expires_at',   
    ];

    /**
     * The attributes that should be hidden for serialization.
     * 
     * Security Note: These fields will never be included in JSON responses 
     * (e.g., when returning the User model via an API endpoint). This is 
     * critical for preventing the accidental exposure of sensitive credentials 
     * or internal session tokens to the frontend or external clients.
     * 
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native PHP types.
     * 
     * Architectural Note:
     * - `email_verified_at`, `last_communicated_at`, and `blacklist_expires_at` 
     *   are cast to Carbon instances, enabling powerful date manipulation 
     *   (e.g., `$user->blacklist_expires_at->isFuture()` to check if a ban is still active).
     * - `password` is cast to 'hashed', meaning if this field is updated via 
     *   mass assignment, Laravel will automatically hash the plaintext value 
     *   using Bcrypt/Argon2 before saving it to the database.
     * - `role` and `status` are explicitly cast to strings to ensure consistent 
     *   type checking, even if the database driver returns them differently.
     * 
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_communicated_at' => 'datetime',
        'blacklist_expires_at' => 'datetime',
        'role' => 'string',
        'status' => 'string',
    ];

    // =========================================================================
    //  RELATIONSHIPS
    //  Defines how this User connects to other entities in the database.
    // =========================================================================

    /**
     * Get all groups that this user is a member of.
     * 
     * This is a Many-to-Many relationship utilizing the `group_user` bridge table.
     * 
     * Architectural Note: We use `withPivot('has_agreed_rules')` to access the 
     * membership-specific data, allowing the application to check if the user 
     * has accepted the group's terms before allowing them to view or post content.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_user')
                    ->withPivot('has_agreed_rules')
                    ->withTimestamps();
    }

    /**
     * Get all discussion topics created by this user.
     * 
     * This is a One-to-Many relationship. It is used to display a user's 
     * contribution history on their profile page or to enforce limits on 
     * how many topics a new user can create in a given time period.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function topics()
    {
        return $this->hasMany(Topic::class, 'creator_id');
    }

    /**
     * Get all posts (replies) authored by this user.
     * 
     * This is a One-to-Many relationship. It is essential for tracking user 
     * engagement, calculating post counts, and allowing users to edit or 
     * delete their own contributions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Get all posts that have explicitly excluded this user from viewing them.
     * 
     * This is the inverse of the `excludedUsers` relationship on the Post model.
     * It powers the granular privacy feature, ensuring that when a user browses 
     * a topic, the query can filter out posts where this user ID is present in 
     * the `post_exclusions` table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function excludedPosts()
    {
        return $this->belongsToMany(Post::class, 'post_exclusions', 'excluded_user_id', 'post_id');
    }

    /**
     * Get all recorded user interactions (e.g., clicks, views, specific engagements).
     * 
     * This relationship tracks granular behavioral data, which can be used for 
     * analytics, personalization, or detecting anomalous activity.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function interactions()
    {
        return $this->hasMany(UserInteraction::class);
    }

    /**
     * Get all quiz attempts/submissions made by this user.
     * 
     * This is a One-to-Many relationship. It is critical for the learning 
     * management aspect of the application, allowing the system to retrieve 
     * a student's quiz history, calculate average scores, and prevent them 
     * from retaking a quiz if the rules forbid multiple attempts.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function quizSubmissions()
    {
        return $this->hasMany(QuizSubmission::class);
    }

    /**
     * Get the moderation and blacklist history for this user.
     * 
     * This is a One-to-Many relationship with the BlacklistLog model. It provides 
     * administrators with a complete audit trail of all disciplinary actions 
     * taken against the user, including the reasons and durations of any bans.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function blacklistLogs()
    {
        return $this->hasMany(BlacklistLog::class);
    }
}