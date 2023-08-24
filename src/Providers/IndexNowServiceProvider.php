<?php

namespace Ymigval\LaravelIndexnow\Providers;

use Illuminate\Support\ServiceProvider;
use Ymigval\LaravelIndexnow\Console\GenerateIndexNowApiKeyCommand;
use Ymigval\LaravelIndexnow\IndexNowService;

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

        $this->app->singleton(IndexNowService::class, function ($app) {
            return new IndexNowService(config('laravel-indexnow.searchengine', 'microsoft_bing'));
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
                GenerateIndexNowApiKeyCommand::class,
            ]);
        }

        // Routes
        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');
    }
}
