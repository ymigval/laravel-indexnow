<?php

namespace Ymigval\LaravelIndexnow\Tests\Unit;

use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;
use Ymigval\LaravelIndexnow\Exceptions\InvalidKeyException;
use Ymigval\LaravelIndexnow\IndexNowApiKeyManager;
use Ymigval\LaravelIndexnow\Tests\TestCase;

class IndexNowApiKeyManagerTest extends TestCase
{
    #[Test] public function it_returns_valid_key_from_configuration()
    {
        // Arrange
        $validKey = 'abc123456789def';
        Config::set('indexnow.indexnow_api_key', $validKey);

        // Act
        $result = IndexNowApiKeyManager::getKey();

        // Assert
        $this->assertEquals($validKey, $result);
    }

    #[Test] public function it_throws_exception_for_empty_key()
    {
        // Arrange
        Config::set('indexnow.indexnow_api_key', '');

        // Assert
        $this->expectException(InvalidKeyException::class);

        // Act
        IndexNowApiKeyManager::getKey();
    }

    #[Test] public function it_throws_exception_for_key_with_invalid_characters()
    {
        // Arrange
        Config::set('indexnow.indexnow_api_key', 'invalid_key!@#');

        // Assert
        $this->expectException(InvalidKeyException::class);

        // Act
        IndexNowApiKeyManager::getKey();
    }

    #[Test] public function it_throws_exception_for_key_with_invalid_length()
    {
        // Arrange - key is too short
        Config::set('indexnow.indexnow_api_key', 'abc');

        // Assert
        $this->expectException(InvalidKeyException::class);

        // Act
        IndexNowApiKeyManager::getKey();

        // Arrange - key is too long (more than 128 characters)
        $longKey = str_repeat('a', 129);
        Config::set('indexnow.indexnow_api_key', $longKey);

        // Assert
        $this->expectException(InvalidKeyException::class);

        // Act
        IndexNowApiKeyManager::getKey();
    }
}
