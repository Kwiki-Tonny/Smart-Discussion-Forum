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

// 3. Password Reset (Stubbed)
Route::get('/password/reset', [AuthController::class, 'showResetForm'])->name('password.request');
Route::post('/password/reset', [AuthController::class, 'resetPassword'])->name('password.update');


// ==================== PROTECTED ROUTES ====================

Route::middleware(['auth'])->group(function () {

    // ---------- Logout ----------
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


    // ==================== STUDENT ROUTES ====================

    // ---------- Dashboard ----------
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
    Route::get('/topics/{topic}/export', [StudentController::class, 'exportPdf'])->name('topics.export');

    // ---------- Posts ----------
    Route::post('/posts', [StudentController::class, 'storePost'])->name('posts.store');
    Route::post('/posts/{post}/like', [StudentController::class, 'toggleLike'])->name('posts.like');
    Route::post('/posts/reply', [StudentController::class, 'storeReply'])->name('posts.reply');

    // ---------- Student Quizzes ----------
    Route::get('/quizzes', [StudentController::class, 'quizIndex'])->name('student.quizzes');
    Route::get('/quizzes/{quiz}/take', [StudentController::class, 'takeQuiz'])->name('student.quiz.take');
    Route::post('/quizzes/{quiz}/submit', [StudentController::class, 'submitQuiz'])->name('student.quiz.submit');

    // ---------- Performance Report ----------
    Route::get('/quiz/{id}/performance-report', [StudentController::class, 'performanceReport'])->name('quiz.report');

    // ---------- Recommendations ----------
    Route::get('/recommendations', [StudentController::class, 'recommendations'])->name('recommendations.index');

    // ---------- Affinity Cache ----------
    Route::post('/affinity/clear', [StudentController::class, 'clearAffinityCache'])->name('affinity.clear');


    // ==================== LECTURER ROUTES ====================

    Route::middleware(['role:lecturer,admin'])->prefix('lecturer')->group(function () {

        // ---------- Lecturer Dashboard ----------
        Route::get('/dashboard', [LecturerController::class, 'index'])->name('lecturer.dashboard');
        Route::get('/profile', [LecturerController::class, 'profile'])->name('lecturer.profile');

        // ---------- Group Analytics ----------
        Route::get('/group/{group}/analytics', [LecturerController::class, 'groupAnalytics'])->name('lecturer.group.analytics');

        // ---------- Quiz Management ----------
        Route::get('/quizzes', [LecturerController::class, 'quizzes'])->name('lecturer.quizzes');
        Route::get('/quiz/create', [LecturerController::class, 'createQuiz'])->name('lecturer.quiz.create');
        Route::post('/quiz/store', [LecturerController::class, 'storeQuiz'])->name('lecturer.quiz.store');
        Route::get('/quiz/{quiz}/results', [LecturerController::class, 'quizResults'])->name('lecturer.quiz.results');

        // ---------- Quiz Edit & Questions ----------
        Route::get('/quiz/{quiz}/edit', [LecturerController::class, 'editQuiz'])->name('lecturer.quiz.edit');
        Route::post('/quiz/{quiz}/question', [LecturerController::class, 'storeQuestion'])->name('lecturer.quiz.question.store');
        Route::post('/quiz/{quiz}/questions/bulk', [LecturerController::class, 'storeBulkQuestions'])->name('lecturer.quiz.question.store.bulk');
        Route::delete('/quiz/{quiz}/question/{question}', [LecturerController::class, 'deleteQuestion'])->name('lecturer.quiz.question.delete');
        Route::post('/quiz/{quiz}/toggle', [LecturerController::class, 'toggleQuizStatus'])->name('lecturer.quiz.toggle');

        // ---------- Grading Matrix ----------
        Route::get('/grading', [LecturerController::class, 'gradingMatrix'])->name('lecturer.grading');
    });


    // ==================== POLLING ROUTES ====================

    // Stateless polling route - no session blocking!
    Route::get('/poll/topic/{topic}', [PollController::class, 'poll'])->name('topics.poll');
});


// ==================== ROUTE PARAMETER PATTERNS ====================

Route::pattern('group', '[0-9]+');
Route::pattern('topic', '[0-9]+');
Route::pattern('post', '[0-9]+');
Route::pattern('quiz', '[0-9]+');
Route::pattern('question', '[0-9]+');
Route::pattern('id', '[0-9]+');