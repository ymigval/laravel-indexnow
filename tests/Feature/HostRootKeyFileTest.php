<?php

namespace Ymigval\LaravelIndexnow\Tests\Feature;

use Ymigval\LaravelIndexnow\Tests\TestCase;

class HostRootKeyFileTest extends TestCase
{
    public function test_get_key_file()
    {
        $response = $this->get('/5750efebf7cf4fc5918b726d621c7820.txt');
        $response->assertOk();
        $response->assertContent('5750efebf7cf4fc5918b726d621c7820');
    }
}
