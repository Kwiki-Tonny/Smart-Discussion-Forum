<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Http\Controllers\Api\V1\ForumController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\StudentController; // <-- NEW: For student-specific endpoints

/*
|--------------------------------------------------------------------------
| Public Endpoints (No Authentication Required)
|--------------------------------------------------------------------------
*/

// 1. Health Check (For Java Desktop Ping)
Route::get('/v1/health-check', function () {
    return response()->json([
        'status' => 'online',
        'timestamp' => now()->toIso8601String(),
        'project' => 'Smart Academic Forum Core Engine'
    ], 200);
});

// 2. Public Authentication Routes (Register & Login)
Route::prefix('v1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

/*
|--------------------------------------------------------------------------
| Protected Endpoints (Requires Valid Sanctum Token)
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {

    // --- User & Auth Management ---
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // --- Academic Forum Core (Requires Role: Student, Lecturer, or Admin) ---
    Route::middleware(['role:admin,lecturer,student'])->group(function () {
        
        // -------------------- READ OPERATIONS --------------------
        // Fetch all groups (ENHANCED: includes is_member field)
        Route::get('/groups', [ForumController::class, 'getGroups']);
        
        // Fetch topics inside a specific group
        Route::get('/groups/{id}/topics', [ForumController::class, 'getTopicsByGroup']);
        
        // Fetch posts for a specific topic with Privacy Filter applied
        Route::get('/topics/{id}/posts', [ForumController::class, 'getPostsByTopic']);

        // -------------------- WRITE OPERATIONS --------------------
        // Create a new post (handles is_private & exclusions)
        Route::post('/posts/publish', [ForumController::class, 'createPost']);
        
        // Create a new discussion topic
        Route::post('/topics', [ForumController::class, 'createTopic']);

        // -------------------- SYNC ENDPOINTS --------------------
        // Upload pending offline posts from Java desktop
        Route::post('/sync/upload', [ForumController::class, 'syncUpload']);
        
        // Download new posts since a given timestamp
        Route::get('/sync/download', [ForumController::class, 'syncDownload']);
    });

    // ============================================================
    //  NEW STUDENT-ONLY ENDPOINTS (Desktop Client)
    //  These are only accessible to users with the 'student' role.
    // ============================================================
    Route::middleware(['role:student'])->group(function () {

        // ─── GROUP MEMBERSHIP ────────────────────────────────────
        // Enhanced getGroups already includes is_member.
        // Search groups by name
        Route::get('/groups/search', [ForumController::class, 'searchGroups']);
        // Join a group
        Route::post('/groups/{id}/join', [StudentController::class, 'joinGroup']);
        Route::post('/groups/{id}/rules/accept', [StudentController::class, 'acceptRules']);
        Route::get('/users', [StudentController::class, 'getUsers']);
        // Leave a group
        Route::delete('/groups/{id}/leave', [StudentController::class, 'leaveGroup']);

        // ─── QUIZZES ─────────────────────────────────────────────
        // Get all quizzes available to the student (based on group membership)
        Route::get('/quizzes', [StudentController::class, 'quizIndex']);
        // Start a quiz – returns questions + started_at + duration
        Route::get('/quizzes/{id}/start', [StudentController::class, 'takeQuiz']);
        // Submit quiz answers (supports single, multiple, text)
        Route::post('/quizzes/{id}/submit', [StudentController::class, 'submitQuiz']);

        // ─── STUDENT STATS & RESULTS ─────────────────────────────
        // Get profile statistics (posts, replies, topics, quizzes taken)
        Route::get('/user/stats', [StudentController::class, 'profileStats']);
        // Get list of past quiz attempts
        Route::get('/user/quiz-attempts', [StudentController::class, 'attemptsList']);
        // Get detailed breakdown of a specific attempt
        Route::get('/user/quiz-attempts/{attempt}', [StudentController::class, 'attemptDetail']);
        //like a post
        Route::post('/posts/{post}/like', [StudentController::class, 'toggleLike']);
    });
});