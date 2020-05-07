<?php

namespace Matrix\Http\Controllers\Api;

use Illuminate\Http\Request;
use Matrix\Contracts\SystemNoticeManager;
use Cache;

class SystemNoticeApiController extends Controller
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
        $sessionId = $this->request->header('X-SessionId');
        if (empty($sessionId)) {
            abort(401);
        }
        $session = Cache::get($sessionId);
        if (empty($session)) {
            abort(401);
        }
        $openId = array_get($session, 'open_id');

        $systemNoticeListData = $this->systemNoticeManager->getCustomerSystemNoticeList($openId);
        $systemNoticeList = array_get($systemNoticeListData, 'data.system_notice_list');
        $statusList = array_column($systemNoticeList, 'read');
        $statusCount = array_count_values($statusList);

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'unread_count' => array_key_exists('0', $statusCount) ? $statusCount['0'] : 0,
                'system_notice_list' => $systemNoticeList,
            ],
        ];

        return $ret;
    }

    public function putRead(int $systemNoticeId)
    {
        $sessionId = $this->request->header('X-SessionId');
        if (empty($sessionId)) {
            abort(401);
        }
        $session = Cache::get($sessionId);
        if (empty($session)) {
            abort(401);
        }
        $openId = array_get($session, 'open_id');

        $systemNoticeReadRes = $this->systemNoticeManager->readCustomerSystemNotice($systemNoticeId, $openId);

        $ret = ['code' => array_get($systemNoticeReadRes, 'code')];
        return $ret;
    }
}
