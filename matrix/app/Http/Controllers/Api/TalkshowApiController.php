<?php

namespace Matrix\Http\Controllers\Api;

use Exception;
use Log;
use Illuminate\Http\Request;
use Matrix\Contracts\TalkshowContract;
use Matrix\Exceptions\MatrixException;
use Matrix\Models\Talkshow;

class TalkshowApiController extends Controller
{
  const GENSEE = 'fhcj.gensee.com';
  const VHALL = 'live.vhall.com';

  protected $request;
  protected $talkshowContract;

  public function __construct(Request $request, TalkshowContract $talkshowContract)
  {
    $this->request = $request;
    $this->talkshowContract = $talkshowContract;
  }

  public function getPredictInfo()
  {
    try {
      $ret = [
        'code' => SYS_STATUS_OK,
        'data' => [],
      ];

      $predict = $this->talkshowContract->getPredictInfo();
      if (empty($predict)) {
        $predict =  $this->talkshowContract->getLastTalkshow();
      }

      $predict['talkshow_code'] = $predict['code'];
      $predict['start_at'] = date('H:i', strtotime($predict['start_time']));
      $predict['end_at'] = date('H:i', strtotime($predict['end_time']));
      $predict['start_time'] = strtotime($predict['start_time']);
      $predict['end_time'] = strtotime($predict['end_time']);
      $predict['summary'] = $predict['boardcast_content'];
      $predict['category_code'] = 'daily_talkshow';
      $predict['source_url'] = empty(array_get($predict, 'source_url')) ? config("app.url") . sprintf('/api/v2/client/live/talkshow/%s', $predict['code']) : $predict['source_url'];
      
      unset($predict['id']);
      unset($predict['code']);
      unset($predict['video_vendor_code']);
      unset($predict['teacher_id']);
      unset($predict['boardcast_content']);
      unset($predict['last_modify_user_id']);
      unset($predict['play_url']);
      unset($predict['created_at']);
      unset($predict['updated_at']);
      $ret['data']['talkshow'] = $predict;
      $ret['data']['status'] = (int)array_get($predict, 'status');
      unset($ret['data']['talkshow']['status']);

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