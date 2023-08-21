<?php

namespace Ymigval\LaravelIndexnow;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Ymigval\LaravelIndexnow\Exceptions\KeyFileDoesNotExistException;

class IndexNow
{

    /**
     * Path to the IndexNow key file.
     * @var string
     */
    private $pathKeyFile = __DIR__ . "/../storage/key.txt";

    public function getKey()
    {

        if (File::exists($this->pathKeyFile) == false) {
            throw new KeyFileDoesNotExistException();
        }

        return true;
    }
}
