<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Topic;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;

class ForumController extends Controller
{
    /**
     * Endpoint 1: Fetch all learning groups/cohorts.
     * Enhanced: adds 'is_member' flag for the authenticated user.
     */
    public function getGroups(Request $request)
    {
        $user = $request->user();
        $groupIds = $user->groups()->pluck('groups.id')->toArray(); // all group IDs the user is in

        $groups = Group::select('id', 'name', 'description', 'created_at')->get();

        $groups->each(function ($group) use ($groupIds) {
            $group->is_member = in_array($group->id, $groupIds);
        });

        return response()->json([
            'status' => 'success',
            'count' => $groups->count(),
            'data' => $groups
        ], 200);
    }

    /**
     * Search groups by name (with is_member flag).
     */
    public function searchGroups(Request $request)
    {
        $query = $request->query('q', '');
        $user = $request->user();

        $groups = Group::where('name', 'LIKE', "%{$query}%")
            ->select('id', 'name', 'description', 'created_at')
            ->get();

        $groups->each(function ($group) use ($user) {
            $group->is_member = $user->groups()->where('group_id', $group->id)->exists();
        });

        return response()->json([
            'status' => 'success',
            'count' => $groups->count(),
            'data' => $groups
        ], 200);
    }

    /**
     * Endpoint 2: Fetch all discussion threads inside a specific group.
     */
    public function getTopicsByGroup($groupId)
    {
        $group = Group::findOrFail($groupId);

        $topics = $group->topics()
            ->with('creator:id,name,role')
            ->get();

        return response()->json([
            'status' => 'success',
            'group_name' => $group->name,
            'data' => $topics
        ], 200);
    }

    /**
     * Endpoint 3: Create a new discussion topic.
     */
    public function createTopic(Request $request)
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
            'creator_id' => $request->user()->id,
            'ml_category' => null,
        ]);

        $request->user()->update(['last_communicated_at' => now()]);

        return response()->json([
            'status' => 'created',
            'message' => 'Topic created successfully.',
            'data' => $topic,
        ], 201);
    }

    /**
     * Endpoint 4: Create/Publish a new interactive response post.
     */
    public function createPost(Request $request)
    {
        $validatedData = $request->validate([
            'topic_id' => 'required|exists:topics,id',
            'user_id' => 'required|exists:users,id',
            'content' => 'required|string|min:3',
            'is_private' => 'required|boolean',
            'excluded_user_ids' => 'nullable|array',
            'excluded_user_ids.*' => 'exists:users,id',
        ]);

        $post = Post::create([
            'topic_id' => $validatedData['topic_id'],
            'user_id' => $validatedData['user_id'],
            'content' => $validatedData['content'],
            'is_private' => $validatedData['is_private'],
        ]);

        if ($validatedData['is_private'] && !empty($validatedData['excluded_user_ids'])) {
            $post->excludedUsers()->attach($validatedData['excluded_user_ids']);
        }

        $user = User::find($validatedData['user_id']);
        $user->last_communicated_at = now();
        $user->save();

        return response()->json([
            'status' => 'created',
            'message' => 'Post successfully recorded.',
            'data' => $post->load('excludedUsers'),
        ], 201);
    }

    /**
     * Endpoint 5: Fetch posts for a specific topic with Privacy Filter applied.
     */
    public function getPostsByTopic($topicId, Request $request)
    {
        $topic = Topic::with('group')->findOrFail($topicId);
        $userId = $request->user()->id;

        $posts = Post::where('topic_id', $topicId)
            ->visibleToUser($userId)
            ->with('author:id,name,email,role')
            ->withCount('likes')                                 // → likes_count
            ->with(['likes' => function ($q) use ($userId) {     // → is_liked
                $q->where('user_id', $userId);
            }])
            // optional: load excluded users if the client needs them
            // ->with('excludedUsers:id,name,email')
            ->orderBy('created_at', 'asc')
            ->get(['id', 'topic_id', 'user_id', 'content', 'is_private', 'created_at', 'parent_id']);

        // Add is_liked attribute
        $posts->each(function ($post) {
            $post->is_liked = $post->likes->isNotEmpty();
            // remove the 'likes' relation from the response to keep it clean
            unset($post->likes);
        });

        return response()->json([
            'status' => 'success',
            'topic' => $topic,
            'data' => $posts,
        ]);
    }

    /**
     * Endpoint 6: Sync Upload (Stubbed for Sprint 3).
     */
    public function syncUpload(Request $request)
    {
        // Implementation pending Sprint 3.
        return response()->json([
            'status' => 'success',
            'message' => 'Sync upload endpoint ready (Sprint 3 implementation pending).',
        ]);
    }

    /**
     * Endpoint 7: Sync Download (Stubbed for Sprint 3).
     */
    public function syncDownload(Request $request)
    {
        $since = $request->input('since', now()->subDays(30));
        // Implementation pending Sprint 3.
        return response()->json([
            'status' => 'success',
            'data' => [],
        ]);
    }
}