<?php

namespace Matrix\Http\Controllers\Client;

use Illuminate\Http\Request;
use Matrix\Contracts\UcManager;
use Exception;
use Log;

class NicknameController extends Controller
{
    private $request;
    private $ucenter;

    public function __construct(Request $request, UcManager $ucenter)
    {
        $this->request = $request;
        $this->ucenter = $ucenter;
    }

    /**
    *返回修改nickname模版
    *@return blade
    **/
    public function nicknameBlade()
    {
        $loginUrl = $this->h5WechatAutoLogin($this->request, $this->ucenter);
        if (!empty($loginUrl)) {
            return redirect()->away($loginUrl);
        }

        $sessionId = $this->request->cookie('X-SessionId');

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => $sessionId,
        ];

        return view('component.nickname', $ret);
    }

    public function forgetCache($sessionId){
        try{
            $this->ucenter->forgetCache($sessionId);

            $ret = [
                'code' => SYS_STATUS_OK,
                'msg' => '清楚缓存成功',
            ];
        }catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);

            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => '系统未知错误',
            ];
        }

        return $ret;
    }

    //public function modifyNicknameToUc(UcManager $ucenter)
    //{
    //    try{
    //        $request = $this->request->validate([
    //            'nickName' => 'required|string',
    //        ]);

    //        //return $sessionId;

    //        $resp = $ucenter->modifyNickname(array_get($request, 'nickName'));
    //        return $resp;
    //    }catch(Exception $e){
    //        Log::error($e->getMessage(), [$e]);

    //        $ret = [
    //            'code' => SYS_STATUS_ERROR_UNKNOW,
    //            'msg' => '系统未知错误',
    //        ];
    //    }

    //    return $ret;
    //}
}
