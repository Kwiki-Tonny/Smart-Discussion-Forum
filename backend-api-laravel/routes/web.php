<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Sprint 1 Evolution Node
|--------------------------------------------------------------------------
*/

// 1. Root redirect (Sends unauthenticated users straight to login)
Route::redirect('/', '/groups')->name('root.redirect');

// 2. Login Screen Entry Point
Route::view('/login', 'auth.login')->name('login');

// 3. Group Collection Screen (The Core Dashboard View)
Route::view('/groups', 'groups.index')->name('groups.index');

// 4. Group Guidelines Page (The Onboarding Gate for a specific group context)
Route::view('/groups/engineering/guidelines', 'onboarding.guidelines')->name('groups.guidelines');

// 5. Mock Login Form Submission (Simulates authentication success)
Route::post('/login', function () {
    // In a real application, auth validation logic goes here.
    // For Sprint 1, we redirect straight to the Group Collection Screen.
    return redirect()->route('groups.index');
})->name('login.submit');