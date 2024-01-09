<?php

namespace Ymigval\LaravelIndexnow\Tests\Feature;

use Ymigval\LaravelIndexnow\IndexNowApiKeyManager;
use Ymigval\LaravelIndexnow\Tests\TestCase;

class IndexNowCommandTest extends TestCase
{
    public function test_generate_new_api_key()
    {
        $this->artisan('indexnow:generate-key')
            ->expectsOutput('New IndexNow API key generated and saved successfully!')
            ->assertExitCode(0);
    }

    public function test_retrieve_my_index_now_api_key()
    {
        $this->artisan('indexnow:apikey')
            ->expectsOutput('API Key: '.IndexNowApiKeyManager::getApiKey())
            ->assertExitCode(0);
    }

    public function test_display_index_now_logs()
    {
        $this->artisan('indexnow:logs')
            ->assertExitCode(0);
    }

    public function test_clear_index_now_logs()
    {
        $this->artisan('indexnow:clear-logs')
            ->expectsOutput('IndexNow logs have been successfully cleared.')
            ->assertExitCode(0);
    }
}
