<?php

namespace Ymigval\LaravelIndexnow\Exceptions;

class ExcessUrlsException extends BaseLoggingException
{
    public function __construct()
    {
        $message = 'You have exceeded the maximum allowed number of URLs to send to IndexNow.';
        parent::__construct($message);
    }
}