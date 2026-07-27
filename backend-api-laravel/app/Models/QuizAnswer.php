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
 * Quiz Answer Model
 * 
 * Represents a single, granular answer provided by a student to a specific 
 * question within a quiz submission.
 * 
 * Purpose in the Application:
 * This model serves as the detailed, question-by-question record of a student's 
 * performance. While the parent `QuizSubmission` model tracks the overall attempt 
 * (total score, duration, etc.), this model captures the individual data points 
 * that power:
 * 1. Detailed Result Views: Showing students exactly which questions they got 
 *    right or wrong, along with their selected answers.
 * 2. Analytics & Reporting: Allowing lecturers to identify which questions were 
 *    most frequently missed, indicating areas where students need more instruction.
 * 3. Grade Calculation: Storing the `points_earned` for each question, which 
 *    aggregates up to form the final submission score.
 * 4. Audit Trails: Providing a permanent record of what the student answered, 
 *    even if the question itself is later modified or deleted.
 * 
 * Architectural Note:
 * This model intentionally does not define explicit Eloquent relationships 
 * (like `belongsTo(QuizSubmission::class)`). This is a deliberate design choice 
 * to keep the model lightweight, as it is primarily used as a data storage 
 * record. The relationships are typically handled at the controller or service 
 * layer when the data needs to be joined and presented to the user.
 * 
 * @property int $id The unique identifier for this answer record.
 * @property int $submission_id The ID of the parent quiz submission this answer belongs to.
 * @property int $question_id The ID of the quiz question being answered.
 * @property mixed $answer The student's selected answer (could be a string, integer, or JSON array depending on question type).
 * @property bool $is_correct Whether the student's answer was marked as correct.
 * @property float $points_earned The number of points awarded for this specific answer.
 * @property \Illuminate\Support\Carbon $created_at Timestamp of when the answer was recorded.
 * @property \Illuminate\Support\Carbon $updated_at Timestamp of last modification.
 */
class QuizAnswer extends Model
{
    /**
     * The attributes that can be safely mass-assigned.
     * 
     * Security Note: By explicitly defining these fields, we protect the 
     * application from Mass Assignment Vulnerabilities. This ensures that 
     * when a submission is being graded and these records are created in bulk, 
     * only these specific, intended attributes can be populated.
     * 
     * Field Breakdown:
     * - `submission_id`: Links this answer to the overall quiz attempt.
     * - `question_id`: Identifies which question this answer corresponds to.
     * - `answer`: Stores the actual response (e.g., "A", "True", or a selected option ID).
     * - `is_correct`: A boolean flag set during the grading process.
     * - `points_earned`: The numeric value awarded (e.g., 1.0 for full credit, 0.5 for partial).
     * 
     * @var array<int, string>
     */
    protected $fillable = [
        'submission_id',
        'question_id',
        'answer',
        'is_correct',
        'points_earned',
    ];
}