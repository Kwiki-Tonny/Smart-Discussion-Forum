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
 * Quiz Question Model
 * 
 * Represents an individual question within a quiz, including its content, 
 * type, available options, correct answers, and point value.
 * 
 * Purpose in the Application:
 * This model provides a flexible, polymorphic question structure that supports 
 * multiple question types (multiple choice, true/false, short answer, etc.) 
 * through the use of JSON arrays for `options` and `correct_answers`. This design 
 * allows lecturers to create diverse assessments without requiring separate 
 * database tables for each question type.
 * 
 * Key Architectural Features:
 * 1. Flexible Question Types: The `type` field determines how the question is 
 *    rendered in the UI and how answers are validated during grading.
 * 2. JSON-Based Options: The `options` array stores all possible answer choices 
 *    (e.g., ["A", "B", "C", "D"]), allowing for dynamic rendering without 
 *    additional database queries.
 * 3. Multi-Answer Support: The `correct_answers` array can store one or multiple 
 *    correct answers, enabling both single-choice and multiple-choice questions.
 * 4. Ordered Presentation: The `order` field ensures questions are displayed in 
 *    the sequence intended by the quiz creator, maintaining a consistent user experience.
 * 5. Grading Integration: The `points` field defines the weight of each question, 
 *    which is used to calculate the final submission score.
 * 
 * @property int $id The unique identifier for this question.
 * @property int $quiz_id The ID of the parent quiz this question belongs to.
 * @property string $question The text content of the question.
 * @property string $type The question type (e.g., "multiple_choice", "true_false", "short_answer").
 * @property array $options JSON array of available answer choices.
 * @property array $correct_answers JSON array of correct answer(s) for grading.
 * @property float $points The point value awarded for a correct answer.
 * @property int $order The display order of this question within the quiz.
 * @property \Illuminate\Support\Carbon $created_at Timestamp of question creation.
 * @property \Illuminate\Support\Carbon $updated_at Timestamp of last modification.
 */
class QuizQuestion extends Model
{
    /**
     * The attributes that can be safely mass-assigned.
     * 
     * Security Note: By explicitly defining these fields, we protect the 
     * application from Mass Assignment Vulnerabilities. This ensures that 
     * when a quiz is being created or updated, only these specific, intended 
     * attributes can be populated from user input.
     * 
     * Field Breakdown:
     * - `quiz_id`: Links this question to its parent quiz.
     * - `question`: The actual text/content of the question.
     * - `type`: Determines how the question is rendered and validated (e.g., "multiple_choice").
     * - `options`: JSON array of answer choices (e.g., ["Paris", "London", "Berlin"]).
     * - `correct_answers`: JSON array of correct answer(s) (e.g., ["Paris"] or ["A", "C"]).
     * - `points`: The numeric value awarded for a correct answer (supports partial credit).
     * - `order`: Integer controlling the display sequence of questions.
     * 
     * @var array<int, string>
     */
    protected $fillable = [
        'quiz_id',
        'question',
        'type',
        'options',
        'correct_answers',
        'points',
        'order',
    ];

    /**
     * The attributes that should be cast to native PHP types.
     * 
     * Architectural Note: 
     * - `options` and `correct_answers` are stored as JSON in the database but 
     *   automatically hydrated into PHP arrays when accessed. This allows for 
     *   easy iteration and manipulation in the application logic without requiring 
     *   manual json_decode() calls.
     * - When these arrays are serialized back to JSON for API responses, they 
     *   maintain their structure, making them immediately usable by the frontend.
     * 
     * @var array<string, string>
     */
    protected $casts = [
        'options' => 'array',
        'correct_answers' => 'array',
    ];

    // =========================================================================
    //  RELATIONSHIPS
    //  Defines how this Question connects to other entities in the database.
    // =========================================================================

    /**
     * Get the quiz that this question belongs to.
     * 
     * This is an inverse One-to-Many (BelongsTo) relationship. It allows us to 
     * easily fetch the parent quiz's details when we have a question (e.g., 
     * `$question->quiz->title`) or to eager-load quizzes when displaying a list 
     * of questions across multiple quizzes.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * Get all student answers submitted for this question.
     * 
     * This is a One-to-Many relationship with the QuizAnswer model. It tracks 
     * every time a student has answered this specific question across all quiz 
     * submissions. This is essential for:
     * 1. Analytics: Calculating which questions are most frequently missed.
     * 2. Auditing: Reviewing specific student responses for academic integrity.
     * 3. Grading: Retrieving the answers to compare against `correct_answers`.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function answers()
    {
        return $this->hasMany(QuizAnswer::class);
    }
}