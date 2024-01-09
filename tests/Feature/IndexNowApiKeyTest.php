<?php

namespace Ymigval\LaravelIndexnow\Tests\Feature;

use Ymigval\LaravelIndexnow\Exceptions\InvalidKeyException;
use Ymigval\LaravelIndexnow\Exceptions\KeyFileDoesNotExistException;
use Ymigval\LaravelIndexnow\IndexNowApiKeyManager;
use Ymigval\LaravelIndexnow\Tests\TestCase;

class IndexNowApiKeyTest extends TestCase
{
    public function test_generate_new_key()
    {
        $this->assertNotEmpty(IndexNowApiKeyManager::generateNewApiKey());
    }

    public function test_get_key()
    {
        $this->assertNotEmpty(IndexNowApiKeyManager::getApiKey());
    }

    /**
     * @test A hypothetical scenario where the key file does not exist.
     */
    public function test_key_file_does_not_exist_exception()
    {

        $this->expectException(KeyFileDoesNotExistException::class);
        $this->expectExceptionMessage('The IndexNow key file does not exist. To create one, use the command: php artisan indexnow:generate-key');
        $this->expectExceptionCode(404);

        IndexNowApiKeyManager::getApiKey();
    }

    /**
     * @test A hypothetical scenario in which the key is not valid.
     */
    public function test_invalid_key_exception()
    {

        $this->expectException(InvalidKeyException::class);
        $this->expectExceptionMessage('Your IndexNow key is invalid. To create a new one, use the command: php artisan indexnow:generate-key');
        $this->expectExceptionCode(404);

        IndexNowApiKeyManager::getApiKey();
    }
}
