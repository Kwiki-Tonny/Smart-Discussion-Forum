<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Topic;
use App\Models\Quiz;
use App\Models\Post;
use App\Models\PostLike;
use App\Models\User;
use App\Models\QuizSubmission;
use App\Models\QuizAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    /**
     * Join a group.
     */
    public function joinGroup($groupId)
    {
        $user = Auth::user();
        $group = Group::findOrFail($groupId);

        // Check if already a member
        if ($user->groups()->where('group_id', $groupId)->exists()) {
            return response()->json([
                'message' => 'You are already a member of this group.'
            ], 409);
        }

        // Add user with has_agreed_rules = false
        $user->groups()->attach($groupId, ['has_agreed_rules' => false]);

        return response()->json([
            'message' => 'Joined group successfully. Please accept the rules.'
        ], 200);
    }

    /**
     * Leave a group.
     */
    public function leaveGroup($groupId)
    {
        $user = Auth::user();
        $group = Group::findOrFail($groupId);

        if (!$user->groups()->where('group_id', $groupId)->exists()) {
            return response()->json([
                'message' => 'You are not a member of this group.'
            ], 404);
        }

        $user->groups()->detach($groupId);

        return response()->json([
            'message' => 'Left group successfully.'
        ], 200);
    }

    /**
     * Get all quizzes available for the student (based on group membership).
     * Adds a computed 'status' field: 'upcoming', 'started', 'ended'.
     */
    public function quizIndex()
        {
            $user = Auth::user();
            $groupIds = $user->groups()->pluck('groups.id')->toArray();

            $quizzes = Quiz::whereIn('group_id', $groupIds)
                ->where('is_active', true)
                ->with(['group'])
                ->orderBy('starts_at', 'asc')
                ->get()
                ->map(function ($quiz) {
                    $now = now();
                    $status = 'ended';
                    $durationMinutes = 0;

                    // ─── NULL CHECKS FOR STARTS_AT / ENDS_AT ──────────────
                    if ($quiz->starts_at === null || $quiz->ends_at === null) {
                        // If either date is missing, treat as 'ended' and skip duration
                        $status = 'ended';
                        $durationMinutes = 0;
                    } else {
                        if ($quiz->starts_at > $now) {
                            $status = 'upcoming';
                        } elseif ($quiz->ends_at >= $now) {
                            $status = 'started';
                        }
                        // Only calculate if both dates are present
                        $durationMinutes = (int) ceil($quiz->starts_at->diffInMinutes($quiz->ends_at));
                    }

                    // Check if user has already taken this quiz
                    $hasTaken = QuizSubmission::where('quiz_id', $quiz->id)
                        ->where('user_id', Auth::id())
                        ->whereNotNull('score')
                        ->exists();

                    return [
                        'id' => $quiz->id,
                        'title' => $quiz->title,
                        'status' => $status,
                        'total_questions' => $quiz->questions()->count(),
                        'duration_minutes' => $durationMinutes,
                        'has_taken' => $hasTaken,
                    ];
                });

            return response()->json(['data' => $quizzes]);
        }

    /**
     * Start a quiz: returns questions, started_at, duration_seconds.
     * Also creates a submission record with null score to track the attempt.
     */
    public function takeQuiz($quizId)
    {
        $user = Auth::user();
        $quiz = Quiz::with(['questions' => function ($q) {
            $q->select('id', 'question', 'type', 'options', 'points');
        }])->findOrFail($quizId);

        // Validate availability
        if ($quiz->starts_at > now()) {
            return response()->json([
                'message' => 'This quiz has not started yet.'
            ], 403);
        }
        if ($quiz->ends_at < now()) {
            return response()->json([
                'message' => 'This quiz has already ended.'
            ], 410);
        }

        // Check for existing submission
        $existing = QuizSubmission::where('quiz_id', $quizId)
            ->where('user_id', $user->id)
            ->first();

        if ($existing && $existing->score !== null) {
            return response()->json([
                'message' => 'You have already completed this quiz.'
            ], 409);
        }

        // If no active submission, create one with null score
        if (!$existing) {
            $submission = QuizSubmission::create([
                'quiz_id' => $quizId,
                'user_id' => $user->id,
                'score' => null,
                'answers_payload' => null,
                'is_auto_submitted' => false,
            ]);
        } else {
            $submission = $existing;
        }

        // Prepare questions with options (ensure options is array)
        $questions = $quiz->questions->map(function ($q) {
            return [
                'id' => $q->id,
                'text' => $q->question,
                'type' => $q->type ?? 'single', // default to single
                'options' => is_array($q->options) ? $q->options : json_decode($q->options, true) ?? [],
            ];
        });

        // Compute duration and started_at
        $startedAt = $submission->created_at; // when the submission was created (or use now)
        $expiresAt = $quiz->ends_at;
        $durationSeconds = $expiresAt->diffInSeconds($startedAt); // total seconds from start to expiry

        return response()->json([
            'data' => [
                'id' => $submission->id,
                'started_at' => $startedAt->toISOString(),
                'duration_seconds' => (int) $durationSeconds,
                'quiz' => [
                    'id' => $quiz->id,
                    'title' => $quiz->title,
                    'questions' => $questions,
                ]
            ]
        ]);
    }


    /**
     * Get all users (except the current user) for private post selection.
     */
    public function getUsers(Request $request)
    {
        $currentUser = $request->user();
        $users = User::select('id', 'name', 'email', 'role')
            ->where('id', '!=', $currentUser->id)
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $users
        ]);
    }

    /**
     * Submit quiz answers.
     * Expects { "answers": { question_id: answer, ... } } where answer can be integer, array, or string.
     * Returns { correct, incorrect, unanswered, percentage, total_questions, quiz_title }.
     */
    public function submitQuiz(Request $request, $quizId)
    {
        $user = Auth::user();
        $quiz = Quiz::with(['questions'])->findOrFail($quizId);
        $submission = QuizSubmission::where('quiz_id', $quizId)
            ->where('user_id', $user->id)
            ->whereNull('score')
            ->first();

        if (!$submission) {
            return response()->json([
                'message' => 'No active attempt found or quiz already submitted.'
            ], 404);
        }

        // Check if quiz is still active
        if ($quiz->ends_at < now()) {
            // Quiz ended, but we can still score it (auto-submit)
            // We'll allow scoring but return a 410 status.
        }

        $answers = $request->input('answers', []);

        // Auto-grade
        $totalQuestions = $quiz->questions->count();
        $correct = 0;
        $incorrect = 0;
        $unanswered = 0;

        // We'll store the answers in the submission payload
        $answersPayload = [];

        foreach ($quiz->questions as $question) {
            $questionId = $question->id;
            $userAnswer = $answers[$questionId] ?? null;
            $answersPayload[$questionId] = $userAnswer;

            // Determine correctness based on type
            $isCorrect = false;
            $correctAnswers = $question->correct_answers ?? [];

            if ($question->type === 'text') {
                // For text, we check if the answer matches one of the correct strings (case-insensitive)
                if (is_string($userAnswer)) {
                    $normalized = strtolower(trim($userAnswer));
                    $correctList = array_map('strtolower', array_map('trim', (array) $correctAnswers));
                    $isCorrect = in_array($normalized, $correctList);
                }
            } elseif ($question->type === 'single') {
                // Single choice: answer should be an integer (index)
                if (is_numeric($userAnswer)) {
                    $isCorrect = in_array((string) $userAnswer, array_map('strval', (array) $correctAnswers));
                }
            } elseif ($question->type === 'multiple') {
                // Multiple choice: answer should be an array of indices
                if (is_array($userAnswer)) {
                    $userSet = array_map('strval', $userAnswer);
                    $correctSet = array_map('strval', (array) $correctAnswers);
                    sort($userSet);
                    sort($correctSet);
                    $isCorrect = $userSet === $correctSet;
                }
            }

            if ($userAnswer === null || $userAnswer === '' || (is_array($userAnswer) && empty($userAnswer))) {
                $unanswered++;
            } elseif ($isCorrect) {
                $correct++;
            } else {
                $incorrect++;
            }
        }

        $percentage = $totalQuestions > 0 ? round(($correct / $totalQuestions) * 100, 2) : 0;

        // Update submission
        $submission->score = $percentage;
        $submission->answers_payload = json_encode($answersPayload);
        $submission->is_auto_submitted = $request->input('auto_submitted', false);
        $submission->save();

        // Return detail
        return response()->json([
            'data' => [
                'correct' => $correct,
                'incorrect' => $incorrect,
                'unanswered' => $unanswered,
                'percentage' => $percentage,
                'total_questions' => $totalQuestions,
                'quiz_title' => $quiz->title,
            ]
        ]);
    }

    /**
     * Get profile statistics: total posts, replies, topics, quizzes taken.
     */
    public function profileStats()
    {
        $user = Auth::user();

        $totalPosts = Post::where('user_id', $user->id)->count();
        $totalReplies = Post::where('user_id', $user->id)->whereNotNull('parent_id')->count();
        $totalTopics = Topic::where('creator_id', $user->id)->count();
        $totalQuizzes = QuizSubmission::where('user_id', $user->id)->whereNotNull('score')->count();

        return response()->json([
            'data' => [
                'total_posts' => $totalPosts,
                'total_replies' => $totalReplies,
                'total_topics' => $totalTopics,
                'total_quizzes' => $totalQuizzes,
            ]
        ]);
    }

    /**
     * Get list of past quiz attempts (completed).
     */
    public function attemptsList()
    {
        $user = Auth::user();

        $attempts = QuizSubmission::where('user_id', $user->id)
            ->whereNotNull('score')
            ->with(['quiz'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($sub) {
                return [
                    'id' => $sub->id,
                    'quiz_title' => $sub->quiz->title ?? 'Unknown Quiz',
                    'score' => (int) $sub->score,
                    'total_questions' => $sub->quiz->questions()->count(),
                    'date' => $sub->created_at->toDateTimeString(),
                ];
            });

        return response()->json(['data' => $attempts]);
    }

    /**
     * Get detailed breakdown of a specific attempt.
     */
    public function attemptDetail($attemptId)
    {
        $user = Auth::user();
        $submission = QuizSubmission::with(['quiz.questions'])
            ->where('id', $attemptId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Decode answers payload
        $answersPayload = json_decode($submission->answers_payload, true) ?? [];

        $correct = 0;
        $incorrect = 0;
        $unanswered = 0;

        foreach ($submission->quiz->questions as $question) {
            $userAnswer = $answersPayload[$question->id] ?? null;
            if ($userAnswer === null || $userAnswer === '' || (is_array($userAnswer) && empty($userAnswer))) {
                $unanswered++;
            } else {
                // We need to know if it's correct; we can recompute or store separately.
                // Simpler: we could store is_correct per question. Since we don't have that,
                // we recalc using the same logic as in submitQuiz.
                $correctAnswers = $question->correct_answers ?? [];
                $isCorrect = false;
                if ($question->type === 'text') {
                    if (is_string($userAnswer)) {
                        $normalized = strtolower(trim($userAnswer));
                        $correctList = array_map('strtolower', array_map('trim', (array) $correctAnswers));
                        $isCorrect = in_array($normalized, $correctList);
                    }
                } elseif ($question->type === 'single') {
                    if (is_numeric($userAnswer)) {
                        $isCorrect = in_array((string) $userAnswer, array_map('strval', (array) $correctAnswers));
                    }
                } elseif ($question->type === 'multiple') {
                    if (is_array($userAnswer)) {
                        $userSet = array_map('strval', $userAnswer);
                        $correctSet = array_map('strval', (array) $correctAnswers);
                        sort($userSet);
                        sort($correctSet);
                        $isCorrect = $userSet === $correctSet;
                    }
                }
                if ($isCorrect) {
                    $correct++;
                } else {
                    $incorrect++;
                }
            }
        }

        $totalQuestions = $submission->quiz->questions->count();
        $percentage = $totalQuestions > 0 ? round(($correct / $totalQuestions) * 100, 2) : 0;

        return response()->json([
            'data' => [
                'correct' => $correct,
                'incorrect' => $incorrect,
                'unanswered' => $unanswered,
                'percentage' => $percentage,
                'total_questions' => $totalQuestions,
                'quiz_title' => $submission->quiz->title ?? 'Unknown Quiz',
            ]
        ]);
    }

    /**
     * Accept group rules (needed for desktop client if they join via API).
     * This is used when the user clicks "Accept" on the rules modal.
     */
    public function acceptRules($groupId)
    {
        $user = Auth::user();
        $group = Group::findOrFail($groupId);

        if (!$user->groups()->where('group_id', $groupId)->exists()) {
            return response()->json([
                'message' => 'You are not a member of this group.'
            ], 403);
        }

        $user->groups()->updateExistingPivot($groupId, [
            'has_agreed_rules' => true
        ]);

        return response()->json([
            'message' => 'Rules accepted successfully.'
        ], 200);
    }

    public function toggleLike($postId)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $post = Post::find($postId);
        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        // Toggle like
        $existing = PostLike::where('post_id', $postId)
                            ->where('user_id', $user->id)
                            ->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            PostLike::create([
                'post_id' => $postId,
                'user_id' => $user->id,
            ]);
            $liked = true;
        }

        // Reload the post with author data and compute like fields
        $post->load('author:id,name,email,role');
        $post->is_liked = $liked;
        $post->likes_count = PostLike::where('post_id', $postId)->count();

        return response()->json(['data' => $post]);
    }
}