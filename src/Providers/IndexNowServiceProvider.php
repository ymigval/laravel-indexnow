<?php

namespace Ymigval\LaravelIndexnow\Providers;

use Illuminate\Support\ServiceProvider;
use Ymigval\LaravelIndexnow\Console\KeyGenerateCommand;
use Ymigval\LaravelIndexnow\IndexNow;

class IndexNowServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Get and merge configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/laravel-indexnow.php', 'laravel-indexnow'
        );

        $this->app->singleton(IndexNow::class, function ($app) {
            return new IndexNow(config('laravel-indexnow.driver', 'microsoft_bing'));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                KeyGenerateCommand::class,
            ]);
        }

        // Routes
        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');
    }
}
