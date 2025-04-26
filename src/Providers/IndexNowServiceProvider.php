<?php

namespace Ymigval\LaravelIndexnow\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Ymigval\LaravelIndexnow\Console\CheckStatusCommand;
use Ymigval\LaravelIndexnow\Console\ClearLogsCommand;
use Ymigval\LaravelIndexnow\Console\GetApiKeyCommand;
use Ymigval\LaravelIndexnow\Console\ResetBlockCommand;
use Ymigval\LaravelIndexnow\Console\ShowLogsCommand;
use Ymigval\LaravelIndexnow\Controllers\IndexNowKeyController;
use Ymigval\LaravelIndexnow\IndexNowApiKeyManager;
use Ymigval\LaravelIndexnow\IndexNowService;
use Ymigval\LaravelIndexnow\LogManager;

class IndexNowServiceProvider extends ServiceProvider
{
    // Configuration constants
    private const CONFIG_PATH = __DIR__ . '/../../config/indexnow.php';
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
        $this->registerIndexNowRoute(); // Register route
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
            $defaultSearchEngine = config('indexnow.search_engine', 'indexnow');
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
            CheckStatusCommand::class,
            GetApiKeyCommand::class,
            ShowLogsCommand::class,
            ClearLogsCommand::class,
            ResetBlockCommand::class,
        ];
    }

    /**
     * Load routes and publish configuration resources.
     */
    private function loadResourceFiles(): void
    {
        $this->publishes([
            self::CONFIG_PATH => config_path('/indexnow.php'),
        ], self::CONFIG_TAG);
    }

    /**
     * Register the IndexNow key verification route.
     */
    protected function registerIndexNowRoute(): void
    {
        try {
            // Get the API key from configuration
            $apiKey = IndexNowApiKeyManager::getKey();

            // Register the route with the API key
            $path = Str::of('/')->append($apiKey)->append('.txt')->toString();

            Route::any($path, [IndexNowKeyController::class, 'show'])
                ->name('indexnow_key_verification');

            // Store API key in config so it can be accessed by the controller
            config(['indexnow.current_api_key' => $apiKey]);

        } catch (\Exception $e) {
            LogManager::addMessage(
                'Error registering IndexNow route: ' . $e->getMessage()
            );
        }
    }
}