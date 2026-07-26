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

use Illuminate\Database\Eloquent\Model;

/**
 * User Interaction Model
 * 
 * Represents a specific, discrete action taken by a user on a discussion topic.
 * 
 * Purpose in the Application:
 * This model serves as an analytics and audit trail mechanism. Instead of 
 * scattering activity tracking across multiple tables, this centralized model 
 * captures key engagement events (like viewing, liking, downloading, or commenting) 
 * in a uniform structure. 
 * 
 * Key Architectural Features:
 * 1. Behavioral Analytics: Provides the raw data needed to calculate metrics like 
 *    "most viewed topics", "most active users", or "trending discussions".
 * 2. Lightweight Design: By storing only the essential foreign keys and an 
 *    `action_type` string, this table remains highly performant even as it 
 *    scales to millions of rows.
 * 3. Extensibility: New interaction types can be added simply by using a new 
 *    string value for `action_type`, without requiring database schema changes.
 * 
 * @property int $id The unique identifier for this interaction record.
 * @property int $user_id The ID of the user who performed the action.
 * @property int $topic_id The ID of the topic that was acted upon.
 * @property string $action_type The type of action performed (e.g., 'view', 'like', 'download', 'comment').
 * @property \Illuminate\Support\Carbon $created_at Timestamp of when the interaction occurred.
 * @property \Illuminate\Support\Carbon $updated_at Timestamp of last modification.
 */
class UserInteraction extends Model
{
    /**
     * The attributes that can be safely mass-assigned.
     * 
     * Security Note: By explicitly defining these fields, we protect the 
     * application from Mass Assignment Vulnerabilities. This ensures that 
     * when logging an interaction, only these specific, intended attributes 
     * can be populated.
     * 
     * Field Breakdown:
     * - `user_id`: Identifies the actor who performed the action.
     * - `topic_id`: Identifies the target topic of the action.
     * - `action_type`: A string categorizing the interaction. Common values 
     *   include 'view' (topic opened), 'like' (post/topic liked), 'download' 
     *   (attachment downloaded), or 'comment' (reply posted).
     * 
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id', 
        'topic_id', 
        'action_type' // 'view', 'like', 'download', 'comment'
    ];

    // =========================================================================
    //  RELATIONSHIPS
    //  Defines how this Interaction connects to other entities in the database.
    // =========================================================================

    /**
     * Get the user who performed this interaction.
     * 
     * This is an inverse One-to-Many (BelongsTo) relationship. It allows the 
     * application to easily fetch the user's profile details when analyzing 
     * interaction data (e.g., displaying an "Activity Feed" showing which 
     * users recently engaged with specific content).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the topic that was the target of this interaction.
     * 
     * This is an inverse One-to-Many (BelongsTo) relationship. It is essential 
     * for aggregating data at the topic level. For example, it allows the 
     * application to run queries like "Count all 'view' interactions for Topic X" 
     * to determine its popularity or to recommend similar topics to the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }
}