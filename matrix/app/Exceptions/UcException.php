<?php

namespace Matrix\Exceptions;

class UcException extends MatrixException
{
    //
    public function __construct(string $message = "", int $code = 0, Throwable $previous = NULL)
    {
        $message = "User Center Exception: $message .";
        parent::__construct($message, $code, $previous);
    }
}
