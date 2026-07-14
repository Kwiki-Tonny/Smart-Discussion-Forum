<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class PollController extends Controller
{
    /**
     * Long polling - Stateless (no session)
     */
    public function poll($topicId, Request $request)
    {
        // No session - we don't need Auth here
        $lastPostId = $request->input('last_post_id', 0);
        $userId = $request->input('user_id', 0);
        $timeout = 20;
        $start = time();

        while (time() - $start < $timeout) {
            $post = Post::where('topic_id', $topicId)
                ->where('id', '>', $lastPostId)
                ->where('user_id', '!=', $userId)
                ->with('author')
                ->orderBy('id', 'asc')
                ->first();

            if ($post) {
                return response()->json([
                    'has_updates' => true,
                    'post' => [
                        'id' => $post->id,
                        'content' => $post->content,
                        'author' => $post->author->name ?? 'Unknown',
                        'author_id' => $post->user_id,
                        'created_at' => $post->created_at->diffForHumans(),
                    ],
                    'total' => Post::where('topic_id', $topicId)->count(),
                ]);
            }

            sleep(1);
        }

        return response()->json([
            'has_updates' => false,
        ]);
    }
}