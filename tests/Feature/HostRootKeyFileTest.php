<?php

namespace Ymigval\LaravelIndexnow\Tests\Feature;

use Ymigval\LaravelIndexnow\Tests\TestCase;

class HostRootKeyFileTest extends TestCase
{

    /**
     * @test In a hypothetical scenario where the key file exists.
     */
    public function test_get_key_file()
    {
        $response = $this->get('/8beab1b2db094033962e3adb13ab3989.txt');
        $response->assertOk();
        $response->assertContent('8beab1b2db094033962e3adb13ab3989');
    }
}
