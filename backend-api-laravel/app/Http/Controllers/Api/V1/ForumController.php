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
        // LEARNING POINT: Data Validation
        // Never trust data coming over the network! $request->validate() ensures 
        // that required fields exist and conform to exact database rules before running SQL.
        $validatedData = $request->validate([
            'topic_id' => 'required|exists:topics,id',
            'user_id' => 'required|exists:users,id',
            'content' => 'required|string|min:3',
            'is_private' => 'required|boolean'
        ]);

        // Insert row directly into the posts table via Eloquent mass assignment
        $post = Post::create($validatedData);

        return response()->json([
            'status' => 'created',
            'message' => 'Post successfully recorded in database tracking registers.',
            'data' => $post
        ], 201); // 201 Created HTTP Status Code
    }
}

