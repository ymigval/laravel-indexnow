<?php

namespace Ymigval\LaravelIndexnow\Exceptions;

class MixedException extends BaseLoggingException
{
    public function __construct(string $message, int $code = 0)
    {
        parent::__construct($message, $code);
    }
}
