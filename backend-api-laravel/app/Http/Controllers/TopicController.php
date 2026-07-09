<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use Illuminate\Http\Request;

class TopicController extends Controller
{
    /**
     * Display the specified cascading conversation feed.
     */
    public function show(Topic $topic)
    {
        // Eager load the creator of the topic and all associated posts with their authors
        $topic->load(['creator', 'posts.user']);
        
        // Count the posts dynamically
        $topic->loadCount('posts');

        // Return the specific conversation feed template view
        return view('topics.show', compact('topic'));
    }
}