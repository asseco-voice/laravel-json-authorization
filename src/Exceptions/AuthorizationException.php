<?php

namespace Asseco\JsonAuthorization\Exceptions;

use Exception;
use Throwable;

class AuthorizationException extends Exception
{
    public function __construct($message = '', $code = 0, Throwable $previous = null)
    {
        $message = "[Authorization] $message";
        parent::__construct($message, $code, $previous);
    }
}
