<?php

namespace Ymigval\LaravelIndexnow;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Ymigval\LaravelIndexnow\Exceptions\InvalidKeyException;
use Ymigval\LaravelIndexnow\Exceptions\KeyFileDoesNotExistException;

class IndexNowApiKeyManager
{

    /**
     * Path to the IndexNow API key file.
     *
     * @var string
     */
    private static $apiKeyFilePath = __DIR__ . "/../storage/indexnow_api_key.txt";

    /**
     * Get the IndexNow API key.
     *
     * @return string
     * @throws KeyFileDoesNotExistException | InvalidKeyException
     */
    public static function getApiKey()
    {
        if (File::exists(static::$apiKeyFilePath) == false) {
            throw new KeyFileDoesNotExistException();
        }

        $apiKey = File::get(static::$apiKeyFilePath);
        $apiKeyLength = strlen($apiKey);

        if ($apiKeyLength < 8 || $apiKeyLength > 128) {
            throw new InvalidKeyException();
        }

        return $apiKey;
    }

    /**
     * Generate and create a new IndexNow API key file.
     *
     * @return string
     */
    public static function generateNewApiKey(): string
    {
        $apiKey = Str::of(Str::uuid())->replace("-", "");

        File::put(static::$apiKeyFilePath, $apiKey);

        return $apiKey;
    }
}