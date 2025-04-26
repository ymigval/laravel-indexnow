<?php

namespace Ymigval\LaravelIndexnow;

use Illuminate\Support\Facades\Config;
use Ymigval\LaravelIndexnow\Exceptions\InvalidKeyException;

class IndexNowApiKeyManager
{
    /**
     * Minimum and maximum lengths for valid API keys.
     */
    private const MIN_KEY_LENGTH = 8;
    private const MAX_KEY_LENGTH = 128;

    /**
     * Retrieves the API key from the configuration.
     *
     * First attempts to fetch the key from the configuration settings and validates it.
     * If the key is invalid, logs a message and rethrows the exception.
     *
     * @return string Returns the valid API key.
     * @throws InvalidKeyException Throws an exception if the retrieved key is invalid.
     */
    public static function getKey(): string
    {
        // First try to get the key from config
        $configKey = Config::get('indexnow.indexnow_api_key');

        try {
            self::validateKey($configKey);
            return $configKey;
        } catch (InvalidKeyException $exception) {
            LogManager::addMessage('Invalid API key in configuration. Attempting to fetch from file or generate new one.');

            throw $exception;
        }
    }


    /**
     * Validate the provided API key.
     *
     * @param string|null $key The API key to validate
     * @throws InvalidKeyException If the key is invalid
     */
    private static function validateKey(?string $key): void
    {
        $key = trim($key);
        $keyLength = strlen($key);

        if ($keyLength < self::MIN_KEY_LENGTH || $keyLength > self::MAX_KEY_LENGTH) {
            throw new InvalidKeyException();
        }

        // Check if key contains only alphanumeric characters
        if (!ctype_alnum($key)) {
            throw new InvalidKeyException();
        }
    }
}