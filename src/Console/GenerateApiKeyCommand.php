<?php

namespace Ymigval\LaravelIndexnow\Console;

use Illuminate\Console\Command;
use Ymigval\LaravelIndexnow\IndexNowApiKeyManager;

class GenerateApiKeyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'indexnow:generate-apikey';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new IndexNow API key.';

    /**
     * Success message for API key generation.
     *
     * @var string
     */
    private const SUCCESS_MESSAGE = 'New IndexNow API key generated and saved successfully!';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $apiKey = IndexNowApiKeyManager::generateApiKey();

        $this->info(self::SUCCESS_MESSAGE);
        $this->newLine();
        $this->line(sprintf('API Key: %s', $apiKey));

        return self::SUCCESS;
    }
}
