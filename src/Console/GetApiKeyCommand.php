<?php

namespace Ymigval\LaravelIndexnow\Console;

use Exception;
use Illuminate\Console\Command;
use Ymigval\LaravelIndexnow\IndexNowApiKeyManager;

class GetApiKeyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'indexnow:apikey';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieve my IndexNow API Key';

    /**
     * Success message for fetching the API key.
     *
     * @var string
     */
    private const SUCCESS_MESSAGE = 'API Key: %s';

    /**
     * Error message for exceptions.
     *
     * @var string
     */
    private const ERROR_MESSAGE = 'Failed to retrieve API key: %s';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $this->outputSuccessMessage(IndexNowApiKeyManager::fetchOrGenerate());
            return self::SUCCESS;
        } catch (Exception $e) {
            $this->outputErrorMessage($e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * Display a success message with the retrieved API key.
     *
     * @param string $apiKey
     */
    private function outputSuccessMessage(string $apiKey): void
    {
        $this->line(sprintf(self::SUCCESS_MESSAGE, $apiKey));
    }

    /**
     * Display an error message.
     *
     * @param string $error
     */
    private function outputErrorMessage(string $error): void
    {
        $this->error(sprintf(self::ERROR_MESSAGE, $error));
    }
}
