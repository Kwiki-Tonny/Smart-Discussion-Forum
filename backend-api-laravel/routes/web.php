<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\StudentController;
use App\Http\Controllers\Web\LecturerController;
use App\Http\Controllers\Api\PollController;

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

    // ---------- Profile ----------
    Route::get('/profile', [StudentController::class, 'profile'])->name('profile');

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
    Route::post('/posts/reply', [StudentController::class, 'storeReply'])->name('posts.reply');

    // ---------- Student Quiz Routes ----------
    Route::get('/quizzes', [StudentController::class, 'quizIndex'])->name('student.quizzes');
    Route::get('/quizzes/{quiz}/take', [StudentController::class, 'takeQuiz'])->name('student.quiz.take');
    Route::post('/quizzes/{quiz}/submit', [StudentController::class, 'submitQuiz'])->name('student.quiz.submit');

    //---------- SSE Routes ----------
    //Route::get('/sse/topic/{topicId}', [StudentController::class, 'sseStream'])->name('sse.stream');

    //---------- Long Polling Routes ----------
    //Route::get('/topics/{topic}/poll', [StudentController::class, 'longPoll'])->name('topics.poll');

    // Stateless polling route - no session!
    Route::get('/poll/topic/{topic}', [App\Http\Controllers\Api\PollController::class, 'poll'])
    ->name('topics.poll');

    // ---------- Quiz ----------
    Route::get('/quiz/{id}/performance-report', [StudentController::class, 'performanceReport'])->name('quiz.report');

    // ---------- Affinity Cache ----------
    Route::post('/affinity/clear', [StudentController::class, 'clearAffinityCache'])->name('affinity.clear');

    //---------- Reccommendations ----------
    Route::get('/recommendations', [StudentController::class, 'recommendations'])->name('recommendations.index');

        // ---------- Lecturer Routes ----------
    Route::middleware(['role:lecturer,admin'])->prefix('lecturer')->group(function () {
        Route::get('/dashboard', [LecturerController::class, 'index'])->name('lecturer.dashboard');
        Route::get('/group/{group}/analytics', [LecturerController::class, 'groupAnalytics'])->name('lecturer.group.analytics');
        Route::get('/quizzes', [LecturerController::class, 'quizzes'])->name('lecturer.quizzes');
        Route::get('/quiz/create', [LecturerController::class, 'createQuiz'])->name('lecturer.quiz.create');
        Route::post('/quiz/store', [LecturerController::class, 'storeQuiz'])->name('lecturer.quiz.store');
        Route::get('/quiz/{quiz}/results', [LecturerController::class, 'quizResults'])->name('lecturer.quiz.results');
        Route::get('/grading', [LecturerController::class, 'gradingMatrix'])->name('lecturer.grading');
    });

    // ---------- Lecturer Quiz Management ----------
    Route::get('/quiz/{quiz}/edit', [LecturerController::class, 'editQuiz'])->name('lecturer.quiz.edit');
    Route::post('/quiz/{quiz}/question', [LecturerController::class, 'storeQuestion'])->name('lecturer.quiz.question.store');
    Route::delete('/quiz/{quiz}/question/{question}', [LecturerController::class, 'deleteQuestion'])->name('lecturer.quiz.question.delete');
    Route::post('/quiz/{quiz}/toggle', [LecturerController::class, 'toggleQuizStatus'])->name('lecturer.quiz.toggle');
    });


// ==================== ROUTE PARAMETER PATTERNS ====================

Route::pattern('group', '[0-9]+');
Route::pattern('topic', '[0-9]+');
Route::pattern('post', '[0-9]+');
Route::pattern('id', '[0-9]+');