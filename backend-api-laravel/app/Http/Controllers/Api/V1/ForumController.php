<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Topic;
use App\Models\Post;
use App\Models\User;  // <-- ADDED: Required for createPost method
use Illuminate\Http\Request;

class ForumController extends Controller
{
    /**
     * Endpoint 1: Fetch all learning groups/cohorts.
     * This provides the Web and Java clients with the initial list of available forum spaces.
     */
    public function getGroups()
    {
        $groups = Group::select('id', 'name', 'description', 'created_at')->get();

        return response()->json([
            'status' => 'success',
            'count' => $groups->count(),
            'data' => $groups
        ], 200);
    }

    /**
     * Endpoint 2: Fetch all discussion threads inside a specific group.
     * Uses 'Eager Loading' to fetch creator data cleanly without heavy SQL strain.
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
     * The ml_category field is left as null (will be filled by Developer 5 via background job).
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
            'ml_category' => null, // Will be filled by ML classifier in Sprint 2
        ]);

        // Update user communication timestamp for inactivity tracking
        $request->user()->update(['last_communicated_at' => now()]);

        return response()->json([
            'status' => 'created',
            'message' => 'Topic created successfully.',
            'data' => $topic,
        ], 201);
    }

    /**
     * Endpoint 4: Create/Publish a new interactive response post.
     * Implements strict incoming request data verification.
     * Handles private posts with user exclusions (Privacy Filter).
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
            'data' => $post->load('excludedUsers'),
        ], 201);
    }

    /**
     * Endpoint 5: Fetch posts for a specific topic with Privacy Filter applied.
     * Uses the visibleToUser scope to filter out private posts and exclusions.
     */
    public function getPostsByTopic($topicId, Request $request)
    {
        $topic = Topic::with('group')->findOrFail($topicId);
        $userId = $request->user()->id;

        $posts = Post::where('topic_id', $topicId)
            ->visibleToUser($userId)
            ->with('author:id,name,email,role')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'topic' => $topic,
            'data' => $posts,
        ]);
    }

    /**
     * Endpoint 6: Sync Upload (Stubbed for Sprint 3).
     * Will handle uploading pending offline posts from the Java desktop client.
     */
    public function syncUpload(Request $request)
    {
        // Sprint 3 implementation: Loop through $request->input('posts') and save them.
        return response()->json([
            'status' => 'success',
            'message' => 'Sync upload endpoint ready (Sprint 3 implementation pending).',
        ]);
    }

    /**
     * Endpoint 7: Sync Download (Stubbed for Sprint 3).
     * Will fetch new posts since a given timestamp for the Java desktop client.
     */
    public function syncDownload(Request $request)
    {
        $since = $request->input('since', now()->subDays(30));

        // Sprint 3 implementation: Query posts created after $since.
        return response()->json([
            'status' => 'success',
            'data' => [], // Return empty array for now
        ]);
    }
}