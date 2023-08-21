<?php

namespace Ymigval\LaravelIndexnow\Tests\Feature;

use Ymigval\LaravelIndexnow\IndexNow;
use Ymigval\LaravelIndexnow\Tests\TestCase;

class IndexNowTest extends TestCase
{

    public function test_get_key()
    {

        $instance = $this->app->make(IndexNow::class);

        //$this->assertEquals($instance->getKey(), 'fbdda258c45d4ebeb29242c702425cab');

        $this->assertTrue($instance->getKey());
    }
}
