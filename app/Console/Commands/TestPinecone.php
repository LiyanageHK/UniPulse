<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestPinecone extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-pinecone';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $ps = app(\App\Services\PineconeService::class);
        $res = $ps->embed('Hello world is tested!');
        if ($res) {
            $this->info("Success! Length of embedding: " . count($res));
        } else {
            $this->error("Failed to generate embedding");
        }
    }
}
