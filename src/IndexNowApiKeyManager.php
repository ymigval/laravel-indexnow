<?php

namespace Ymigval\LaravelIndexnow;

use Exception;
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
    private static $apiKeyFilePath = __DIR__.'/../storage/indexnow_api_key.txt';

    /**
     * Get the IndexNow API key.
     *
     * @return string
     */
    public static function getApiKey()
    {
        try {
            if (File::exists(static::$apiKeyFilePath) == false) {
                throw new KeyFileDoesNotExistException();
            }

            $apiKey = File::get(static::$apiKeyFilePath);
            $apiKeyLength = strlen($apiKey);

            if ($apiKeyLength < 8 || $apiKeyLength > 128) {
                throw new InvalidKeyException();
            }

            return $apiKey;
        } catch (Exception $e) {
            // If you don't have an API Key, generate a new one.
            return IndexNowApiKeyManager::generateNewApiKey();
        }
    }

    /**
     * Generate and create a new IndexNow API key file.
     */
    public static function generateNewApiKey(): string
    {
        $apiKey = Str::uuid()
            ->getHex()
            ->toString();

        File::put(static::$apiKeyFilePath, $apiKey);

        return $apiKey;
    }
}
