<?php

namespace Alaa\Paymob\Exceptions;

use Throwable;

class PaymentException extends \Exception
{

    public function __construct($message = "unknown Exception", $code = -1 , Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}