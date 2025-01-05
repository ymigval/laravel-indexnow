<?php

namespace Ymigval\LaravelIndexnow;

use Exception;
use Illuminate\Support\Facades\File;
use Ymigval\LaravelIndexnow\Exceptions\InvalidKeyException;
use Ymigval\LaravelIndexnow\Exceptions\KeyFileDoesNotExistException;
use Ymigval\LaravelIndexnow\Exceptions\MixedException;

class IndexNowApiKeyManager
{
    /**
     * Path to the IndexNow API key file.
     */
    private const API_KEY_FILE_PATH = __DIR__ . '/../storage/indexnow_api_key.txt';

    /**
     * Minimum and maximum lengths for valid API keys.
     */
    private const MIN_KEY_LENGTH = 8;
    private const MAX_KEY_LENGTH = 128;

    /**
     * Fetches the API key from the specified file if it exists and is valid.
     * If the file does not exist or contains an invalid key, a new API key is generated.
     *
     * @return string Returns a valid API key, either fetched or newly generated.
     */
    public static function fetchOrGenerate(): string
    {
        try {
            if (!File::exists(self::API_KEY_FILE_PATH)) {
                throw new KeyFileDoesNotExistException();
            }

            $apiKey = File::get(self::API_KEY_FILE_PATH);
            self::validateKey($apiKey);

            return $apiKey;
        } catch (KeyFileDoesNotExistException|InvalidKeyException) {
            // If there's no valid API key, generate a new one.

            LogManager::addMessage('A new API Key has been created');

            return self::generateApiKey();
        }
    }

    /**
     * Generate and save a new IndexNow API key file.
     */
    public static function generateApiKey(): string
    {
        try {
            $randomBytes = random_bytes(16);
        } catch (Exception) {
            new MixedException('Unable to generate API Key');
        }

        $apiKey = bin2hex($randomBytes);
        File::put(self::API_KEY_FILE_PATH, $apiKey);

        return $apiKey;
    }

    /**
     * Validate the provided API key.
     *
     * @throws InvalidKeyException
     */
    private static function validateKey(string $key): void
    {
        $keyLength = strlen($key);

        if ($keyLength < self::MIN_KEY_LENGTH || $keyLength > self::MAX_KEY_LENGTH) {
            throw new InvalidKeyException();
        }
    }
}
