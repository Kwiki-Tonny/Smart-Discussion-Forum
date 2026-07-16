<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Group;
use App\Models\Topic;
use App\Models\Post;
use App\Models\Quiz;
use App\Models\BlacklistLog;
use App\Models\QuizSubmission;
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

        // Pending registrations (users without groups or inactive)
        $pendingRegistrations = User::where('role', 'student')
            ->whereDoesntHave('groups')
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
            'userGrowth'
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
        $pendingUsers = User::where('role', 'student')
            ->whereDoesntHave('groups')
            ->orderBy('created_at', 'asc')
            ->get();

        $approvedUsers = User::where('role', 'student')
            ->whereHas('groups')
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

        // Set status to active
        $user->status = 'active';
        $user->save();

        // Assign to a default group (optional)
        $defaultGroup = Group::first();
        if ($defaultGroup) {
            $user->groups()->attach($defaultGroup->id, ['has_agreed_rules' => false]);
        }

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

        $user->status = 'blacklisted';
        $user->blacklist_expires_at = now()->addDays($validated['duration']);
        $user->save();

        // Revoke all tokens
        $user->tokens()->delete();

        BlacklistLog::create([
            'user_id' => $user->id,
            'reason' => $validated['reason'],
            'action_type' => 'manual_blacklist',
            'expires_at' => $user->blacklist_expires_at,
        ]);

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
        // Get current settings from config or database
        $settings = [
            'inactivity_warning_1' => config('forum.inactivity_warning_1', 7),
            'inactivity_warning_2' => config('forum.inactivity_warning_2', 14),
            'inactivity_blacklist' => config('forum.inactivity_blacklist', 21),
            'blacklist_duration' => config('forum.blacklist_duration', 14),
            'max_login_attempts' => config('forum.max_login_attempts', 5),
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
            'blacklist_duration' => 'required|integer|min:1|max:365',
            'max_login_attempts' => 'required|integer|min:1|max:20',
        ]);

        // Store in config or database
        foreach ($validated as $key => $value) {
            config(['forum.' . $key => $value]);
        }

        // Optionally store in a settings table
        // Setting::updateOrCreate(['key' => $key], ['value' => $value]);

        return redirect()->route('admin.configuration')
            ->with('success', 'Configuration updated successfully.');
    }
}