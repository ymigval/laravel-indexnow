<?php

namespace Ymigval\LaravelIndexnow\Exceptions;

use Exception;

class KeyFileDoesNotExistException extends Exception
{
    /**
     * @var string
     */
    protected $message = "The key file does not exist. To create a new one, run the command: php artisan indexnow:newkey";

    /**
     * @var integer
     */
    protected $code = 404;
}
