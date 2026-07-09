<?php

use Illuminate\Support\Facades\Route;
use App\Models\Topic;
use App\Models\Group;
use App\Models\Post;
use App\Http\Controllers\TopicController; // Added this line

// Root Route - Displays the Dashboard View with Topics
Route::get('/', function () {
    $topics = class_exists(Topic::class) ? Topic::with(['creator', 'posts'])->get() : collect();
    return view('welcome', compact('topics'));
});

// Feature 2: View Dynamic Cascading Conversation Feed (Clean Controller Route)
Route::get('/topics/{topic}', [TopicController::class, 'show'])->name('topics.show');

// Group Rules Access Gate Mechanics
Route::get('/groups/{group}/rules', function ($group) {
    $group = Group::findOrFail($group);
    return view('groups.rules-gate', compact('group'));
})->name('groups.rules-gate');

Route::post('/groups/{group}/agree', function ($group) {
    return redirect('/')->with('success', 'You have agreed to the group rules! Welcome!');
})->name('groups.agree-rules');

Route::post('/groups/{group}/decline', function ($group) {
    return view('groups.access-denied');
})->name('groups.decline-rules');