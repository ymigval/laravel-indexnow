<?php

namespace Ymigval\LaravelIndexnow\Tests\Feature;

use Ymigval\LaravelIndexnow\Exceptions\InvalidKeyException;
use Ymigval\LaravelIndexnow\Exceptions\KeyFileDoesNotExistException;
use Ymigval\LaravelIndexnow\KeyIndexNow;
use Ymigval\LaravelIndexnow\Tests\TestCase;

class KeyIndexNowTest extends TestCase
{

    public function test_new_key()
    {
        $this->assertNotEmpty(KeyIndexNow::newkey());
    }

    public function test_get_key()
    {
        $this->assertNotEmpty(KeyIndexNow::getKey());
    }

    /**
     * @test A hypothetical scenario where the key file does not exist.
     */
    public function test_key_file_does_not_exist_exception()
    {

        $this->expectException(KeyFileDoesNotExistException::class);
        $this->expectExceptionMessage("The IndexNow key file doesn't exist. To create one, use the command: php artisan indexnow:newkey");
        $this->expectExceptionCode(404);

        KeyIndexNow::getKey();
    }

    /**
     * @test A hypothetical scenario in which the key is not valid.
     */
    public function test_invalid_key_exception()
    {

        $this->expectException(InvalidKeyException::class);
        $this->expectExceptionMessage("Your IndexNow key is not valid. To create a new one, use the command: php artisan indexnow:newkey");
        $this->expectExceptionCode(404);

        KeyIndexNow::getKey();
    }
}
