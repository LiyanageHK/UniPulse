<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Weekly Summary Scheduler
|--------------------------------------------------------------------------
| Runs every Sunday at 11:00 PM to process all users' weekly journal
| entries and generate risk assessment summaries.
|
*/
Schedule::command('summaries:process-weekly')
    ->weeklyOn(0, '23:00')   // Sunday at 11 PM
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/weekly-summaries.log'));
