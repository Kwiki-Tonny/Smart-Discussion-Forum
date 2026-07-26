<?php

/**
 * Namespace: App\Models
 * 
 * This namespace contains the Eloquent models for the application. 
 * Each class here represents a database table and defines how the 
 * application interacts with that data, including relationships and business logic.
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Post Like Model
 * 
 * Represents a single "like" action performed by a user on a specific post.
 * 
 * Purpose in the Application:
 * This model serves as the explicit pivot record for the Many-to-Many relationship 
 * between Users and Posts. By using a dedicated model instead of a hidden pivot table, 
 * we gain the ability to easily query, audit, or extend like data in the future 
 * (for example, adding a "like weight" or tracking the exact millisecond a like occurred).
 * 
 * @property int $id The unique identifier for this like record.
 * @property int $post_id The ID of the post that was liked.
 * @property int $user_id The ID of the user who performed the like action.
 * @property \Illuminate\Support\Carbon $created_at Timestamp of when the like was registered.
 * @property \Illuminate\Support\Carbon $updated_at Timestamp of last modification.
 */
class PostLike extends Model
{
    /**
     * The attributes that can be safely mass-assigned.
     * 
     * Security Note: Only the foreign keys are listed here. This protects the 
     * application from Mass Assignment Vulnerabilities. 
     * 
     * Note on Timestamps: `created_at` and `updated_at` are intentionally omitted 
     * from this array because Eloquent manages them automatically upon record 
     * creation and modification.
     * 
     * @var array<int, string>
     */
    protected $fillable = [
        'post_id',
        'user_id',
    ];

    // =========================================================================
    //  RELATIONSHIPS
    //  Defines how this Like record connects to other entities in the database.
    // =========================================================================

    /**
     * Get the post that this like belongs to.
     * 
     * This is the inverse of the `hasMany` relationship defined in the Post model.
     * It allows us to easily fetch the post's details when we only have the like 
     * record (e.g., `$like->post->title`).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the user who performed this like action.
     * 
     * This links the `user_id` foreign key back to the `users` table. It is 
     * particularly useful for generating "Liked by..." lists, where we need to 
     * display the avatars and names of the users who engaged with a post.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}