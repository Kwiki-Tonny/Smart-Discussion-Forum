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
 * Quiz Model
 * 
 * Represents an assessment or examination within a specific discussion group.
 * 
 * Key Architectural Features:
 * 1. Time-Based State Management: Implements a robust temporal state machine 
 *    that determines whether a quiz is "Upcoming", "Active", or "Ended" based 
 *    on the current time relative to `starts_at` and `ends_at` timestamps.
 * 2. Flexible Question Selection: Supports `allowed_categories` as a JSON array, 
 *    allowing lecturers to dynamically generate quizzes from specific topic categories 
 *    rather than hard-coding individual questions.
 * 3. UI-Ready Serialization: Provides helper methods (`getStatusLabel()`, `getStatusColor()`) 
 *    that return presentation-ready data for the frontend, keeping styling logic 
 *    consistent across the application.
 * 4. Ownership & Authorization: Tracks the creator of the quiz, enabling straightforward 
 *    authorization checks for administrative actions (editing, deleting, viewing results).
 * 
 * @property int $id The unique identifier for this quiz.
 * @property string $title The display name of the quiz.
 * @property int $group_id The ID of the group this quiz belongs to.
 * @property int $created_by The ID of the user (typically a lecturer) who created the quiz.
 * @property int $duration The time limit for the quiz in minutes.
 * @property array $allowed_categories JSON array of category names/topics to draw questions from.
 * @property \Illuminate\Support\Carbon $starts_at The timestamp when the quiz becomes available.
 * @property \Illuminate\Support\Carbon $ends_at The timestamp when the quiz closes.
 * @property bool $is_active Whether the quiz is manually enabled/disabled by the creator.
 * @property \Illuminate\Support\Carbon $created_at Timestamp of quiz creation.
 * @property \Illuminate\Support\Carbon $updated_at Timestamp of last modification.
 */
class Quiz extends Model
{
    /**
     * The attributes that can be safely mass-assigned.
     * 
     * Security Note: By explicitly defining these fields, we protect the 
     * application from Mass Assignment Vulnerabilities. This ensures that 
     * malicious users cannot forge requests to modify sensitive fields 
     * (like `id` or `created_at`) when creating or updating a quiz.
     * 
     * @var array<int, string>
     */
    protected $fillable = [
        'title',              // The display name of the quiz
        'group_id',           // Links this quiz to a specific discussion group
        'created_by',         // The lecturer/admin who created this quiz
        'duration',           // Time limit in minutes for completing the quiz
        'allowed_categories', // JSON array of question categories to include
        'starts_at',          // When the quiz becomes available to students
        'ends_at',            // When the quiz closes and is no longer accessible
        'is_active',          // Manual on/off switch for the quiz
    ];

    /**
     * The attributes that should be cast to native PHP types.
     * 
     * Architectural Note: 
     * - `allowed_categories` is stored as JSON in the database but automatically 
     *   hydrated into a PHP array when accessed, making it easy to iterate over.
     * - `starts_at` and `ends_at` are cast to Carbon instances, enabling powerful 
     *   date manipulation and comparison methods (e.g., `$quiz->starts_at->isPast()`).
     * - `is_active` is cast to boolean, ensuring it returns `true`/`false` instead 
     *   of `1`/`0` from the database.
     * 
     * @var array<string, string>
     */
    protected $casts = [
        'allowed_categories' => 'array',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // =========================================================================
    //  RELATIONSHIPS
    //  Defines how this Quiz connects to other entities in the database.
    // =========================================================================

    /**
     * Get the group that this quiz belongs to.
     * 
     * This is an inverse One-to-Many (BelongsTo) relationship. It allows us to 
     * easily fetch the group's details when we have a quiz (e.g., `$quiz->group->name`) 
     * or to eager-load groups when displaying a list of quizzes.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Get the user (lecturer) who created this quiz.
     * 
     * This links the `created_by` foreign key back to the `users` table. It is 
     * useful for displaying the creator's name in quiz listings or for verifying 
     * administrative privileges (e.g., "Can this user edit this quiz?").
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all questions associated with this quiz.
     * 
     * This is a One-to-Many relationship with the QuizQuestion model. 
     * 
     * Architectural Note: Questions are automatically ordered by the `order` column, 
     * ensuring they are displayed in the sequence intended by the quiz creator. 
     * This is critical for maintaining a consistent user experience during the exam.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function questions()
    {
        return $this->hasMany(QuizQuestion::class)->orderBy('order');
    }

    /**
     * Get all submissions (attempts) for this quiz.
     * 
     * This relationship tracks every time a student has attempted this quiz. 
     * It is essential for generating analytics, calculating average scores, 
     * and preventing duplicate submissions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function submissions()
    {
        return $this->hasMany(QuizSubmission::class);
    }

    // =========================================================================
    //  AUTHORIZATION HELPERS
    //  Utility methods for permission checks and access control.
    // =========================================================================

    /**
     * Check if a specific user is the creator of this quiz.
     * 
     * This is a simple, efficient authorization helper used in controllers or 
     * policies to determine if a user has the right to modify, delete, or 
     * view detailed analytics for this quiz.
     *
     * @param int $userId The ID of the user to check.
     * @return bool True if the user created the quiz, false otherwise.
     */
    public function isCreatedBy($userId)
    {
        return $this->created_by == $userId;
    }

    // =========================================================================
    //  TIME-BASED STATE MANAGEMENT
    //  Methods that determine the quiz's temporal status and availability.
    // =========================================================================

    /**
     * Check if the quiz is currently active and accepting submissions.
     * 
     * A quiz is considered active if ALL of the following conditions are met:
     * 1. `is_active` is true (manually enabled by the creator)
     * 2. The current time is after or equal to `starts_at` (the quiz has started)
     * 3. The current time is before or equal to `ends_at` (the quiz has not ended)
     * 
     * This method is critical for controlling access to the quiz-taking interface.
     *
     * @return bool True if the quiz is currently active, false otherwise.
     */
    public function isActive()
    {
        return $this->is_active &&
               $this->starts_at <= now() &&
               $this->ends_at >= now();
    }

    /**
     * Check if the quiz has ended and is no longer accepting submissions.
     * 
     * A quiz is considered ended if EITHER of the following is true:
     * 1. The `ends_at` timestamp is in the past (time has expired)
     * 2. The `is_active` flag is false (manually disabled by the creator)
     * 
     * This allows lecturers to emergency-close a quiz even before the scheduled end time.
     *
     * @return bool True if the quiz has ended, false otherwise.
     */
    public function hasEnded()
    {
        return $this->ends_at < now() || !$this->is_active;
    }

    /**
     * Check if the quiz has started and is available to students.
     * 
     * A quiz has started if the current time is after or equal to `starts_at`.
     * This is useful for displaying "Quiz opens in X minutes" countdown timers 
     * before the quiz becomes accessible.
     *
     * @return bool True if the quiz has started, false otherwise.
     */
    public function hasStarted()
    {
        return $this->starts_at <= now();
    }

    /**
     * Get the remaining time in seconds until the quiz ends.
     * 
     * This method is typically used by the frontend to display a countdown timer 
     * during an active quiz attempt. It returns 0 if the quiz is not currently 
     * active (either hasn't started or has already ended).
     * 
     * Note: The `false` parameter in `diffInSeconds()` ensures the result is 
     * always positive (absolute value), preventing negative countdowns.
     *
     * @return int The number of seconds remaining, or 0 if not active.
     */
    public function getRemainingSeconds()
    {
        if (!$this->isActive()) {
            return 0;
        }
        return now()->diffInSeconds($this->ends_at, false);
    }

    // =========================================================================
    //  UI PRESENTATION HELPERS
    //  Methods that provide presentation-ready data for the frontend.
    // =========================================================================

    /**
     * Get a human-readable status label for the quiz.
     * 
     * Returns one of three states:
     * - "Ended": The quiz is no longer accepting submissions
     * - "Upcoming": The quiz has not started yet
     * - "Active": The quiz is currently open for submissions
     * 
     * This is used by the frontend to display status badges next to quiz titles.
     *
     * @return string The status label ("Ended", "Upcoming", or "Active").
     */
    public function getStatusLabel()
    {
        if ($this->hasEnded()) {
            return 'Ended';
        } elseif (!$this->hasStarted()) {
            return 'Upcoming';
        } else {
            return 'Active';
        }
    }

    /**
     * Get the CSS color classes for the quiz status badge.
     * 
     * Returns Tailwind CSS classes that match the quiz's current state:
     * - Gray for "Ended" (neutral, inactive)
     * - Green for "Upcoming" (positive, future event)
     * - Orange for "Active" (attention-grabbing, current action)
     * 
     * This centralizes the styling logic, ensuring consistent colors across 
     * all views where quiz status is displayed.
     *
     * @return string The Tailwind CSS classes for text and border colors.
     */
    public function getStatusColor()
    {
        if ($this->hasEnded()) {
            return 'text-[#666666] border-[#E5E5E5]';
        } elseif (!$this->hasStarted()) {
            return 'text-[#16A34A] border-[#16A34A]';
        } else {
            return 'text-[#D97706] border-[#D97706]';
        }
    }
}