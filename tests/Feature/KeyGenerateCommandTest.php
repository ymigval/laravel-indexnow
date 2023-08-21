<?php

namespace Ymigval\LaravelIndexnow\Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Ymigval\LaravelIndexnow\Tests\TestCase;

class KeyGenerateCommandTest extends TestCase
{

    public function test_generate_new_key()
    {
        $this->assertEquals(Artisan::call('indexnow:newkey'), 1);
    }
}
