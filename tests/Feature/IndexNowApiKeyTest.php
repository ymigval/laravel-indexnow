<?php

namespace Ymigval\LaravelIndexnow\Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Ymigval\LaravelIndexnow\Exceptions\InvalidKeyException;
use Ymigval\LaravelIndexnow\Exceptions\KeyFileDoesNotExistException;
use Ymigval\LaravelIndexnow\IndexNowApiKeyManager;
use Ymigval\LaravelIndexnow\Tests\TestCase;

class IndexNowApiKeyTest extends TestCase
{
    #[Test]
    public function test_generate_new_key()
    {
        $this->assertNotEmpty(IndexNowApiKeyManager::generateApiKey());
    }

    #[Test]
    public function test_get_key()
    {
        $this->assertNotEmpty(IndexNowApiKeyManager::fetchOrGenerate());
    }

    #[Test]
    public function test_key_file_does_not_exist_exception()
    {

        $this->expectException(KeyFileDoesNotExistException::class);
        $this->expectExceptionMessage('The IndexNow key file does not exist. To create one, use the command: php artisan indexnow:create-apikey');
        $this->expectExceptionCode(0);

        IndexNowApiKeyManager::fetchOrGenerate();
    }

    #[Test]
    public function test_invalid_key_exception()
    {

        $this->expectException(InvalidKeyException::class);
        $this->expectExceptionMessage('Your IndexNow key is invalid. To create a new one, use the command: php artisan indexnow:generate-apikey');
        $this->expectExceptionCode(0);

        IndexNowApiKeyManager::fetchOrGenerate();
    }
}
