<?php

namespace Ymigval\LaravelIndexnow\Tests\Feature;

use Ymigval\LaravelIndexnow\Exceptions\InvalidKeyException;
use Ymigval\LaravelIndexnow\Exceptions\KeyFileDoesNotExistException;
use Ymigval\LaravelIndexnow\Exceptions\UnknownDriverException;
use Ymigval\LaravelIndexnow\IndexNow;
use Ymigval\LaravelIndexnow\KeyIndexNow;
use Ymigval\LaravelIndexnow\Tests\TestCase;

class IndexNowTest extends TestCase
{

    public function test_submit()
    {
        $indexNow = $this->app->make(IndexNow::class);

        $indexNow->submit(['/test', '/cat']);

        $this->assertTrue(true);
    }

    /**
     * @test A hypothetical case where an undefined driver is provided in the configuration
     */
    public function test_unknown_driver_exception()
    {
        $this->expectException(UnknownDriverException::class);
        $this->expectExceptionMessage("Unknown IndexNow driver.");
        $this->expectExceptionCode(404);

        $indexNow = $this->app->make(IndexNow::class);
        $indexNow->setDriver('unknown_driver');
    }
}
