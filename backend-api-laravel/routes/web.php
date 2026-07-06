<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\StudentController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ==================== PUBLIC ROUTES ====================

// 1. Root Redirect
Route::redirect('/', '/login')->name('root.redirect');

// 2. Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');

// 3. Password Reset (Stubbed - can be implemented later)
Route::get('/password/reset', [AuthController::class, 'showResetForm'])->name('password.request');
Route::post('/password/reset', [AuthController::class, 'resetPassword'])->name('password.update');


// ==================== PROTECTED ROUTES (Require Authentication) ====================

Route::middleware(['auth'])->group(function () {

    // ---------- Logout ----------
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // ---------- Student Dashboard ----------
    Route::get('/dashboard', [StudentController::class, 'index'])->name('dashboard');

    // ---------- Groups ----------
    Route::get('/groups', [StudentController::class, 'groups'])->name('groups.index');
    Route::get('/groups/{group}/topics', [StudentController::class, 'topics'])->name('groups.topics');
    Route::get('/groups/{group}/guidelines', [StudentController::class, 'guidelines'])->name('groups.guidelines');
    Route::post('/groups/{group}/agree', [StudentController::class, 'agreeRules'])->name('groups.agree');
    Route::post('/groups/{group}/decline', [StudentController::class, 'declineRules'])->name('groups.decline');
    Route::post('/groups/{group}/join', [StudentController::class, 'joinGroup'])->name('groups.join');

    // ---------- Topics ----------
    Route::get('/topics/create', [StudentController::class, 'createTopic'])->name('topics.create');
    Route::post('/topics', [StudentController::class, 'storeTopic'])->name('topics.store');
    Route::get('/groups/{group}/topics/{topic}', [StudentController::class, 'showTopic'])->name('topics.show');
    Route::get('/topics/{topic}/export', [StudentController::class, 'exportPdf'])->name('topics.export'); // NEW

    // ---------- Posts ----------
    Route::post('/posts', [StudentController::class, 'storePost'])->name('posts.store');
    Route::post('/posts/{post}/like', [StudentController::class, 'toggleLike'])->name('posts.like');
    Route::post('/posts/reply', [StudentController::class, 'storeReply'])->name('posts.reply'); // NEW

    // ---------- Quiz ----------
    Route::get('/quiz/{id}/performance-report', [StudentController::class, 'performanceReport'])->name('quiz.report');
});


// ==================== ROUTE PARAMETER PATTERNS ====================

Route::pattern('group', '[0-9]+');
Route::pattern('topic', '[0-9]+');
Route::pattern('post', '[0-9]+');
Route::pattern('id', '[0-9]+');