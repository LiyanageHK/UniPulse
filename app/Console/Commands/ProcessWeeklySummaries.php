<?php

namespace App\Console\Commands;

use App\Services\WeeklySummaryService;
use Illuminate\Console\Command;

class ProcessWeeklySummaries extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'summaries:process-weekly';

    /**
     * The console command description.
     */
    protected $description = 'Process all users\' weekly journal entries and generate risk summaries';

    public function __construct(
        protected WeeklySummaryService $weeklySummaryService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Starting weekly summary processing...');

        $results = $this->weeklySummaryService->processAllUsers();

        $this->info("Processing complete:");
        $this->info("  Processed: {$results['processed']}");
        $this->info("  Skipped:   {$results['skipped']}");
        $this->info("  Failed:    {$results['failed']}");

        if ($results['failed'] > 0) {
            $this->warn("{$results['failed']} users failed — check logs for details.");
        }

        return self::SUCCESS;
    }
}
