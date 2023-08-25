<?php

namespace Ymigval\LaravelIndexnow\Providers;

use Illuminate\Support\ServiceProvider;
use Ymigval\LaravelIndexnow\Console\ClearIndexNowLogsCommand;
use Ymigval\LaravelIndexnow\Console\GenerateIndexNowApiKeyCommand;
use Ymigval\LaravelIndexnow\Console\GetIndexNowApiKeyCommand;
use Ymigval\LaravelIndexnow\Console\ShowIndexNowLogsCommand;
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
            __DIR__ . '/../../config/indexnow.php', 'indexnow'
        );

        $this->app->singleton('IndexNow', function ($app) {
            return new IndexNowService(config('indexnow.searchengine', 'microsoft_bing'));
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
                GetIndexNowApiKeyCommand::class,
                ShowIndexNowLogsCommand::class,
                ClearIndexNowLogsCommand::class,
            ]);
        }

        // Routes
        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');

        $this->publishes([
            __DIR__ . '/../../config/indexnow.php' => config_path('/indexnow.php'),
        ], 'indexnow');
    }
}
