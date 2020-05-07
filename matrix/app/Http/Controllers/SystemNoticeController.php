<?php

namespace Matrix\Http\Controllers;

use Illuminate\Http\Request;
use Matrix\Contracts\SystemNoticeManager;
use Auth;

class SystemNoticeController extends Controller
{
    //
    private $request;
    private $systemNoticeManager;

    public function __construct(Request $request, SystemNoticeManager $systemNoticeManager)
    {
        $this->request = $request;
        $this->systemNoticeManager = $systemNoticeManager;
    }

    public function getSystemNoticeList()
    {
        $userId = Auth::user()->id;
        $systemNoticeListData = $this->systemNoticeManager->getManageSystemNoticeList($userId);
        $systemNoticeList = array_get($systemNoticeListData, 'data.system_notice_list');

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'system_notice_list' => $systemNoticeList,
            ],
        ];

        return $ret;
    }

    public function putRead(int $systemNoticeId)
    {
        $systemNoticeReadRes = $this->systemNotice->readManageSystemNotice($systemNoticeId);

        $ret = ['code' => array_get($systemNoticeReadRes, 'code')];
        return $ret;
    }
}
