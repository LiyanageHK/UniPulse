<?php
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WeeklyCheckinController;
use App\Http\Controllers\ChatSupportController;
use App\Http\Controllers\CrisisManagementController;
use App\Http\Controllers\FeedbackController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\OnBoardingPoornimaController;
use App\Http\Controllers\DashboardPoornimaController;
use App\Http\Controllers\AuthController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');


Route::middleware(['auth', 'check.onboarding'])->group(function () {
   Route::get('/dashboard-poornima',[DashboardPoornimaController::class,'dashboard'])->name('dashboard-poornima');
    Route::get('/on-boarding',[OnBoardingPoornimaController::class,'onBoarding'])->name('on-boarding');
    Route::post('/on-boarding-store',[OnBoardingPoornimaController::class,'onBoardingStore'])->name('on-boarding-store');
    Route::get('/on-boarding-success',[OnBoardingPoornimaController::class,'onBoardingSuccess'])->name('on-boarding-success');
    Route::get('/survey',[DashboardPoornimaController::class, 'survey'])->name('survey');
    Route::post('/survey', [DashboardPoornimaController::class, 'surveyStore'])->name('survey-store');
    Route::get('/survey-success',[DashboardPoornimaController::class, 'surveySuccess'])->name('survey-success');

    Route::get('/weekly-checkings',[DashboardPoornimaController::class, 'weeklyCheckings'])->name('weekly-checkings');
    Route::get('/weekly-checkings-view/{id}',[DashboardPoornimaController::class, 'weeklyCheckingsView'])->name('weekly-checkings.view');


    Route::get('/profile/{id}', [DashboardPoornimaController::class, 'profileView'])->name('profile.view');
    Route::get('/my-connections', [DashboardPoornimaController::class, 'myConnections'])->name('myConnections');
    Route::get('/peer-matchings', [DashboardPoornimaController::class, 'peerMatchings'])->name('peer-matchings');
    Route::post('/peer/send/{id}', [DashboardPoornimaController::class, 'sendRequest'])->name('peer.send');
    Route::post('/peer/accept/{id}', [DashboardPoornimaController::class, 'acceptRequest'])->name('peer.accept');
    Route::post('/peer/reject/{id}', [DashboardPoornimaController::class, 'rejectRequest'])->name('peer.reject');
    Route::post('/peer/rating/{to_id}',[DashboardPoornimaController::class, 'peerRating'])->name('peer.rating');

    Route::get('/chat-view', [DashboardPoornimaController::class, 'chat'])->name('chat.view');
    Route::get('/chat/validate/{chatId}', [DashboardPoornimaController::class, 'validateChatAccess']);

    Route::get('/requests', [DashboardPoornimaController::class, 'viewRequests'])->name('requests.incoming');
    Route::post('/requests/{id}/accept', [DashboardPoornimaController::class, 'acceptRequest'])->name('requests.accept');
    Route::post('/requests/{id}/reject', [DashboardPoornimaController::class, 'rejectRequest'])->name('requests.reject');

    Route::get('/requests', [DashboardPoornimaController::class, 'viewRequests'])->name('requests.incoming');

    Route::get('/groups', [GroupController::class, 'index'])->name('groups.index');
    Route::get('/groups/discover', [GroupController::class, 'discover'])->name('groups.discover');
    Route::get('/groups/create', [GroupController::class, 'create'])->name('groups.create');
    Route::post('/groups', [GroupController::class, 'store'])->name('groups.store');
    Route::get('/groups/{id}', [GroupController::class, 'show'])->name('groups.show');
    Route::post('/groups/{id}/request', [GroupController::class, 'sendRequest'])->name('groups.sendRequest');
    Route::post('/groups/requests/{id}/accept', [GroupController::class, 'acceptRequest'])->name('groups.acceptRequest');
    Route::post('/groups/requests/{id}/reject', [GroupController::class, 'rejectRequest'])->name('groups.rejectRequest');
    Route::post('/groups/{id}/invite', [GroupController::class, 'inviteUser'])->name('groups.inviteUser');
    Route::post('/groups/{id}/leave', [GroupController::class, 'leave'])->name('groups.leave');
    Route::delete('/groups/{id}', [GroupController::class, 'destroy'])->name('groups.destroy');
    Route::delete('/groups/{groupId}/members/{userId}', [GroupController::class, 'removeMember'])->name('groups.removeMember');
    Route::get('/risk-level',[DashboardPoornimaController::class,'riskLevel'])->name('risk-level');
    Route::get('/suggestions',[DashboardPoornimaController::class,'suggestions'])->name('suggestions');


});

Route::get('/', function () {
    return view('home');
})->name('home');

// Public pages
Route::get('/terms-of-service', function () {
    return view('terms-of-service');
})->name('terms');

Route::get('/privacy-policy', function () {
    return view('privacy-policy');
})->name('privacy');

// Public About page
Route::view('/about', 'AboutUs')->name('about');

Route::get('/contact', function () {
    return view('contact');
})->name('contact');


// Public chat information page (accessible without login)
Route::get('/conversational-support', function () {
    return view('chat-info');
})->name('chat.info');

// Public chat information page (accessible without login)
Route::get('/profiling', function () {
    return view('profiling');
})->name('profiling');

// Public Peer Matching service page
Route::view('/services/peer-matching', 'ServicesPeerMatching')->name('services.peer-matching');

Route::get('/socialriskservice', function () {
    return view('socialriskservice');
})->name('socialriskservice');

/*Route::get('/profiling', [ServicePageController::class, 'studentProfiling'])
    ->name('services.studentProfiling');*/

// Public API for approved feedback (for home page)
Route::get('/api/feedback/approved', [FeedbackController::class, 'getApproved'])->name('feedback.approved');

// Public guest feedback submission (no login required)
Route::post('/api/feedback/guest', [FeedbackController::class, 'storeGuest'])->name('feedback.guest');



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
        Route::post('/conversation/{id}/unarchive', [ChatSupportController::class, 'unarchiveConversation'])->name('unarchive');
        Route::delete('/conversation/{id}', [ChatSupportController::class, 'deleteConversation'])->name('delete');

        // Bulk conversation operations
        Route::post('/conversations/archive-all', [ChatSupportController::class, 'archiveAllConversations'])->name('archive.all');
        Route::post('/conversations/unarchive-all', [ChatSupportController::class, 'unarchiveAllConversations'])->name('unarchive.all');
        Route::delete('/conversations/delete-active', [ChatSupportController::class, 'deleteAllActiveConversations'])->name('delete.active');
        Route::delete('/conversations/delete-archived', [ChatSupportController::class, 'deleteAllArchivedConversations'])->name('delete.archived');

        // Memory management endpoints
        Route::get('/memories', [ChatSupportController::class, 'getMemories'])->name('memories');
        Route::patch('/memory/{id}', [ChatSupportController::class, 'updateMemory'])->name('memory.update');
        Route::delete('/memory/{id}', [ChatSupportController::class, 'deleteMemory'])->name('memory.delete');
        Route::delete('/memories/clear', [ChatSupportController::class, 'clearAllMemories'])->name('memories.clear');

        // Counselors endpoint
        Route::get('/counselors', [ChatSupportController::class, 'getCounselors'])->name('counselors');
        Route::get('/counselors/{category}', [ChatSupportController::class, 'getCounselorsByCategory'])->name('counselors.category');
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
