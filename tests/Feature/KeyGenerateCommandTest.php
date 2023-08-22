<?php

namespace Ymigval\LaravelIndexnow\Tests\Feature;

use Ymigval\LaravelIndexnow\Tests\TestCase;

class KeyGenerateCommandTest extends TestCase
{

    public function test_generate_new_key()
    {
        $this->artisan('indexnow:newkey')
            ->expectsOutputToContain('Key file generated and created successfully!')
            ->assertSuccessful();
    }
}
