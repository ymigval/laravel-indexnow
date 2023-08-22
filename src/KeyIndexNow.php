<?php

namespace Ymigval\LaravelIndexnow;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Ymigval\LaravelIndexnow\Exceptions\InvalidKeyException;
use Ymigval\LaravelIndexnow\Exceptions\KeyFileDoesNotExistException;

class KeyIndexNow
{

    /**
     * Path to the IndexNow key file.
     *
     * @var string
     */
    private static $pathKeyFile = __DIR__ . "/../storage/key.txt";

    /**
     * Get key IndexNow
     *
     * @return string
     * @throws KeyFileDoesNotExistException | InvalidKeyException
     */
    public static function getKey()
    {
        if (File::exists(static::$pathKeyFile) == false) {
            throw new KeyFileDoesNotExistException();
        }

        $key    = File::get(static::$pathKeyFile);
        $keylen = strlen($key);

        if ($keylen < 8 || $keylen > 128) {
            throw new InvalidKeyException();
        }

        return $key;
    }

    /**
     * Generate and create a new key file.
     *
     * @return string
     */
    public static function newkey(): string
    {
        $key = Str::of(Str::uuid())->replace("-", "");

        File::put(static::$pathKeyFile, $key);

        return $key;
    }
}
