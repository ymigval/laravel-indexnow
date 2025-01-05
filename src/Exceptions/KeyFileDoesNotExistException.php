<?php

namespace Ymigval\LaravelIndexnow\Exceptions;

class KeyFileDoesNotExistException extends BaseLoggingException
{
    public function __construct()
    {
        $message = 'The IndexNow key file does not exist. To create one, use the command: php artisan indexnow:generate-apikey';
        parent::__construct($message);
    }
}
