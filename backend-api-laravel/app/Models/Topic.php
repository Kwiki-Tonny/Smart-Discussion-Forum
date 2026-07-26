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
 * Topic Model
 * 
 * Represents a discussion thread or conversation starter within a specific group.
 * 
 * Purpose in the Application:
 * A Topic serves as the primary container for all related posts and replies. 
 * It acts as the bridge between a Group (the broader community/category) and 
 * individual Posts (the actual conversation content). When a user wants to 
 * start a new discussion, they create a Topic; when they want to respond, 
 * they create Posts within that Topic.
 * 
 * Key Architectural Features:
 * 1. Hierarchical Organization: Topics belong to Groups, creating a clear 
 *    content hierarchy (Group → Topic → Posts) that enables logical navigation 
 *    and scoped content discovery.
 * 2. ML-Powered Categorization: The `ml_category` field stores the output of 
 *    machine learning models that automatically analyze the topic's title and 
 *    description to assign a category tag. This enables automated content routing, 
 *    trending topic detection, and intelligent recommendations without manual 
 *    user intervention.
 * 3. Ownership Tracking: The `creator_id` field establishes clear ownership, 
 *    enabling authorization checks for editing, deleting, or moderating the topic.
 * 4. Content Aggregation: Through the `posts()` relationship, a Topic can easily 
 *    retrieve all its associated discussion content, reply counts, and engagement 
 *    metrics for display in topic listings.
 * 
 * @property int $id The unique identifier for this topic.
 * @property int $group_id The ID of the group this topic belongs to.
 * @property string $title The main title or subject line of the discussion.
 * @property string|null $description The detailed body or opening description of the topic.
 * @property int $creator_id The ID of the user who created this topic.
 * @property string|null $ml_category The machine-learning derived category tag.
 * @property \Illuminate\Support\Carbon $created_at Timestamp of topic creation.
 * @property \Illuminate\Support\Carbon $updated_at Timestamp of last modification.
 */
class Topic extends Model
{
    /**
     * The attributes that can be safely mass-assigned.
     * 
     * Security Note: By explicitly defining these fields, we protect the 
     * application from Mass Assignment Vulnerabilities. This ensures that 
     * when a topic is created or updated, only these specific, intended 
     * attributes can be populated from user input or API requests.
     * 
     * Field Breakdown:
     * - `group_id`: Links this topic to its parent group/community.
     * - `title`: The main subject line displayed in topic listings.
     * - `description`: The initial content or detailed summary of the discussion.
     * - `creator_id`: Identifies who started this discussion (for ownership/authorization).
     * - `ml_category`: Stores the AI-generated category tag for automated classification.
     * 
     * @var array<int, string>
     */
    protected $fillable = [
        'group_id',
        'title',
        'description',  
        'creator_id',
        'ml_category',
    ];

    // =========================================================================
    //  RELATIONSHIPS
    //  Defines how this Topic connects to other entities in the database.
    // =========================================================================

    /**
     * Get the group that this topic belongs to.
     * 
     * This is an inverse One-to-Many (BelongsTo) relationship. It allows us to 
     * easily fetch the group's details when we have a topic (e.g., `$topic->group->name`) 
     * or to eager-load groups when displaying a list of topics across multiple groups.
     * 
     * Use Cases:
     * - Displaying breadcrumb navigation: "Group Name > Topic Title"
     * - Filtering topics by group membership
     * - Verifying that a user has access to the parent group before showing the topic
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Get the user who created this topic.
     * 
     * This links the `creator_id` foreign key back to the `users` table. It is 
     * essential for displaying the author's name and avatar in topic listings, 
     * verifying ownership for edit/delete permissions, and generating "Started by..." 
     * attribution in the UI.
     * 
     * Note: We explicitly specify 'creator_id' as the foreign key here because 
     * the column name doesn't follow Laravel's default convention (which would 
     * expect 'topic_id' based on the method name 'creator').
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Get all posts (replies) within this topic.
     * 
     * This is a One-to-Many relationship with the Post model. It provides access 
     * to the entire conversation thread, including the initial post and all replies.
     * 
     * Use Cases:
     * - Rendering the full discussion thread in the topic detail view
     * - Counting total replies: `$topic->posts()->count()`
     * - Eager-loading posts with their authors: `Topic::with('posts.author')->get()`
     * - Calculating engagement metrics (most active topics, reply velocity, etc.)
     * 
     * Architectural Note: The Post model includes privacy scopes and threaded 
     * reply support, so this relationship automatically respects those constraints 
     * when queries are executed with the appropriate scopes applied.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}