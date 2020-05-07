<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/23
 * Time: 9:08
 */

namespace Matrix\Exceptions;

use Throwable;

class BossException extends MatrixException
{
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        $message = "BOSS Exception: $message .";
        parent::__construct($message, $code, $previous);
    }
}
