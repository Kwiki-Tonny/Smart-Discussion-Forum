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
 * Quiz Submission Model
 * 
 * Represents a single attempt by a student to complete a quiz.
 * 
 * Purpose in the Application:
 * This model serves as the parent record for a quiz attempt, tracking the overall 
 * performance metrics (score, submission method) while maintaining a relationship 
 * to the detailed, question-by-question answers stored in the `QuizAnswer` model.
 * 
 * Key Architectural Features:
 * 1. Snapshot Storage: The `answers_payload` JSON field provides a redundant, 
 *    denormalized snapshot of all answers at the time of submission. This ensures 
 *    data integrity even if individual QuizAnswer records are corrupted or deleted.
 * 2. Timeout Handling: The `is_auto_submitted` flag distinguishes between manual 
 *    submissions (student clicked "Submit") and automatic submissions (timer expired), 
 *    enabling different analytics and audit trails.
 * 3. Offline-First Sync Support: Similar to the Post model, `created_at` is 
 *    mass-assignable to preserve the original client-side timestamp when syncing 
 *    quiz attempts from the desktop client.
 * 4. Performance Tracking: The `score` field provides immediate access to the 
 *    final grade without requiring aggregation queries across all QuizAnswer records.
 * 
 * @property int $id The unique identifier for this submission.
 * @property int $quiz_id The ID of the quiz being attempted.
 * @property int $user_id The ID of the student who submitted the quiz.
 * @property float $score The final score achieved (sum of all points_earned).
 * @property array $answers_payload JSON snapshot of all answers at submission time.
 * @property bool $is_auto_submitted Whether the quiz was auto-submitted due to timeout.
 * @property \Illuminate\Support\Carbon $created_at Timestamp of submission (preserved from client during sync).
 * @property \Illuminate\Support\Carbon $updated_at Timestamp of last modification.
 */
class QuizSubmission extends Model
{
    /**
     * The attributes that can be safely mass-assigned.
     * 
     * Security Note: By explicitly defining these fields, we protect the 
     * application from Mass Assignment Vulnerabilities. This ensures that 
     * when a quiz is submitted, only these specific, intended attributes 
     * can be populated from the request.
     * 
     * Special Note on `created_at`: 
     * Normally, Eloquent manages timestamps automatically. However, `created_at` is 
     * explicitly made fillable here to support the offline-first desktop client. 
     * When a student completes a quiz while offline, the client records the local 
     * completion time. Upon reconnection, the SyncWorker pushes this original 
     * timestamp to the server to maintain accurate chronological ordering.
     * 
     * Field Breakdown:
     * - `quiz_id`: Links this submission to the specific quiz being attempted.
     * - `user_id`: Identifies which student made this attempt.
     * - `score`: The final calculated score (aggregated from QuizAnswer records).
     * - `answers_payload`: JSON snapshot of all answers for data integrity/backup.
     * - `is_auto_submitted`: Boolean flag indicating timeout-based auto-submission.
     * - `created_at`: The exact moment the quiz was completed (client-preserved).
     * 
     * @var array<int, string>
     */
    protected $fillable = [
        'quiz_id',
        'user_id',
        'score',
        'answers_payload',
        'is_auto_submitted',
        'created_at',
    ];

    /**
     * The attributes that should be cast to native PHP types.
     * 
     * Architectural Note: 
     * - `answers_payload` is stored as JSON in the database but automatically 
     *   hydrated into a PHP array when accessed. This provides a complete snapshot 
     *   of the student's answers at the moment of submission, serving as both a 
     *   backup mechanism and a quick-reference for displaying results without 
     *   needing to query the QuizAnswer table.
     * - `is_auto_submitted` is cast to boolean, ensuring it returns `true`/`false` 
     *   instead of `1`/`0` from the database, making conditional logic more readable.
     * 
     * @var array<string, string>
     */
    protected $casts = [
        'answers_payload' => 'array',
        'is_auto_submitted' => 'boolean',
    ];

    // =========================================================================
    //  RELATIONSHIPS
    //  Defines how this Submission connects to other entities in the database.
    // =========================================================================

    /**
     * Get the quiz that this submission belongs to.
     * 
     * This is an inverse One-to-Many (BelongsTo) relationship. It allows us to 
     * easily fetch the quiz's details when we have a submission (e.g., 
     * `$submission->quiz->title`) or to eager-load quizzes when displaying a 
     * student's submission history.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * Get the student who made this submission.
     * 
     * This links the `user_id` foreign key back to the `users` table. It is 
     * essential for displaying the student's name in grade reports, generating 
     * personalized feedback, and preventing duplicate submissions (checking if 
     * a user has already attempted a quiz).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all individual answers submitted for this quiz attempt.
        *
     * This is a One-to-Many relationship with the QuizAnswer model. While the
     * `answers_payload` provides a snapshot, this relationship gives access to
     * the detailed, structured records of each question's answer, including
     * whether it was marked correct and how many points were earned.
     * 
     * Use Cases:
     * 1. Detailed Result Views: Showing students exactly which questions they 
     *    got right or wrong.
     * 2. Analytics: Calculating which questions were most frequently missed.
     * 3. Grade Verification: Auditing the score calculation by summing points_earned.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function answers()
    {
        return $this->hasMany(QuizAnswer::class, 'submission_id');
    }
}