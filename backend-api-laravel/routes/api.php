<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\ForumController;
use App\Http\Controllers\Api\V1\AuthController; // <-- NEW: Imported for Authentication

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
        // Fetch all groups
        Route::get('/groups', [ForumController::class, 'getGroups']);
        
        // Fetch topics inside a specific group
        Route::get('/groups/{id}/topics', [ForumController::class, 'getTopicsByGroup']);
        
        // **NEW** Fetch posts for a specific topic with Privacy Filter applied
        Route::get('/topics/{id}/posts', [ForumController::class, 'getPostsByTopic']);

        // -------------------- WRITE OPERATIONS --------------------
        // **ENHANCED** Create a new post (handles is_private & exclusions)
        Route::post('/posts/publish', [ForumController::class, 'createPost']);
        
        // **NEW** Create a new discussion topic
        Route::post('/topics', [ForumController::class, 'createTopic']);

        // -------------------- SYNC ENDPOINTS (Stubbed for Sprint 3) --------------------
        // Upload pending offline posts from Java desktop
        Route::post('/sync/upload', [ForumController::class, 'syncUpload']);
        
        // Download new posts since a given timestamp
        Route::get('/sync/download', [ForumController::class, 'syncDownload']);
    });
});