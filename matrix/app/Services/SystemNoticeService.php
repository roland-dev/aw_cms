<?php

namespace Matrix\Services;

use Matrix\Contracts\SystemNoticeManager;
use Matrix\Models\SystemNotice;
use Auth;

class SystemNoticeService extends BaseService implements SystemNoticeManager
{
    private $systemNotice;

    public function __construct(SystemNotice $systemNotice)
    {
        $this->systemNotice = $systemNotice;
    }

    public function getCustomerSystemNoticeList(string $openId)
    {
        $systemNoticeList = $this->systemNotice->getSystemNoticeList(SystemNotice::TARGET_CLIENT, $openId);
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'system_notice_list' => $systemNoticeList,
            ],
        ];

        return $ret;
    }

    public function readCustomerSystemNotice(int $systemNoticeId, string $openId)
    {
        try {
            $this->systemNotice->readSystemNotice(SystemNotice::TARGET_CLIENT, $openId, $systemNoticeId);
            $ret = ['code' => SYS_STATUS_OK];
        } catch (Exception $e) {
            $ret = ['code' => SYS_STATUS_ERROR_UNKNOW];
        }

        return $ret;
    }

    public function getManageSystemNoticeList(int $userId)
    {
        $systemNoticeList = $this->systemNotice->getSystemNoticeList(SystemNotice::TARGET_MANAGE, $userId);
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'system_notice_list' => $systemNoticeList,
            ],
        ];

        return $ret;
    }

    public function readManageSystemNotice(int $systemNoticeId)
    {
        try {
            $userId = Auth::user()->id;
            $this->systemNotice->readSystemNotice(SystemNotice::TARGET_MANAGE, $userId, $systemNoticeId);
            $ret = ['code' => SYS_STATUS_OK];
        } catch (Exception $e) {
            $ret = ['code' => SYS_STATUS_ERROR_UNKNOW];
        }

        return $ret;
    }

}

