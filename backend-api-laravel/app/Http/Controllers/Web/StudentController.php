<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Topic;
use App\Models\Quiz;
use App\Models\Post;
use App\Models\QuizSubmission;
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
        
        // Get group IDs the user is in - ✅ FIXED: Specify table
        $groupIds = $user->groups()->pluck('groups.id')->toArray();
        
        // Get recent topics from user's groups
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
        
        // Get available groups (groups the user is NOT in)
        $availableGroups = Group::whereNotIn('id', $groupIds)
            ->withCount(['topics', 'users'])
            ->orderBy('name')
            ->get();
        
        // Stats
        $totalTopics = Topic::whereIn('group_id', $groupIds)->where('creator_id', $user->id)->count();
        $totalPosts = Post::where('user_id', $user->id)->count();
        $totalLikes = 0; // Placeholder
        $totalQuizzesTaken = QuizSubmission::where('user_id', $user->id)->count();
        
        return view('student.dashboard', compact(
            'groups', 
            'availableGroups',
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
     * List All Groups (Index) - Shows ALL groups with membership status
     */
    public function groups()
    {
        $user = Auth::user();
        
        // Get IDs of groups the user is already in - ✅ FIXED: Specify table
        $userGroupIds = $user->groups()->pluck('groups.id')->toArray();
        
        // Fetch ALL groups with counts
        $groups = Group::withCount(['topics', 'users'])
            ->orderBy('name')
            ->get();
        
        // Add a flag to each group indicating if the user is a member
        $groups->each(function ($group) use ($userGroupIds) {
            $group->isMember = in_array($group->id, $userGroupIds);
        });
        
        return view('groups.index', compact('groups'));
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
     * Join a Group
     */
    public function joinGroup($groupId)
    {
        $user = Auth::user();
        $group = Group::findOrFail($groupId);
        
        // Check if already a member
        if ($user->groups()->where('group_id', $groupId)->exists()) {
            return redirect()->route('groups.topics', $groupId)
                ->with('info', 'You are already a member of this group.');
        }
        
        // Add user to group with has_agreed_rules = false
        $user->groups()->attach($groupId, ['has_agreed_rules' => false]);
        
        // Redirect to guidelines to accept rules
        return redirect()->route('groups.guidelines', $groupId)
            ->with('success', 'You have joined the group. Please review and accept the guidelines.');
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

    /**
     * Show create topic form
     */
    public function createTopic()
    {
        // Get groups the user belongs to (for dropdown)
        $groups = Auth::user()->groups()->get();
        
        return view('topics.create', compact('groups'));
    }

    /**
     * Store a new topic
     */
    public function storeTopic(Request $request)
    {
        $validated = $request->validate([
            'group_id' => 'required|exists:groups,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        
        $topic = Topic::create([
            'group_id' => $validated['group_id'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'creator_id' => Auth::id(),
            'ml_category' => null,
        ]);
        
        Auth::user()->update(['last_communicated_at' => now()]);
        
        return redirect()->route('topics.show', [$topic->group_id, $topic->id])
            ->with('success', 'Topic created successfully.');
    }

    /**
     * Show a single topic with its posts
     */
    public function showTopic($groupId, $topicId)
    {
        $topic = Topic::with(['creator', 'group'])
            ->findOrFail($topicId);
        
        $posts = Post::where('topic_id', $topicId)
            ->with('author')
            ->orderBy('created_at', 'asc')
            ->get();
        
        return view('topics.show', compact('topic', 'posts'));
    }

    /**
     * Store a new post
     */
    public function storePost(Request $request)
    {
        $validated = $request->validate([
            'topic_id' => 'required|exists:topics,id',
            'content' => 'required|string|min:3',
            'is_private' => 'boolean',
            'excluded_user_ids' => 'nullable|array',
        ]);
        
        $post = Post::create([
            'topic_id' => $validated['topic_id'],
            'user_id' => Auth::id(),
            'content' => $validated['content'],
            'is_private' => $validated['is_private'] ?? false,
        ]);
        
        // Handle exclusions if private
        if ($post->is_private && !empty($validated['excluded_user_ids'])) {
            $post->excludedUsers()->attach($validated['excluded_user_ids']);
        }
        
        Auth::user()->update(['last_communicated_at' => now()]);
        
        return redirect()->back()->with('success', 'Post added successfully.');
    }

    /**
     * Toggle like on a post
     */
    public function toggleLike($postId)
    {
        // Placeholder - will implement later
        return response()->json(['status' => 'success']);
    }

    /**
     * Show performance report
     */
    public function performanceReport($quizId)
    {
        // Placeholder - will implement later
        return view('quiz.report', ['quizId' => $quizId]);
    }
}