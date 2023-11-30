<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class CustomException extends \RuntimeException
{
    public function __construct($message, $code = 400, $responseCode = null)
    {
        parent::__construct($message, $code);
        if (isset($responseCode)) {
            $this->responseCode = $responseCode;
        }
    }
}
