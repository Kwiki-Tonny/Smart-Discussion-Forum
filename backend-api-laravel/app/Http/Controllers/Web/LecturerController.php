<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Topic;
use App\Models\Post;
use App\Models\User;
use App\Models\Quiz;
use App\Models\QuizSubmission;
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

        $quiz = Quiz::create([
            'title' => $validated['title'],
            'group_id' => $validated['group_id'],
            'duration' => $validated['duration'],
            'allowed_categories' => $validated['allowed_categories'] ?? ['active'],
            'starts_at' => $validated['starts_at'],
            'ends_at' => now()->addMinutes($validated['duration']),
        ]);

        return redirect()->route('lecturer.quizzes')
            ->with('success', "Quiz '{$quiz->title}' created successfully!");
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
}