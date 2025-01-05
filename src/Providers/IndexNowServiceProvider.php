<?php

namespace Ymigval\LaravelIndexnow\Providers;

use Illuminate\Support\ServiceProvider;
use Ymigval\LaravelIndexnow\Console\ClearLogsCommand;
use Ymigval\LaravelIndexnow\Console\GenerateApiKeyCommand;
use Ymigval\LaravelIndexnow\Console\GetApiKeyCommand;
use Ymigval\LaravelIndexnow\Console\ShowLogsCommand;
use Ymigval\LaravelIndexnow\IndexNowService;

class IndexNowServiceProvider extends ServiceProvider
{
    // Configuration constants
    private const CONFIG_PATH = __DIR__ . '/../../config/indexnow.php';
    private const ROUTES_PATH = __DIR__ . '/../../routes/web.php';
    private const CONFIG_TAG = 'indexnow';

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfig(); // Merge configuration
        $this->bindIndexNowService(); // Bind singleton
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerCommands(); // Register console commands
        $this->loadResourceFiles(); // Load routes and publish configuration
    }

    /**
     * Merge the configuration file.
     */
    private function mergeConfig(): void
    {
        $this->mergeConfigFrom(self::CONFIG_PATH, self::CONFIG_TAG);
    }

    /**
     * Bind the IndexNowService as a singleton.
     */
    private function bindIndexNowService(): void
    {
        $this->app->singleton('IndexNow', function ($app) {
            $defaultSearchEngine = config('indexnow.searchengine', 'microsoft_bing');
            return new IndexNowService($defaultSearchEngine);
        });
    }

    /**
     * Register the console commands for the package.
     */
    private function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands($this->getCommandList());
        }
    }

    /**
     * Get the list of commands to register.
     *
     * @return array
     */
    private function getCommandList(): array
    {
        return [
            GenerateApiKeyCommand::class,
            GetApiKeyCommand::class,
            ShowLogsCommand::class,
            ClearLogsCommand::class,
        ];
    }

    /**
     * Load routes and publish configuration resources.
     */
    private function loadResourceFiles(): void
    {
        $this->loadRoutesFrom(self::ROUTES_PATH);
        $this->publishes([
            self::CONFIG_PATH => config_path('/indexnow.php'),
        ], self::CONFIG_TAG);
    }
}