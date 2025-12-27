<?php
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WeeklyCheckinController;
use App\Http\Controllers\ChatSupportController;
use App\Http\Controllers\CrisisManagementController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('home');
});

// Public chat information page (accessible without login)
Route::get('/conversational-support', function () {
    return view('chat-info');
})->name('chat.info');

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

    // Chat Support Routes (Authentication Required)
    Route::prefix('chat')->name('chat.')->group(function () {
        // Chat interface page
        Route::get('/support', [ChatSupportController::class, 'index'])->name('support');
        
        // API endpoints
        Route::post('/conversation/start', [ChatSupportController::class, 'startConversation'])->name('start');
        Route::post('/message', [ChatSupportController::class, 'sendMessage'])->name('send');
        Route::get('/conversation/{id}', [ChatSupportController::class, 'getConversation'])->name('conversation');
        Route::get('/conversations', [ChatSupportController::class, 'listConversations'])->name('list');
        Route::patch('/conversation/{id}/rename', [ChatSupportController::class, 'renameConversation'])->name('rename');
        Route::post('/conversation/{id}/archive', [ChatSupportController::class, 'archiveConversation'])->name('archive');
        Route::delete('/conversation/{id}', [ChatSupportController::class, 'deleteConversation'])->name('delete');
        
        // Memory management endpoints
        Route::get('/memories', [ChatSupportController::class, 'getMemories'])->name('memories');
        Route::patch('/memory/{id}', [ChatSupportController::class, 'updateMemory'])->name('memory.update');
        Route::delete('/memory/{id}', [ChatSupportController::class, 'deleteMemory'])->name('memory.delete');
        Route::delete('/memories/clear', [ChatSupportController::class, 'clearAllMemories'])->name('memories.clear');
    });
    
    // Crisis Management Routes (Admin/Counselor Only)
    // TODO: Add admin middleware when role system is implemented
    Route::prefix('crisis')->name('crisis.')->group(function () {
        Route::get('/alerts', [CrisisManagementController::class, 'listCrisisAlerts'])->name('alerts');
        Route::get('/alerts/critical', [CrisisManagementController::class, 'getCriticalAlerts'])->name('alerts.critical');
        Route::post('/alert/{id}/acknowledge', [CrisisManagementController::class, 'acknowledgeAlert'])->name('acknowledge');
        Route::post('/alert/{id}/resolve', [CrisisManagementController::class, 'resolveAlert'])->name('resolve');
        Route::get('/conversation/{id}/flags', [CrisisManagementController::class, 'viewConversationFlags'])->name('flags');
        Route::post('/flag/{id}/review', [CrisisManagementController::class, 'reviewFlag'])->name('flag.review');
        Route::get('/dashboard/stats', [CrisisManagementController::class, 'getDashboardStats'])->name('dashboard.stats');
    });
});

require __DIR__.'/auth.php';