<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\ForumController; // Explicitly imports your new controller class

/*
|--------------------------------------------------------------------------
| Default Laravel Boilerplate Route
|--------------------------------------------------------------------------
*/
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


/*
|--------------------------------------------------------------------------
| Sprint 1 Academic Forum Custom Endpoints (API Version 1)
|--------------------------------------------------------------------------
*/

// Public Health Check Endpoint (Verifies the server engine is up and running)
Route::get('/v1/health-check', function () {
    return response()->json([
        'status' => 'online',
        'timestamp' => now()->toIso8601String(),
        'project' => 'Smart Academic Forum Core Engine'
    ], 200);
});

// Grouped routes for our application resources
Route::prefix('v1')->group(function () {
    
    // Read operations to populate the dashboards for Web & Java clients
    Route::get('/groups', [ForumController::class, 'getGroups']);
    Route::get('/groups/{id}/topics', [ForumController::class, 'getTopicsByGroup']);
    
    // Write operations to publish interactive response posts
    Route::post('/posts/publish', [ForumController::class, 'createPost']);
    
});