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
        $this->app->singleton(IndexNow::class, fn($app) => new IndexNow());
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
