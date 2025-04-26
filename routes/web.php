<?php

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Ymigval\LaravelIndexnow\IndexNowApiKeyManager;
use Ymigval\LaravelIndexnow\LogManager;

if (!function_exists('registerIndexNowKeyRoute')) {
    /**
     * Generates the path for the IndexNow API key route.
     *
     * @param string $apiKey
     * @return string
     */
    function generateIndexNowKeyRoutePath(string $apiKey): string
    {
        return Str::of('/')->append($apiKey)->append('.txt')->toString();
    }

    /**
     * Registers the IndexNow API key route.
     *
     * @param string $apiKey
     */
    function registerIndexNowKeyRoute(string $apiKey): void
    {
        Route::any(generateIndexNowKeyRoutePath($apiKey), function () use ($apiKey) {
            return new Response($apiKey, ResponseAlias::HTTP_OK, [
                'Content-Type' => 'text/plain; charset=utf-8',
            ]);
        })->name('indexnow_key_verification');
    }

    try {
        // Use the new getKey method instead of fetchOrGenerate
        $apiKey = IndexNowApiKeyManager::getKey();
        registerIndexNowKeyRoute($apiKey);
    } catch (Exception $e) {
        LogManager::addMessage(
            'Error registering IndexNow route: ' . $e->getMessage()
        );
    }
}