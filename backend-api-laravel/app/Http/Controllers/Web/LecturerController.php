<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Topic;
use App\Models\Post;
use App\Models\User;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizSubmission;
use App\Exports\StudentPerformanceExport;
use App\Exports\QuizResultsExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LecturerController extends Controller
{
    /**
     * Lecturer Dashboard – Only own groups
     */
    public function index()
    {
        $user = Auth::user();

        // Only groups created by this lecturer
        $groups = Group::where('created_by', $user->id)
            ->withCount(['topics', 'users'])
            ->orderBy('name')
            ->get();

        $totalGroups = $groups->count(); 

        // Only students in lecturer's groups
        $studentIds = DB::table('group_user')
            ->whereIn('group_id', $groups->pluck('id'))
            ->pluck('user_id')
            ->unique();

        $totalStudents = User::whereIn('id', $studentIds)->where('role', 'student')->count();

        // Only topics in lecturer's groups
        $totalTopics = Topic::whereIn('group_id', $groups->pluck('id'))->count();

        // Only posts in lecturer's groups
        $totalPosts = Post::whereIn('topic_id', function($query) use ($groups) {
            $query->select('id')->from('topics')->whereIn('group_id', $groups->pluck('id'));
        })->count();

        // Active students (in lecturer's groups)
        $activeStudents = User::whereIn('id', $studentIds)
            ->where('role', 'student')
            ->where('last_communicated_at', '>=', now()->subDays(7))
            ->count();

        // Topics per group (only lecturer's groups)
        $topicsPerGroup = Group::where('created_by', $user->id)
            ->withCount('topics')
            ->orderBy('topics_count', 'desc')
            ->limit(5)
            ->get()
            ->pluck('topics_count', 'name')
            ->toArray();

        // Recent topics in lecturer's groups
        $recentTopics = Topic::whereIn('group_id', $groups->pluck('id'))
            ->with(['group', 'creator'])
            ->latest()
            ->limit(10)
            ->get();

        // Only quizzes created by this lecturer
        $totalQuizzes = Quiz::where('created_by', $user->id)->count();

        // Only submissions for quizzes created by this lecturer
        $totalSubmissions = QuizSubmission::whereIn('quiz_id', function($query) use ($user) {
            $query->select('id')->from('quizzes')->where('created_by', $user->id);
        })->count();

        // Average score for lecturer's quizzes
        $avgScore = QuizSubmission::whereIn('quiz_id', function($query) use ($user) {
            $query->select('id')->from('quizzes')->where('created_by', $user->id);
        })->avg('score') ?? 0;

        // Top students in lecturer's groups
        $topStudents = User::whereIn('id', $studentIds)
            ->where('role', 'student')
            ->withCount(['topics', 'posts'])
            ->orderBy('posts_count', 'desc')
            ->limit(10)
            ->get();

        return view('lecturer.dashboard', compact(
            'groups',
            'totalGroups', 
            'totalStudents',
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
     * My Groups – Only groups created by lecturer
     */
    public function groups()
    {
        $user = Auth::user();
        $groups = Group::where('created_by', $user->id)
            ->withCount(['topics', 'users'])
            ->orderBy('name')
            ->get();

        return view('lecturer.groups', compact('groups'));
    }

    /**
     * Show create group form
     */
    public function createGroup()
    {
        return view('lecturer.group-create');
    }

    /**
     * Store a new group
     */
    public function storeGroup(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $group = Group::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('lecturer.groups')
            ->with('success', "Group '{$group->name}' created successfully!");
    }

    /**
     * Group Analytics – Only own groups
     */
    public function groupAnalytics($groupId)
    {
        $user = Auth::user();

        $group = Group::withCount(['topics', 'users'])
            ->where('created_by', $user->id)
            ->findOrFail($groupId);

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
            ->whereHas('groups', function($query) use ($groupId) {
                $query->where('group_id', $groupId);
            })
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
     * Quiz Management – Only quizzes created by lecturer
     */
    public function quizzes()
    {
        $user = Auth::user();
        $quizzes = Quiz::where('created_by', $user->id)
            ->with(['group', 'submissions'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('lecturer.quizzes', compact('quizzes'));
    }

    /**
     * Create Quiz Form – Only own groups
     */
    public function createQuiz()
    {
        $user = Auth::user();
        $groups = Group::where('created_by', $user->id)->orderBy('name')->get();
        $allowedCategories = ['active', 'warned_once', 'warned_twice'];

        return view('lecturer.quiz-create', compact('groups', 'allowedCategories'));
    }

    /**
     * Store Quiz – Only own groups
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

        // Verify lecturer owns this group
        $group = Group::where('created_by', Auth::id())->findOrFail($validated['group_id']);

        $duration = (int) $validated['duration'];
        $startsAt = Carbon::parse($validated['starts_at']);
        $endsAt = $startsAt->copy()->addMinutes($duration);

        $quiz = Quiz::create([
            'title' => $validated['title'],
            'group_id' => $validated['group_id'],
            'created_by' => Auth::id(),
            'duration' => $duration,
            'allowed_categories' => $validated['allowed_categories'] ?? ['active'],
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'is_active' => true,
        ]);

        return redirect()->route('lecturer.quiz.edit', $quiz->id)
            ->with('success', "Quiz '{$quiz->title}' created! Add questions.");
    }

    /**
     * Edit Quiz – Only own quizzes
     */
    public function editQuiz($quizId)
    {
        $user = Auth::user();
        $quiz = Quiz::where('created_by', $user->id)
            ->with(['questions', 'group'])
            ->findOrFail($quizId);

        $groups = Group::where('created_by', $user->id)->orderBy('name')->get();

        return view('lecturer.quiz-edit', compact('quiz', 'groups'));
    }

    /**
     * Add a question to a quiz – Only own quizzes
     */
    public function storeQuestion(Request $request, $quizId)
    {
        $user = Auth::user();
        $quiz = Quiz::where('created_by', $user->id)->findOrFail($quizId);

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

        if ($validated['type'] === 'text') {
            $options = [];
        }

        QuizQuestion::create([
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
     * Remove a question – Only own quizzes
     */
    public function deleteQuestion($quizId, $questionId)
    {
        $user = Auth::user();
        $quiz = Quiz::where('created_by', $user->id)->findOrFail($quizId);

        $question = QuizQuestion::where('quiz_id', $quizId)->findOrFail($questionId);
        $question->delete();

        return redirect()->route('lecturer.quiz.edit', $quizId)
            ->with('success', 'Question removed.');
    }

    /**
     * Toggle quiz status – Only own quizzes
     */
    public function toggleQuizStatus($quizId)
    {
        $user = Auth::user();
        $quiz = Quiz::where('created_by', $user->id)->findOrFail($quizId);

        $quiz->is_active = !$quiz->is_active;
        $quiz->save();

        $status = $quiz->is_active ? 'activated' : 'deactivated';
        return redirect()->route('lecturer.quizzes')
            ->with('success', "Quiz {$status} successfully.");
    }

    /**
     * Store multiple questions at once (bulk) – Only own quizzes
     */
    public function storeBulkQuestions(Request $request, $quizId)
    {
        $user = Auth::user();
        $quiz = Quiz::where('created_by', $user->id)->findOrFail($quizId);

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

    /**
     * Quiz Results – Only own quizzes
     */
    public function quizResults($quizId)
    {
        $user = Auth::user();
        $quiz = Quiz::where('created_by', $user->id)
            ->with(['group', 'submissions.user'])
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
     * Grading Matrix – Only students in lecturer's groups
     */
    public function gradingMatrix()
    {
        $user = Auth::user();
        $groupIds = Group::where('created_by', $user->id)->pluck('id')->toArray();

        $studentIds = DB::table('group_user')
            ->whereIn('group_id', $groupIds)
            ->pluck('user_id')
            ->unique();

        $students = User::whereIn('id', $studentIds)
            ->where('role', 'student')
            ->withCount(['topics', 'posts'])
            ->get();

        $students->each(function ($student) {
            $student->participation_score = min(100,
                ($student->topics_count * 5) + ($student->posts_count * 2)
            );
        });

        return view('lecturer.grading', compact('students'));
    }

    /**
     * Export Students – Only in lecturer's groups
     */
    public function exportStudents()
    {
        $user = Auth::user();
        $groupIds = Group::where('created_by', $user->id)->pluck('id')->toArray();

        if (empty($groupIds)) {
            return redirect()->back()->with('error', 'No students to export.');
        }

        return Excel::download(new StudentPerformanceExport($groupIds), 'student_performance.xlsx');
    }

    /**
     * Export Quiz Results – Only own quizzes
     */
    public function exportQuizResults($quizId)
    {
        $user = Auth::user();
        $quiz = Quiz::where('created_by', $user->id)->findOrFail($quizId);

        return Excel::download(new QuizResultsExport($quizId), 'quiz_results_' . $quiz->title . '.xlsx');
    }

    /**
     * Lecturer Profile – Only own groups
     */
    public function profile()
    {
        $user = Auth::user();
        $groups = Group::where('created_by', $user->id)
            ->withCount(['topics', 'users'])
            ->get();

        $totalGroups = $groups->count();
        $totalTopics = Topic::whereIn('group_id', $groups->pluck('id'))->count();
        $totalPosts = Post::whereIn('topic_id', function($query) use ($groups) {
            $query->select('id')->from('topics')->whereIn('group_id', $groups->pluck('id'));
        })->count();

        $studentIds = DB::table('group_user')
            ->whereIn('group_id', $groups->pluck('id'))
            ->pluck('user_id')
            ->unique();

        $totalStudents = User::whereIn('id', $studentIds)->where('role', 'student')->count();

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