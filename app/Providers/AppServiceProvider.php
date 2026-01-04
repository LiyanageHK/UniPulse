<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\WeeklyChecking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Chat;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
         View::composer('*', function ($view) {
            $hasSubmittedWeeklyCheck = false;
            $hasChats = false;

            if (Auth::check()) {
                $startOfWeek = Carbon::now()->startOfWeek();
                $endOfWeek = Carbon::now()->endOfWeek();

                $hasSubmittedWeeklyCheck = WeeklyChecking::where('user_id', Auth::id())
                    ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                    ->exists();

                $chatsCount = Chat::where('user1_id', Auth::id())->orWhere('user2_id', Auth::id())->count();

                if($chatsCount){
                    $hasChats = true;
                }
            }

            $view->with('hasSubmittedWeeklyCheck', $hasSubmittedWeeklyCheck)->with('hasChats',$hasChats);
        });
        // Register model observers
        \App\Models\WeeklyCheckin::observe(\App\Observers\WeeklyCheckinObserver::class);
    }
}
