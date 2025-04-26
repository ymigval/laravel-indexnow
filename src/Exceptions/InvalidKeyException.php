<?php

namespace Ymigval\LaravelIndexnow\Exceptions;

class InvalidKeyException extends BaseLoggingException
{
    public function __construct()
    {
        $message = 'Your IndexNow key is invalid. Please configure a valid key in your .env file with INDEXNOW_API_KEY.';
        parent::__construct($message);
    }
}
