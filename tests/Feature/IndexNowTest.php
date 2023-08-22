<?php

namespace Ymigval\LaravelIndexnow\Tests\Feature;

use Ymigval\LaravelIndexnow\Exceptions\InvalidKeyException;
use Ymigval\LaravelIndexnow\Exceptions\KeyFileDoesNotExistException;
use Ymigval\LaravelIndexnow\IndexNow;
use Ymigval\LaravelIndexnow\KeyIndexNow;
use Ymigval\LaravelIndexnow\Tests\TestCase;

class IndexNowTest extends TestCase
{

    public function test_process()
    {
        $indexNow = $this->app->make(IndexNow::class);

        $indexNow->url();

        $this->assertTrue(true);
    }
}
