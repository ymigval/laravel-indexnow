<?php

namespace Ymigval\LaravelIndexnow\Console;

use Exception;
use Illuminate\Console\Command;
use Ymigval\LaravelIndexnow\IndexNowApiKeyManager;

class GetIndexNowApiKeyCommand extends Command
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
     */
    public function handle(): int
    {
        try {
            $key = IndexNowApiKeyManager::getApiKey();

            $this->line('API Key: '.$key);

            return self::SUCCESS;
        } catch (Exception $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
