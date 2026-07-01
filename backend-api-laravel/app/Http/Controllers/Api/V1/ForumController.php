<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Topic;
use App\Models\Post;
use Illuminate\Http\Request;

class ForumController extends Controller
{
    /**
     * Endpoint 1: Fetch all learning groups/cohorts.
     * This provides the Web and Java clients with the initial list of available forum spaces.
     */
    public function getGroups()
    {
        // Fetch group records safely out of smart_forum_db using Eloquent
        $groups = Group::select('id', 'name', 'description', 'created_at')->get();

        // Return a standardized machine-readable JSON payload
        return response()->json([
            'status' => 'success',
            'count' => $groups->count(),
            'data' => $groups
        ], 200); // 200 OK HTTP Status Code
    }

    /**
     * Endpoint 2: Fetch all discussion threads inside a specific group.
     * Uses 'Eager Loading' to fetch creator data cleanly without heavy SQL strain.
     */
    public function getTopicsByGroup($groupId)
    {
        // Locate the group or instantly fail with a clean 404 message block if it doesn't exist
        $group = Group::findOrFail($groupId);

        // LEARNING POINT: Eager Loading ('with')
        // Instead of running separate queries for every single topic creator, 
        // ->with('creator:id,name,role') joins the user details efficiently in one query block.
        $topics = $group->topics()->with('creator:id,name,role')->get();

        return response()->json([
            'status' => 'success',
            'group_name' => $group->name,
            'data' => $topics
        ], 200);
    }

    /**
     * Endpoint 3: Create/Publish a new interactive response post.
     * Implements strict incoming request data verification.
     */

    public function createPost(Request $request)
    {
        $validatedData = $request->validate([
            'topic_id' => 'required|exists:topics,id',
            'user_id' => 'required|exists:users,id',
            'content' => 'required|string|min:3',
            'is_private' => 'required|boolean',
            'excluded_user_ids' => 'nullable|array', // List of user IDs to exclude
            'excluded_user_ids.*' => 'exists:users,id', // Validate each ID
        ]);

        // Create the post
        $post = Post::create([
            'topic_id' => $validatedData['topic_id'],
            'user_id' => $validatedData['user_id'],
            'content' => $validatedData['content'],
            'is_private' => $validatedData['is_private'],
        ]);

        // If private, attach exclusions
        if ($validatedData['is_private'] && !empty($validatedData['excluded_user_ids'])) {
            $post->excludedUsers()->attach($validatedData['excluded_user_ids']);
        }

        // Update the user's last_communicated_at timestamp (for inactivity tracking)
        $user = User::find($validatedData['user_id']);
        $user->last_communicated_at = now();
        $user->save();

        return response()->json([
            'status' => 'created',
            'message' => 'Post successfully recorded.',
            'data' => $post->load('excludedUsers'), // Load exclusions to return to client
        ], 201);
    }

    public function getPostsByTopic($topicId, Request $request)
    {
        $topic = Topic::with('group')->findOrFail($topicId);
        $userId = $request->user()->id; // Get the authenticated user's ID

        $posts = Post::where('topic_id', $topicId)
                    ->visibleToUser($userId) // <-- Applying our Privacy Filter!
                    ->with('author:id,name,email,role')
                    ->orderBy('created_at', 'asc')
                    ->get();

        return response()->json([
            'status' => 'success',
            'topic' => $topic,
            'data' => $posts,
        ]);
    }

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
            'ml_category' => null, // Dev 5 will fill this via a background job later
        ]);

        // Update user communication timestamp
        $request->user()->update(['last_communicated_at' => now()]);

        return response()->json([
            'status' => 'created',
            'message' => 'Topic created successfully.',
            'data' => $topic,
        ], 201);
    }

        // Stub for Upload
    public function syncUpload(Request $request)
    {
        // In Sprint 3, you will loop through $request->input('posts') and save them.
        return response()->json([
            'status' => 'success',
            'message' => 'Sync upload endpoint ready (Sprint 3 implementation pending).'
        ]);
    }

    // Stub for Download
    public function syncDownload(Request $request)
    {
        $since = $request->input('since', now()->subDays(30));
        // In Sprint 3, you will query posts created after $since.
        return response()->json([
            'status' => 'success',
            'data' => [] // Return empty array for now
        ]);
    }
}

