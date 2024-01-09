<?php

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Ymigval\LaravelIndexnow\IndexNowApiKeyManager;

try {
    $key = IndexNowApiKeyManager::getApiKey();

    Route::any(Str::of('/')->append($key)->append('.txt'), function () use ($key) {
        return new Response($key, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
        ]);
    })->name('my_key_index_now');
} catch (Exception $e) {
}
