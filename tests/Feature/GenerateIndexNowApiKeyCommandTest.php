<?php

namespace Ymigval\LaravelIndexnow\Tests\Feature;

use Ymigval\LaravelIndexnow\Tests\TestCase;

class GenerateIndexNowApiKeyCommandTest extends TestCase
{

    public function test_generate_new_api_key()
    {
        $this->artisan('indexnow:generate-key')
            ->expectsOutput('New IndexNow API key generated and saved successfully!')
            ->assertExitCode(0);
    }
}
