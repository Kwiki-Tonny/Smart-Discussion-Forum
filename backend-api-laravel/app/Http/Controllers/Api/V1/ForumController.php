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
        $groups = Group::select('id', 'name', 'description', 'created_at')
            ->withCount(['topics', 'users'])
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
            ->withCount('posts')
            ->get(['id', 'group_id', 'title', 'description', 'creator_id', 'created_at', 'ml_category']);

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
            'parent_id' => 'nullable|exists:posts,id',   // ✅ support parent_id
        ]);

        $post = Post::create([
            'topic_id' => $validatedData['topic_id'],
            'user_id' => $validatedData['user_id'],
            'content' => $validatedData['content'],
            'is_private' => $validatedData['is_private'],
            'parent_id' => $validatedData['parent_id'] ?? null,
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
            'data' => $post->load('excludedUsers', 'author:id,name,email,role'),
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
            ->withCount('likes')
            ->with(['likes' => function ($q) use ($userId) {
                $q->where('user_id', $userId);
            }])
            ->orderBy('created_at', 'asc')
            ->get(['id', 'topic_id', 'user_id', 'content', 'is_private', 'created_at', 'parent_id']);

        // Add is_liked attribute
        $posts->each(function ($post) {
            $post->is_liked = $post->likes->isNotEmpty();
            unset($post->likes);
        });

        return response()->json([
            'status' => 'success',
            'topic' => $topic,
            'data' => $posts,
        ]);
    }

    /**
     * Endpoint 6: Sync Upload (REAL implementation).
     */
    public function syncUpload(Request $request)
    {
        $user = $request->user();
        $posts = $request->input('posts', []);
        $created = [];

        foreach ($posts as $postData) {
            // Validate required fields
            if (!isset($postData['topic_id']) || !isset($postData['content'])) {
                continue;
            }

            $post = Post::create([
                'topic_id' => $postData['topic_id'],
                'user_id' => $postData['user_id'] ?? $user->id,
                'content' => $postData['content'],
                'is_private' => $postData['is_private'] ?? false,
                'parent_id' => $postData['parent_id'] ?? null,
                'created_at' => $postData['created_at'] ?? now(),
                'updated_at' => now(),
            ]);

            $created[] = ['id' => $post->id];
        }

        return response()->json(['data' => $created]);
    }

    /**
     * Endpoint 7: Sync Download (REAL implementation).
     */
    public function syncDownload(Request $request)
    {
        $since = $request->input('since', now()->subDays(30));
        $posts = Post::where('created_at', '>', $since)
                    ->with(['author:id,name,email,role'])
                    ->orderBy('created_at', 'asc')
                    ->get();

        return response()->json(['data' => $posts]);
    }
}