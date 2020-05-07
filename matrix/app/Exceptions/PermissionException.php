<?php

namespace Matrix\Exceptions;

class PermissionException extends MatrixException
{
    //
    public function __construct(string $message = "", int $code = SYS_STATUS_PERMISSION_ERROR, Throwable $previous = NULL)
    {
        $message = "Permission Exception: $message .";
        parent::__construct($message, $code, $previous);
    }
}
