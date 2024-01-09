<?php

namespace Ymigval\LaravelIndexnow\Tests\Feature;

use Illuminate\Support\Facades\Http;
use Ymigval\LaravelIndexnow\PreventSpan;
use Ymigval\LaravelIndexnow\Tests\TestCase;

class PreventSpanTest extends TestCase
{
    /**
     * @test
     */
    public function test_detect_possible_span()
    {
        Http::fake([
            'api-endpoint' => Http::response([], 429),
        ]);

        $response = Http::get('/api-endpoint');

        PreventSpan::detectPotentialSpam($response);

        $this->assertFileExists('storage/temporary_blocking.txt');
    }

    /**
     * @test
     */
    public function test_detect_possible_span_v2()
    {
        Http::fake([
            'api-endpoint' => Http::response([], 200),
        ]);

        $response = Http::get('/api-endpoint');
        PreventSpan::detectPotentialSpam($response);

        $this->assertFileDoesNotExist('storage/temporary_blocking.txt');
    }

    /**
     * @test
     */
    public function test_allow()
    {
        $status = PreventSpan::isAllowed();
        $this->assertTrue($status);
    }

    /**
     * @test
     */
    public function test_allow_v2()
    {
        $status = PreventSpan::isAllowed();
        $this->assertNotTrue($status);
    }
}
