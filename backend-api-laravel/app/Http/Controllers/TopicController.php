<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use App\Models\Group;
use Illuminate\Http\Request;

class TopicController extends Controller
{
    public function show($group, $topic)
    {
        $group = Group::findOrFail($group);
        $topic = Topic::findOrFail($topic);

        $topic->load(['creator', 'posts.user']);
        $topic->loadCount('posts');

        $posts = $topic->posts;

        return view('topics.show', compact('group', 'topic', 'posts'));
    }
}