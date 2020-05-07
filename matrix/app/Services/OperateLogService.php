<?php
namespace Matrix\Services;

use Matrix\Contracts\OperateLogContract;
use Matrix\Models\OperateLog;

class OperateLogService extends BaseService implements OperateLogContract
{
    protected function getOperateTitle(string $operateCode)
    {
        $operateList = [
            'create' => '创建',
            'update' => '更新',
            'delete' => '删除',
            'approve' => '批准',
            'reject' => '拒绝',
            'publish' => '发布',
        ];

        $operateTitle = (string)array_get($operateList, $operateCode);

        return $operateTitle;
    }

    public function record(string $operateCode, string $contentType, string $contentId, string $message, string $ip, int $operatorUserId)
    {
        $operateLog = OperateLog::create([
            'operator_user_id' => $operatorUserId,
            'operate_code' => $operateCode,
            'operate_title' => $this->getOperateTitle($operateCode),
            'content_type' => $contentType,
            'content_id' => $contentId,
            'message' => $message,
            'ip' => $ip,
        ]);

        return $operateLog;
    }
}
