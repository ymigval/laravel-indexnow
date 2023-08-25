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

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.url', 'http://example.com/');
        $app['config']->set('indexnow.ignore_production_environment', true);
    }
}
