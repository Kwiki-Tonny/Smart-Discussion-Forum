<?php

/**
 * Namespace: App\Models
 * 
 * This namespace contains the Eloquent models for the application. 
 * Each class here represents a database table and defines how the 
 * application interacts with that data.
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Post Model
 * 
 * Represents an individual message, reply, or contribution within a discussion Topic.
 * 
 * Key Architectural Features:
 * 1. Threaded Conversations: Supports nested replies via self-referential 
 *    `parent` and `children` relationships, allowing for deep discussion threads.
 * 2. Granular Privacy: Implements a robust visibility system where posts can be 
 *    marked as private, or explicitly hidden from specific users via an exclusion list.
 * 3. Offline-First Sync Support: Allows `created_at` to be mass-assigned so that 
 *    posts drafted while the desktop client was offline retain their original 
 *    client-side timestamps when synchronized to the server.
 * 4. API-Ready Serialization: Automatically appends computed attributes (like 
 *    `likes_count`) to JSON responses, reducing the need for frontend data manipulation.
 * 
 * @property int $id The unique identifier for this post.
 * @property int $topic_id The ID of the topic this post belongs to.
 * @property int|null $parent_id The ID of the parent post (if this is a reply).
 * @property int $user_id The ID of the user who authored the post.
 * @property string $content The text body of the post.
 * @property bool $is_private Whether the post is restricted to the author and non-excluded users.
 * @property bool $is_pinned Whether the post is pinned to the top of the topic.
 * @property array|null $attachments JSON-encoded array of file paths or URLs attached to the post.
 * @property \Illuminate\Support\Carbon $created_at Timestamp of creation (preserved from client during sync).
 * @property \Illuminate\Support\Carbon $updated_at Timestamp of last modification.
 */
class Post extends Model
{
    /**
     * The attributes that can be safely mass-assigned.
     * 
     * Security Note: Listing these fields protects against Mass Assignment Vulnerabilities.
     * 
     * Special Note on `created_at`: 
     * Normally, Eloquent manages timestamps automatically. However, `created_at` is 
     * explicitly made fillable here to support the offline-first desktop client. 
     * When a user writes a post while offline, the client records the local time. 
     * Upon reconnection, the SyncWorker pushes this original timestamp to the server 
     * to maintain accurate chronological ordering, rather than using the server's sync time.
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
     * The attributes that should be cast to native PHP types.
     * 
     * This ensures that boolean flags are returned as actual `true`/`false` rather 
     * than `1`/`0`, and that the JSON `attachments` column is automatically 
     * hydrated into a usable PHP array when accessed.
     */
    protected $casts = [
        'is_private' => 'boolean',
        'is_pinned' => 'boolean',
        'attachments' => 'array',
    ];

    /**
     * The attributes that should be automatically appended to JSON responses.
     * 
     * When this model is serialized to JSON for the API, these computed accessors 
     * will be included automatically. This provides the frontend with immediate 
     * access to engagement metrics without requiring separate API calls.
     */
    protected $appends = [
        'likes_count',
        'is_liked',
    ];

    // =========================================================================
    //  RELATIONSHIPS
    //  Defines how this Post connects to other entities in the database.
    // =========================================================================

    /**
     * Get the discussion topic that this post belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    /**
     * Get the user who authored this post.
     * 
     * Note: We explicitly specify 'user_id' as the foreign key here for clarity, 
     * though Eloquent would guess it correctly based on the method name 'author'.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the parent post that this post is replying to.
     * 
     * This is a self-referential relationship that enables threaded/nested conversations.
     * If `parent_id` is null, this post is a top-level reply to the Topic itself.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(Post::class, 'parent_id');
    }

    /**
     * Get all direct replies (children) to this post.
     * 
     * Architectural Note: This relationship eagerly loads its own `children` 
     * and the `author` of those children. This recursive eager-loading pattern 
     * is highly effective for rendering nested comment trees in the UI while 
     * minimizing N+1 query problems.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children()
    {
        return $this->hasMany(Post::class, 'parent_id')->with('children', 'author');
    }

    /**
     * Get the raw "like" records associated with this post.
     * 
     * Useful for counting total likes or checking if a specific like record exists.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function likes()
    {
        return $this->hasMany(PostLike::class);
    }

    /**
     * Get the actual User models who have liked this post.
     * 
     * This is a Many-to-Many relationship that bypasses the intermediate PostLike 
     * model to give direct access to the User profiles of the likers.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function likedByUsers()
    {
        return $this->belongsToMany(User::class, 'post_likes');
    }

    /**
     * Get the users who are explicitly blocked from viewing this post.
     * 
     * This powers the granular privacy feature. If a post is private, it is 
     * visible to everyone EXCEPT the users listed in this relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function excludedUsers()
    {
        return $this->belongsToMany(User::class, 'post_exclusions', 'post_id', 'excluded_user_id')
                    ->withTimestamps();
    }

    // =========================================================================
    //  COMPUTED ATTRIBUTES (ACCESSORS)
    //  Virtual fields generated on the fly for API serialization.
    // =========================================================================

    /**
     * Accessor for the total number of likes on this post.
     * 
     * Note: Because this executes a `COUNT()` query every time it is accessed, 
     * it is highly recommended to use the `withCount('likes')` eager-loading 
     * method in your controllers or scopes to prevent N+1 query performance issues 
     * when rendering lists of posts.
     *
     * @return int
     */
    public function getLikesCountAttribute()
    {
        return $this->likes()->count();
    }

    /**
     * Accessor to check if the currently authenticated user has liked this post.
     * 
     * Architectural Note: By default, this returns `false`. In the controller, 
     * when preparing a list of posts for a specific user, this attribute is 
     * typically overridden or populated dynamically based on the user's session 
     * to avoid running a separate database query for every single post in a list.
     *
     * @return bool
     */
    public function getIsLikedAttribute()
    {
        // This will be set dynamically in the controller when the user is known.
        // By default, it returns false.
        return false;
    }

    // =========================================================================
    //  HELPER METHODS
    //  Utility functions for common business logic checks.
    // =========================================================================

    /**
     * Check if a specific user has liked this post.
     * 
     * This runs a lightweight `EXISTS` query on the database, which is much 
     * faster than loading all likes into memory and counting them.
     *
     * @param int $userId The ID of the user to check.
     * @return bool True if the user has liked the post, false otherwise.
     */
    public function isLikedByUser($userId)
    {
        return $this->likes()->where('user_id', $userId)->exists();
    }

    // =========================================================================
    //  QUERY SCOPES
    //  Reusable query constraints to keep controller logic clean and DRY.
    // =========================================================================

    /**
     * Privacy Scope: Filters the query to only return posts visible to a specific user.
     * 
     * Visibility Logic:
     * A post is visible if:
     * 1. It is NOT private (is_private = false), OR
     * 2. It IS private, but the requesting user is the author (user_id = $userId).
     * 
     * AND
     * 
     * 3. The requesting user is NOT in the `excludedUsers` blocklist for that post.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId The ID of the user requesting the data.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVisibleToUser($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            // Condition 1 & 2: Public posts OR private posts authored by the user
            $q->where('is_private', false)
              ->orWhere('user_id', $userId);
        })->whereDoesntHave('excludedUsers', function ($q) use ($userId) {
            // Condition 3: Ensure the user is not explicitly blocked from seeing this post
            $q->where('excluded_user_id', $userId);
        });
    }

    /**
     * API Optimization Scope: Eager-loads necessary relationships for frontend rendering.
     * 
     * This scope is designed to be used when returning a list of posts via the API.
     * It optimizes the payload by:
     * 1. Loading only the necessary fields for the author (id, name, email, role).
     * 2. Appending the total `likes_count` via a highly optimized `COUNT()` subquery.
     * 3. Checking if the *specific requesting user* has liked the post, allowing 
     *    the frontend to immediately render the "Liked" heart icon without extra calls.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId The ID of the currently authenticated user.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithAuthorAndLikes($query, $userId)
    {
        return $query->with('author:id,name,email,role')
                     ->withCount('likes')
                     ->with(['likes' => function ($q) use ($userId) {
                         // Only load the like record if it belongs to the current user
                         $q->where('user_id', $userId);
                     }]);
    }
}