<?php

namespace Ymigval\LaravelIndexnow\Providers;

use Illuminate\Support\ServiceProvider;
use Ymigval\LaravelIndexnow\IndexNow;

class IndexNowServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(IndexNow::class, fn($app) => new IndexNow());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

    }
}
