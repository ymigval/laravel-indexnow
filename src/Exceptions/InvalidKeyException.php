<?php

namespace Ymigval\LaravelIndexnow\Exceptions;

class InvalidKeyException extends BaseLoggingException
{
    public function __construct()
    {
        $message = 'Your IndexNow key is invalid. To create a new one, use the command: php artisan indexnow:generate-apikey';
        parent::__construct($message);
    }
}
