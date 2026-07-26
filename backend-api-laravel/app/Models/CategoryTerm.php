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
 * Category Term Model
 * 
 * This model represents a specific keyword, phrase, or "term" that is 
 * associated with a particular discussion group. 
 * 
 * Purpose in the Application:
 * It is typically used to track trending topics, categorize content, or 
 * power auto-suggestion features within a specific group. By tracking the 
 * 'frequency', the application can identify which terms are most popular 
 * or relevant in a given group over time.
 * 
 * @property int $id The unique identifier for this term record.
 * @property string $term The actual keyword or phrase being tracked.
 * @property int $group_id The ID of the group this term belongs to.
 * @property string|null $category A broader classification or tag for this term (e.g., "Technology", "Announcement").
 * @property int $frequency How many times this term has been used, searched, or detected in the group.
 */
class CategoryTerm extends Model
{
    /**
     * The attributes that can be safely mass-assigned.
     * 
     * Security Note: Listing these fields here protects the application from 
     * "Mass Assignment Vulnerabilities". It ensures that when we create or 
     * update a CategoryTerm from user input or an automated script, only 
     * these specific, intended columns can be modified.
     */
    protected $fillable = [
        'term',        // The keyword or phrase itself (e.g., "homework", "exam")
        'group_id',    // Links this term to a specific discussion group
        'category',    // Optional broader classification for the term
        'frequency',   // A counter tracking how often this term appears or is used
    ];

    /**
     * Get the group that this category term belongs to.
     * 
     * This defines a "Belongs To" relationship with the Group model.
     * 
     * Why this is useful: It allows us to easily fetch the parent group's 
     * details when we have a term. For example, we can write clean code like 
     * `$term->group->name` to display which group a trending term belongs to, 
     * or use `CategoryTerm::with('group')->get()` to load terms and their 
     * groups efficiently in a single database query (eager loading).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}