<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WeeklyCheckinController;
use App\Http\Controllers\ChatSupportController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('home');
});
Route::get('/chat-support', function () {
    return view('chat-support');
});

Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

    // Onboarding step 1
    Route::get('/onboarding/step1', [OnboardingController::class, 'step1'])
        ->name('onboarding.step1');

    Route::post('/onboarding/step1', [OnboardingController::class, 'storeStep1'])
        ->name('onboarding.step1.store');

    // Onboarding step 2
    Route::get('/onboarding/step2', [OnboardingController::class, 'step2'])
        ->name('onboarding.step2');

    Route::post('/onboarding/step2', [OnboardingController::class, 'storeStep2'])
        ->name('onboarding.step2.store');

    // Weekly Chek-in
    Route::get('/weekly-checkin', [WeeklyCheckinController::class,'showForm'])->name('weekly.checkin');
    Route::post('/weekly-checkin', [WeeklyCheckinController::class,'submitForm'])->name('weekly.checkin.submit');


    // profile routes 
    // Show profile (public route for viewing user's profile)
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    // Edit profile (separate path so /profile resolves to the show view)
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/password', [ProfileController::class, 'password'])->name('profile.password');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
   // Route::get('/chat-support', [\App\Http\Controllers\ChatSupportController::class, 'index'])->name('chat.support');
});

require __DIR__.'/auth.php';