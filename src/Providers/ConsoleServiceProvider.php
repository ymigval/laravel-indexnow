<?php

namespace Ymigval\LaravelIndexnow\Providers;

use Illuminate\Support\ServiceProvider;
use Ymigval\LaravelIndexnow\Console\KeyGenerateCommand;

class ConsoleServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                KeyGenerateCommand::class,
            ]);
        }
    }
}
