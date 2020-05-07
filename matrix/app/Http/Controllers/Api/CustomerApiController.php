<?php

namespace Matrix\Http\Controllers\Api;

use Illuminate\Http\Request;

use Matrix\Contracts\CustomerManager;
use Matrix\Contracts\UcManager;
use Exception;
use Cache;
use Log;

class CustomerApiController extends Controller
{
    private $request;
    private $ucManager;
    private $customerManager;

    public function __construct(Request $request, CustomerManager $customerManager, UcManager $ucManager)
    {
        $this->request = $request;
        $this->customerManager = $customerManager;
        $this->ucManager = $ucManager;
    }

    protected function maskMobile($mobile)
    {
        return substr_replace($mobile, '****', 3, 4);
    }

    public function login()
    {
        $session = $this->request->validate([
            'session_id' => 'required|string',
            'channel' => 'required|string',
        ]);

        try {
            $sessionId = array_get($session, 'session_id');
            $channel = array_get($session, 'channel');
            $ucUserInfo = $this->ucManager->getUserInfoBySessionId($sessionId, $channel);

            $customerData = [
                'open_id' => (string)array_get($ucUserInfo, 'data.user.openId'),
                'code' => (string)array_get($ucUserInfo, 'data.user.customerCode'),
                'name' => (string)array_get($ucUserInfo, 'data.user.name'),
                'mobile' => (string)array_get($ucUserInfo, 'data.user.mobile'),
                'icon_url' => (string)array_get($ucUserInfo, 'data.user.iconUrl'),
                'qy_userid' => (string)array_get($ucUserInfo, 'data.user.qyUserId'),
            ];

            $customerInfo = $this->customerManager->updateCustomer($customerData);
            $session = array_merge($session, $customerData);
            $session['mobile'] = $this->maskMobile($customerInfo['mobile']);

            Cache::put($sessionId, $session, config('uc.session.lifetime'));

            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'customer' => $session,
                ],
            ];
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = ['code' => SYS_STATUS_ERROR_UNKNOW];
        }

        return $ret;
    }

    public function logout()
    {
        try {
            $sessionId = $this->request->header('X-SessionId');
            Cache::forget($sessionId);
            $ret = ['code' => SYS_STATUS_OK];
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = ['code' => SYS_STATUS_ERROR_UNKNOW];
        }

        return $ret;
    }
}

