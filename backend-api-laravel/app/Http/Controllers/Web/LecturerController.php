<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Topic;
use App\Models\Post;
use App\Models\User;
use App\Models\Quiz;
use App\Models\QuizSubmission;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LecturerController extends Controller
{
    /**
     * Lecturer Dashboard - Overview
     */
    public function index()
    {
        // All groups
        $groups = Group::withCount(['topics', 'users'])
            ->orderBy('name')
            ->get();

        // Stats
        $totalStudents = User::where('role', 'student')->count();
        $totalGroups = Group::count();
        $totalTopics = Topic::count();
        $totalPosts = Post::count();
        $activeStudents = User::where('role', 'student')
            ->where('last_communicated_at', '>=', now()->subDays(7))
            ->count();

        // Topics per group (for chart)
        $topicsPerGroup = Group::withCount('topics')
            ->orderBy('topics_count', 'desc')
            ->limit(5)
            ->get()
            ->pluck('topics_count', 'name')
            ->toArray();

        // Recent topics
        $recentTopics = Topic::with(['group', 'creator'])
            ->latest()
            ->limit(10)
            ->get();

        // Quiz stats
        $totalQuizzes = Quiz::count();
        $totalSubmissions = QuizSubmission::count();
        $avgScore = QuizSubmission::avg('score') ?? 0;

        // Top students
        $topStudents = User::where('role', 'student')
            ->withCount(['topics', 'posts'])
            ->orderBy('posts_count', 'desc')
            ->limit(10)
            ->get();

        return view('lecturer.dashboard', compact(
            'groups',
            'totalStudents',
            'totalGroups',
            'totalTopics',
            'totalPosts',
            'activeStudents',
            'topicsPerGroup',
            'recentTopics',
            'totalQuizzes',
            'totalSubmissions',
            'avgScore',
            'topStudents'
        ));
    }

    /**
     * Group Analytics - Detailed stats per group
     */
    public function groupAnalytics($groupId)
    {
        $group = Group::withCount(['topics', 'users'])->findOrFail($groupId);

        // Daily activity (last 30 days)
        $dailyActivity = Post::whereHas('topic', function($query) use ($groupId) {
            $query->where('group_id', $groupId);
        })
        ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
        ->where('created_at', '>=', now()->subDays(30))
        ->groupBy('date')
        ->orderBy('date')
        ->get()
        ->pluck('count', 'date')
        ->toArray();

        // Top topics
        $topTopics = Topic::where('group_id', $groupId)
            ->withCount('posts')
            ->orderBy('posts_count', 'desc')
            ->limit(10)
            ->get();

        // Student participation
        $studentParticipation = User::where('role', 'student')
            ->withCount(['posts' => function($query) use ($groupId) {
                $query->whereHas('topic', function($q) use ($groupId) {
                    $q->where('group_id', $groupId);
                });
            }])
            ->having('posts_count', '>', 0)
            ->orderBy('posts_count', 'desc')
            ->limit(10)
            ->get();

        // Category distribution
        $categories = Topic::where('group_id', $groupId)
            ->whereNotNull('ml_category')
            ->select('ml_category', DB::raw('count(*) as count'))
            ->groupBy('ml_category')
            ->get()
            ->pluck('count', 'ml_category')
            ->toArray();

        return view('lecturer.group-analytics', compact(
            'group',
            'dailyActivity',
            'topTopics',
            'studentParticipation',
            'categories'
        ));
    }

    /**
     * Quiz Management - List all quizzes
     */
    public function quizzes()
    {
        $quizzes = Quiz::with(['group', 'submissions'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('lecturer.quizzes', compact('quizzes'));
    }

    /**
     * Create Quiz Form
     */
    public function createQuiz()
    {
        $groups = Group::orderBy('name')->get();
        $allowedCategories = ['active', 'warned_once', 'warned_twice'];
        return view('lecturer.quiz-create', compact('groups', 'allowedCategories'));
    }

     /**
     * Store Quiz
     */
    public function storeQuiz(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'group_id' => 'required|exists:groups,id',
            'duration' => 'required|integer|min:1|max:180',
            'allowed_categories' => 'nullable|array',
            'starts_at' => 'required|date|after:now',
        ]);

        // ✅ Cast duration to integer
        $duration = (int) $validated['duration'];
        $startsAt = \Carbon\Carbon::parse($validated['starts_at']);
        $endsAt = $startsAt->copy()->addMinutes($duration);

        $quiz = Quiz::create([
            'title' => $validated['title'],
            'group_id' => $validated['group_id'],
            'duration' => $duration,
            'allowed_categories' => $validated['allowed_categories'] ?? ['active'],
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ]);

        return redirect()->route('lecturer.quiz.edit', $quiz->id)
        ->with('success', "Quiz '{$quiz->title}' created! Now add questions.");
    }

    /**
     * Quiz Results
     */
    public function quizResults($quizId)
    {
        $quiz = Quiz::with(['group', 'submissions.user'])
            ->findOrFail($quizId);

        $submissions = $quiz->submissions;
        $averageScore = $submissions->avg('score') ?? 0;
        $passRate = $submissions->count() > 0
            ? ($submissions->where('score', '>=', 50)->count() / $submissions->count()) * 100
            : 0;

        return view('lecturer.quiz-results', compact(
            'quiz',
            'submissions',
            'averageScore',
            'passRate'
        ));
    }

    /**
     * Grading Matrix
     */
    public function gradingMatrix()
    {
        $students = User::where('role', 'student')
            ->withCount(['topics', 'posts'])
            ->get();

        // Calculate participation score
        $students->each(function ($student) {
            $student->participation_score = min(100,
                ($student->topics_count * 5) + ($student->posts_count * 2)
            );
        });

        return view('lecturer.grading', compact('students'));
    }

    /**
     * Show questions for a quiz
     */
    public function editQuiz($quizId)
    {
        $quiz = Quiz::with(['questions', 'group'])->findOrFail($quizId);
        $groups = Group::orderBy('name')->get();
        return view('lecturer.quiz-edit', compact('quiz', 'groups'));
    }

    /**
     * Add a question to a quiz
     */
    public function storeQuestion(Request $request, $quizId)
    {
        $validated = $request->validate([
            'question' => 'required|string',
            'type' => 'required|in:single,multiple,text',
            'options' => 'nullable|array',
            'options.*' => 'nullable|string',
            'correct_answers' => 'nullable|array',
            'points' => 'required|integer|min:1|max:100',
        ]);

        // Clean options (remove empty)
        $options = array_filter($validated['options'] ?? [], function($opt) {
            return !empty(trim($opt));
        });

        // For text type, no options needed
        if ($validated['type'] === 'text') {
            $options = [];
        }

        $question = QuizQuestion::create([
            'quiz_id' => $quizId,
            'question' => $validated['question'],
            'type' => $validated['type'],
            'options' => $options,
            'correct_answers' => $validated['correct_answers'] ?? [],
            'points' => $validated['points'],
            'order' => QuizQuestion::where('quiz_id', $quizId)->count() + 1,
        ]);

        return redirect()->route('lecturer.quiz.edit', $quizId)
            ->with('success', 'Question added successfully!');
    }

    /**
     * Remove a question
     */
    public function deleteQuestion($quizId, $questionId)
    {
        $question = QuizQuestion::where('quiz_id', $quizId)->findOrFail($questionId);
        $question->delete();

        return redirect()->route('lecturer.quiz.edit', $quizId)
            ->with('success', 'Question removed.');
    }

    /**
     * Toggle quiz status (active/inactive)
     */
    public function toggleQuizStatus($quizId)
    {
        $quiz = Quiz::findOrFail($quizId);
        $quiz->is_active = !$quiz->is_active;
        $quiz->save();

        $status = $quiz->is_active ? 'activated' : 'deactivated';
        return redirect()->route('lecturer.quizzes')
            ->with('success', "Quiz {$status} successfully.");
    }

    /**
     * Store multiple questions at once (bulk)
     */
    public function storeBulkQuestions(Request $request, $quizId)
    {
        $quiz = Quiz::findOrFail($quizId);

        $validated = $request->validate([
            'questions' => 'required|array',
            'questions.*.question' => 'required|string',
            'questions.*.type' => 'required|in:single,multiple,text',
            'questions.*.options' => 'nullable|string',
            'questions.*.correct_answers' => 'nullable|string',
            'questions.*.points' => 'required|integer|min:1|max:100',
        ]);

        $count = 0;

        foreach ($validated['questions'] as $index => $qData) {
            // Parse options (split by newline)
            $options = [];
            if (!empty($qData['options'])) {
                $options = array_filter(array_map('trim', explode("\n", $qData['options'])));
            }

            // Parse correct answers
            $correctAnswers = [];
            if (!empty($qData['correct_answers'])) {
                if ($qData['type'] === 'text') {
                    $correctAnswers = [trim($qData['correct_answers'])];
                } else {
                    // Split by comma and trim
                    $correctAnswers = array_filter(array_map('trim', explode(',', $qData['correct_answers'])));
                }
            }

            QuizQuestion::create([
                'quiz_id' => $quizId,
                'question' => $qData['question'],
                'type' => $qData['type'],
                'options' => $options,
                'correct_answers' => $correctAnswers,
                'points' => $qData['points'],
                'order' => $index + 1,
            ]);

            $count++;
        }

        return redirect()->route('lecturer.quiz.edit', $quizId)
            ->with('success', "{$count} question(s) added successfully!");
    }

    public function profile()
    {
        $user = Auth::user();

        // Get groups the lecturer manages (all groups they have access to)
        $groups = Group::withCount(['topics', 'users'])->get();

        // Stats
        $totalGroups = $groups->count();
        $totalTopics = Topic::count();
        $totalPosts = Post::count();
        $totalStudents = User::where('role', 'student')->count();

        return view('lecturer.profile', compact(
            'user',
            'groups',
            'totalGroups',
            'totalTopics',
            'totalPosts',
            'totalStudents'
        ));
    }
}