<?php

namespace Matrix\Http\Controllers\Api;

use Exception;
use Log;
use Illuminate\Http\Request;
use Matrix\Contracts\UcManager;
use Matrix\Exceptions\MatrixException;

class TokenApiController extends Controller
{
  private $request;
  private $ucManager;

  public function __construct(Request $request, UcManager $ucManager)
  {
    $this->request = $request;
    $this->ucManager = $ucManager;

  }

  public function getVhallSign()
  {
    $reqData = $this->request->validate([
      'room_id' => 'required|string',
    ]);

    try {
      $sessionId = $this->request->header('X-SessionId');
      if (empty($sessionId)) {
        $sessionId = $this->request->cookie('X-SessionId');
      }

      $roomId = array_get($reqData, 'room_id');
      $appKey = config('token.vhall.app_key');
      $secretKey = config('token.vhall.secret_key');
      $signedat = time();

      if (empty($sessionId)) {
        $username = "游客";
        $account = time();
      } else {
        $ucUserInfo = $this->ucManager->getUserInfoBySessionId($sessionId);
        $username = array_get($ucUserInfo, 'data.user.nickName');
        $account = array_get($ucUserInfo, 'data.user.openId');
      }

      $params = [
        'roomid' => $roomId,
        'account' => $account,
        'username' => $username,
        'app_key' => $appKey,
        'signedat' => $signedat,
      ];

      ksort($params);

      array_walk($params, function (&$value, $key) {
        $value = $key . $value;
      });

      $vhallSign = md5($secretKey . implode('', $params) . $secretKey);

      $ret = [
        'code' => SYS_STATUS_OK,
        'msg' => 'success',
        'data' => [
          'vhall_sign' => $vhallSign,
          'signedat' => $signedat,
          'account' => $account, 
          'username' => $username,
        ],
      ];
    } catch (MatrixException $e) {
      Log::error($e->getMessage(), [$e]);
      $ret = [
        'code' => $e->getCode(),
        'msg' => $e->getMessage(),
      ];
    } catch (Exception $e) {
      Log::error($e->getMessage(), [$e]);
      $ret = [
        'code' => SYS_STATUS_ERROR_UNKNOW,
        'msg' => '未知错误',
      ];
    }
    
    return $ret;
  }
}