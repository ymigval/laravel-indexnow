<?php

namespace Ymigval\LaravelIndexnow\Tests;

use Orchestra\Testbench\TestCase as TestCaseBase;
use Ymigval\LaravelIndexnow\Providers\IndexNowServiceProvider;

class TestCase extends TestCaseBase
{
    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array<int, class-string<\Illuminate\Support\ServiceProvider>>
     */
    protected function getPackageProviders($app)
    {
        return [
            IndexNowServiceProvider::class,
        ];
    }
}
