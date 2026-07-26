<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Group;
use App\Models\Topic;
use App\Models\Post;
use App\Models\Quiz;
use App\Models\Setting;
use App\Models\BlacklistLog;
use App\Models\QuizSubmission;
use App\Notifications\UserApproved;
use App\Notifications\AccountBlacklisted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Admin Dashboard - Overview
     */
    public function index()
    {
        // Stats
        $totalUsers = User::count();
        $totalStudents = User::where('role', 'student')->count();
        $totalLecturers = User::where('role', 'lecturer')->count();
        $totalAdmins = User::where('role', 'admin')->count();
        $totalGroups = Group::count();
        $totalTopics = Topic::count();
        $totalPosts = Post::count();
        $totalQuizzes = Quiz::count();
        $totalSubmissions = QuizSubmission::count();
        $groups = Group::withCount(['topics', 'users'])->get(); 

        // Pending registrations (users with status = 'pending')
        $pendingRegistrations = User::where('role', 'student')
            ->where('status', 'pending')
            ->count();    
        // Blacklisted users
        $blacklistedUsers = User::where('status', 'blacklisted')->count();

        // Recent activity
        $recentUsers = User::latest()->limit(10)->get();
        $recentTopics = Topic::with(['group', 'creator'])->latest()->limit(10)->get();
        $recentBlacklistLogs = BlacklistLog::with('user')->latest()->limit(10)->get();

        // User growth (last 7 days)
        $userGrowth = User::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalStudents',
            'totalLecturers',
            'totalAdmins',
            'totalGroups',
            'totalTopics',
            'totalPosts',
            'totalQuizzes',
            'totalSubmissions',
            'pendingRegistrations',
            'blacklistedUsers',
            'recentUsers',
            'recentTopics',
            'recentBlacklistLogs',
            'userGrowth',
            'groups'
        ));
    }

    /**
     * User Management - List all users
     */
    public function users(Request $request)
    {
        $query = User::query();

        // Search
        if ($request->has('search') && !empty($request->search)) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by role
        if ($request->has('role') && !empty($request->role)) {
            $query->where('role', $request->role);
        }

        // Filter by status
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get stats for sidebar
        $stats = [
            'total' => User::count(),
            'students' => User::where('role', 'student')->count(),
            'lecturers' => User::where('role', 'lecturer')->count(),
            'admins' => User::where('role', 'admin')->count(),
            'active' => User::where('status', 'active')->count(),
            'warned' => User::where('status', 'warned_once')->orWhere('status', 'warned_twice')->count(),
            'blacklisted' => User::where('status', 'blacklisted')->count(),
        ];

        return view('admin.users', compact('users', 'stats'));
    }

    /**
     * Show user edit form
     */
    public function editUser($id)
    {
        $user = User::with(['groups', 'blacklistLogs'])->findOrFail($id);
        $groups = Group::all();

        return view('admin.user-edit', compact('user', 'groups'));
    }

    /**
     * Update user
     */
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'role' => 'required|in:admin,lecturer,student',
            'status' => 'required|in:active,warned_once,warned_twice,blacklisted',
            'password' => 'nullable|min:8|confirmed',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        $user->status = $validated['status'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        // If blacklisted, set expiry
        if ($validated['status'] === 'blacklisted') {
            $user->blacklist_expires_at = now()->addDays(14);
            BlacklistLog::create([
                'user_id' => $user->id,
                'reason' => 'Manually blacklisted by admin',
                'action_type' => 'hard_blacklist',
                'expires_at' => $user->blacklist_expires_at,
            ]);
        }

        $user->save();

        return redirect()->route('admin.users')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Delete user
     */
    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('admin.users')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Registration Queue - Pending registrations
     */
    public function registrations()
    {
        // Pending = status = 'pending' AND role = 'student'
        $pendingUsers = User::where('role', 'student')
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();

        // Approved = status = 'active' AND role = 'student'
        $approvedUsers = User::where('role', 'student')
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view('admin.registrations', compact('pendingUsers', 'approvedUsers'));
    }

    /**
     * Approve user registration
     */
    public function approveRegistration($id)
    {
        $user = User::findOrFail($id);
        if ($user->status !== 'pending') {
        return redirect()->route('admin.registrations')
            ->with('info', 'User is not pending approval.');
        }

        // Set status to active
        $user->status = 'active';
        $user->save();

        // Get stored password from session
        $password = session()->pull('pending_password_' . $user->id);

        // Notify user of approval
        $user->notify(new UserApproved($password));

        return redirect()->route('admin.registrations')
            ->with('success', 'User approved successfully. They can now log in.');
    }

    /**
     * Reject user registration
     */
    public function rejectRegistration($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('admin.registrations')
            ->with('success', 'User rejected and removed.');
    }

    /**
     * Blacklist Management
     */
    public function blacklist()
    {
        $blacklisted = User::where('status', 'blacklisted')
            ->with('blacklistLogs')
            ->get();

        $logs = BlacklistLog::with('user')
            ->latest()
            ->limit(50)
            ->get();

        return view('admin.blacklist', compact('blacklisted', 'logs'));
    }

    /**
     * Manually blacklist a user
     */
    public function manualBlacklist(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'reason' => 'required|string|max:255',
            'duration' => 'required|integer|min:1|max:365',
        ]);

        $user = User::findOrFail($validated['user_id']);

        // ✅ Force cast to integer
        $duration = (int) $validated['duration'];
        $user->blacklist_expires_at = now()->addDays($duration);

        $user->status = 'blacklisted';
        $user->save();

        // Revoke all tokens
        $user->tokens()->delete();

        BlacklistLog::create([
            'user_id' => $user->id,
            'reason' => $validated['reason'],
            'action_type' => 'manual_blacklist',
            'expires_at' => $user->blacklist_expires_at,
        ]);

        //send email
        $user->notify(new AccountBlacklisted($user->blacklist_expires_at));
        
        return redirect()->route('admin.blacklist')
            ->with('success', 'User blacklisted successfully.');
    }

    /**
     * Remove user from blacklist
     */
    public function removeBlacklist($id)
    {
        $user = User::findOrFail($id);
        $user->status = 'active';
        $user->blacklist_expires_at = null;
        $user->save();

        return redirect()->route('admin.blacklist')
            ->with('success', 'User removed from blacklist.');
    }

    /**
     * System Configuration
     */
    public function configuration()
    {
        $settings = [
            'inactivity_warning_1' => Setting::get('inactivity_warning_1', 7),
            'inactivity_warning_2' => Setting::get('inactivity_warning_2', 14),
            'inactivity_blacklist' => Setting::get('inactivity_blacklist', 21),
            'blacklist_duration'   => Setting::get('blacklist_duration', 14),
            'max_login_attempts'   => Setting::get('max_login_attempts', 5),
        ];

        return view('admin.configuration', compact('settings'));
    }

    /**
     * Update System Configuration
     */
    public function updateConfiguration(Request $request)
    {
        $validated = $request->validate([
            'inactivity_warning_1' => 'required|integer|min:1|max:30',
            'inactivity_warning_2' => 'required|integer|min:1|max:30',
            'inactivity_blacklist' => 'required|integer|min:1|max:60',
            'blacklist_duration'   => 'required|integer|min:1|max:365',
            'max_login_attempts'   => 'required|integer|min:1|max:20',
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value);
        }

        // Also update the runtime config for the current request (optional)
        foreach ($validated as $key => $value) {
            config(['forum.' . $key => $value]);
        }

        return redirect()->route('admin.configuration')
            ->with('success', 'Configuration updated successfully.');
    }

    /**
     * Group Statistics – Detailed analytics for a single group (Admin)
     */
    public function groupStatistics($groupId)
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

        // Top topics by posts
        $topTopics = Topic::where('group_id', $groupId)
            ->withCount('posts')
            ->orderBy('posts_count', 'desc')
            ->limit(5)
            ->get();

        // ✅ Top students (by posts) – ADD THIS
        $topStudents = User::where('role', 'student')
            ->whereHas('groups', function($query) use ($groupId) {
                $query->where('group_id', $groupId);
            })
            ->withCount(['posts' => function($query) use ($groupId) {
                $query->whereHas('topic', function($q) use ($groupId) {
                    $q->where('group_id', $groupId);
                });
            }])
            ->orderBy('posts_count', 'desc')
            ->limit(10)
            ->get();

        // Category distribution
        $categories = Topic::where('group_id', $groupId)
            ->whereNotNull('ml_category')
            ->select('ml_category', DB::raw('count(*) as count'))
            ->groupBy('ml_category')
            ->get();

        // Lecturer who created the group
        $lecturer = User::find($group->created_by);

        return view('admin.group-statistics', compact(
            'group',
            'dailyActivity',
            'topTopics',
            'topStudents',   // ✅ ADD THIS
            'categories',
            'lecturer'
        ));
    }

    /**
     * List all groups with analytics links (Admin)
     */
    public function groupsList()
    {
        // Fetch all groups with counts
        $groups = Group::withCount(['topics', 'users'])
            ->orderBy('topics_count', 'desc')
            ->get();

        // Add posts_count (replies) to each group
        foreach ($groups as $g) {
            $g->posts_count = \App\Models\Post::whereHas('topic', function ($q) use ($g) {
                $q->where('group_id', $g->id);
            })->count();
        }

        // Sort by topics_count (default)
        $groups = $groups->sortByDesc('topics_count')->values();

        return view('admin.groups-list', compact('groups'));
    }

    /**
     * Export full admin report as PDF (A4 portrait)
     */
    public function exportReport()
    {
        // ---- Base Stats ----
        $totalUsers      = User::count();
        $totalStudents   = User::where('role', 'student')->count();
        $totalLecturers  = User::where('role', 'lecturer')->count();
        $totalAdmins     = User::where('role', 'admin')->count();
        $totalGroups     = Group::count();
        $totalTopics     = Topic::count();
        $totalPosts      = Post::count();
        $totalQuizzes    = Quiz::count();
        $totalSubmissions= QuizSubmission::count();

        $pendingRegistrations = User::where('role', 'student')->where('status', 'pending')->count();
        $blacklistedUsers     = User::where('status', 'blacklisted')->count();
        $warnedOnce   = User::where('status', 'warned_once')->count();
        $warnedTwice  = User::where('status', 'warned_twice')->count();

        // ---- Group Rankings ----
        // By topics
        $groupRankByTopics = Group::withCount('topics')
            ->orderByDesc('topics_count')
            ->limit(20)
            ->get();

        // By replies (total posts across all topics in the group)
        $groupRankByReplies = Group::withCount(['topics as posts_count' => function ($q) {
            $q->withCount('posts');
        }])
        ->orderByDesc('posts_count')
        ->limit(20)
        ->get();

        // ---- Student Rankings ----
        // By total posts (replies)
        $topPosters = User::where('role', 'student')
            ->withCount('posts')
            ->orderByDesc('posts_count')
            ->limit(20)
            ->get();

        // By replies (same as posts, but we can also compute separately if needed)
        $topRepliers = User::where('role', 'student')
            ->withCount('posts')
            ->orderByDesc('posts_count')
            ->limit(20)
            ->get();

        // ---- Quiz Performance ----
        $quizStats = [
            'total_quizzes'    => $totalQuizzes,
            'total_submissions'=> $totalSubmissions,
            'avg_score'        => $totalSubmissions > 0 ? QuizSubmission::avg('score') : 0,
            'pass_rate'        => 0,
            'top_students'     => collect([]),
            'quiz_performance' => collect([]),
        ];

        if ($totalSubmissions > 0) {
            // Pass rate (score >= 50)
            $passed = QuizSubmission::where('score', '>=', 50)->count();
            $quizStats['pass_rate'] = round(($passed / $totalSubmissions) * 100, 1);

            // Top students by average score (at least 5 submissions to avoid outliers)
            $topStudents = \DB::table('quiz_submissions')
                ->select('user_id', \DB::raw('AVG(score) as avg_score'))
                ->groupBy('user_id')
                ->having('avg_score', '>=', 50)
                ->orderByDesc('avg_score')
                ->limit(20)
                ->get();

            $userIds = $topStudents->pluck('user_id')->toArray();
            $users = User::whereIn('id', $userIds)->get()->keyBy('id');

            $quizStats['top_students'] = $topStudents->map(function ($item) use ($users) {
                $user = $users->get($item->user_id);
                return (object) [
                    'name'      => $user ? $user->name : 'Unknown',
                    'avg_score' => round($item->avg_score, 1),
                ];
            });

            // Per-quiz performance (only quizzes with submissions)
            $quizStats['quiz_performance'] = Quiz::whereHas('submissions')
                ->withCount('submissions')
                ->withAvg('submissions', 'score')
                ->orderByDesc('submissions_count')
                ->limit(20)
                ->get()
                ->map(function ($q) {
                    $q->avg_score = round($q->submissions_avg_score ?? 0, 1);
                    return $q;
                });
        }

        // ---- Recent Activity ----
        $recentUsers = User::latest()->limit(20)->get();
        $recentBlacklistLogs = BlacklistLog::with('user')->latest()->limit(20)->get();

        // ---- Pass all variables to view ----
        $data = compact(
            'totalUsers', 'totalStudents', 'totalLecturers', 'totalAdmins',
            'totalGroups', 'totalTopics', 'totalPosts',
            'totalQuizzes', 'totalSubmissions',
            'pendingRegistrations', 'blacklistedUsers', 'warnedOnce', 'warnedTwice',
            'groupRankByTopics', 'groupRankByReplies',
            'topPosters', 'topRepliers',
            'quizStats',
            'recentUsers', 'recentBlacklistLogs'
        );
        $data['generatedAt'] = now()->format('Y-m-d H:i:s');

        $pdf = \PDF::loadView('admin.report-pdf', $data);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('admin-report-' . now()->format('Y-m-d') . '.pdf');
    }
}