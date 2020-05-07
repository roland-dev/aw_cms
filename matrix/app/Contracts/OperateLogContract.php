<?php
namespace Matrix\Contracts;

interface OperateLogContract extends BaseInterface
{
    public function record(string $operateCode, string $contentType, string $contentId, string $message, string $ip, int $operatorUserId);
}
