<?php

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Ymigval\LaravelIndexnow\IndexNowApiKeyManager;
use Ymigval\LaravelIndexnow\LogManager;


/**
 * Content-Type constant for UTF-8 plain text responses.
 */
const TEXT_PLAIN_UTF8 = 'text/plain; charset=utf-8';


try {
    $apiKey = IndexNowApiKeyManager::fetchOrGenerate();
    registerKeyRoute($apiKey);
} catch (Exception $e) {
    LogManager::addMessage('Error registering IndexNow route: ' . $e->getMessage());
}

/**
 * Registers the IndexNow API key route.
 *
 * @param string $apiKey
 */
function registerKeyRoute(string $apiKey): void
{
    Route::any(generateKeyRoutePath($apiKey), function () use ($apiKey) {
        return new Response(
            $apiKey,
            ResponseAlias::HTTP_OK,
            ['Content-Type' => TEXT_PLAIN_UTF8]
        );
    })->name('my_key_index_now');
}

/**
 * Generates the path for the IndexNow API key route.
 *
 * @param string $apiKey
 * @return string
 */
function generateKeyRoutePath(string $apiKey): string
{
    return Str::of('/')->append($apiKey)->append('.txt')->toString();
}

