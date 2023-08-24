<?php

namespace Ymigval\LaravelIndexnow\Console;

use Illuminate\Console\Command;
use Ymigval\LaravelIndexnow\IndexNowApiKeyManager;

class GenerateIndexNowApiKeyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "indexnow:generate-key";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Generate a new IndexNow API key.";

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $apiKey = IndexNowApiKeyManager::generateNewApiKey();

        $this->info('New IndexNow API key generated and saved successfully!');
        $this->newLine();
        $this->line('API Key: ' . $apiKey);

        return self::SUCCESS;
    }
}