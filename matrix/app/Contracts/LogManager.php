<?php

namespace Matrix\Contracts;

interface LogManager extends BaseInterface
{
    public function getOperationLogList();
    public function createOperationLog(string $sourceKey, int $userId, string $originalData, string $operate);
}

