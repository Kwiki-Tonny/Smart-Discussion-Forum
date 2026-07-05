<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Topic;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    /**
     * Student Dashboard
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get groups the user belongs to
        $groups = $user->groups()
            ->withCount('topics')
            ->with(['topics' => function($query) {
                $query->latest()->limit(1);
            }])
            ->get();
        
        // Get recent topics from user's groups
        $groupIds = $groups->pluck('id')->toArray();
        $recentTopics = Topic::whereIn('group_id', $groupIds)
            ->with(['group', 'creator'])
            ->withCount('posts')
            ->latest()
            ->limit(10)
            ->get();
        
        // Get recommendations
        $recommendations = Topic::whereIn('group_id', $groupIds)
            ->whereNotIn('id', $recentTopics->pluck('id')->toArray())
            ->with('group')
            ->inRandomOrder()
            ->limit(5)
            ->get();
        
        // Get upcoming quizzes (if starts_at column exists)
        try {
            $upcomingQuizzes = Quiz::whereIn('group_id', $groupIds)
                ->with('group')
                ->where('starts_at', '>', now())
                ->orderBy('starts_at')
                ->limit(5)
                ->get();
        } catch (\Exception $e) {
            $upcomingQuizzes = collect([]);
        }
        
        // Stats
        $totalTopics = Topic::whereIn('group_id', $groupIds)->where('creator_id', $user->id)->count();
        $totalPosts = \App\Models\Post::where('user_id', $user->id)->count();
        $totalLikes = 0; // Placeholder
        $totalQuizzesTaken = \App\Models\QuizSubmission::where('user_id', $user->id)->count();
        
        return view('student.dashboard', compact(
            'groups', 
            'recentTopics', 
            'recommendations', 
            'upcomingQuizzes',
            'totalTopics',
            'totalPosts',
            'totalLikes',
            'totalQuizzesTaken'
        ));
    }

    /**
     * Show Group Guidelines (Rules Gate)
     */
    public function guidelines($groupId)
    {
        $user = Auth::user();
        $group = Group::withCount(['topics', 'users'])->findOrFail($groupId);
        
        // Check if user already agreed
        $hasAgreed = $user->groups()
            ->where('group_id', $groupId)
            ->wherePivot('has_agreed_rules', true)
            ->exists();
        
        if ($hasAgreed) {
            return redirect()->route('groups.topics', $groupId);
        }
        
        return view('groups.guidelines', compact('group'));
    }

    /**
     * Accept Group Rules
     */
    public function agreeRules($groupId)
    {
        $user = Auth::user();
        $group = Group::findOrFail($groupId);
        
        // Update pivot table
        $user->groups()->updateExistingPivot($groupId, [
            'has_agreed_rules' => true
        ]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Rules accepted successfully.'
        ]);
    }

    /**
     * Decline Group Rules
     */
    public function declineRules($groupId)
    {
        $user = Auth::user();
        $group = Group::findOrFail($groupId);
        
        // Remove user from group (detach)
        $user->groups()->detach($groupId);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Group access declined.'
        ]);
    }

    /**
     * List Groups (Index)
     */
    public function groups()
    {
        $user = Auth::user();
        $groups = $user->groups()
            ->withCount('topics')
            ->with(['topics' => function($query) {
                $query->latest()->limit(1);
            }])
            ->get();
        
        return view('groups.index', compact('groups'));
    }

    /**
     * List Topics in a Group
     */
    public function topics($groupId)
    {
        $user = Auth::user();
        $group = Group::withCount('topics')->findOrFail($groupId);
        
        // Check if user has agreed to rules
        $hasAgreed = $user->groups()
            ->where('group_id', $groupId)
            ->wherePivot('has_agreed_rules', true)
            ->exists();
        
        if (!$hasAgreed) {
            return redirect()->route('groups.guidelines', $groupId);
        }
        
        $topics = Topic::where('group_id', $groupId)
            ->with(['creator', 'posts'])
            ->withCount('posts')
            ->latest()
            ->get();
        
        return view('groups.topics', compact('group', 'topics'));
    }

    // ... other methods will be added later
}