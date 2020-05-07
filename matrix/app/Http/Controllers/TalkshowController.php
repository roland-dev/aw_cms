<?php

namespace Matrix\Http\Controllers;

use Illuminate\Http\Request;
use Matrix\Contracts\TalkshowContract;
use Matrix\Contracts\OperateLogContract;

use Matrix\Exceptions\VideoException;
use Matrix\Exceptions\MatrixException;
use Exception;
use Auth;
use Log;
use Illuminate\Support\Facades\Storage;
use Matrix\Models\Talkshow;

class TalkshowController extends Controller
{
    //
    private $request;
    private $talkshow;
    private $operateLog;

    public function __construct(Request $request, TalkshowContract $talkshow, OperateLogContract $operateLog)
    {
        $this->request = $request;
        $this->talkshow = $talkshow;
        $this->operateLog = $operateLog;
    }

    public function getStaticTalkshowList()
    {
        $credentials = $this->request->validate([
            'page_no' => 'required|integer',
            'page_size' => 'required|integer',
        ]);

        $pageNo = array_get($credentials, 'page_no');
        $pageSize = array_get($credentials, 'page_size');

        try {
            $staticTalkshowList = $this->talkshow->getStaticTalkshowList($pageNo, $pageSize);
            $staticTalkshowCnt = $this->talkshow->getStaticTalkshowCnt();

            $ret = [
                'code' => SYS_STATUS_OK,
                'msg' => 'success',
                'data' => [
                    'static_talkshow_list' => $staticTalkshowList,
                    'static_talkshow_cnt' => $staticTalkshowCnt,
                ],
            ];
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => '发生了一个不可预知的错误',
            ];
        }

        return $ret;
    }

    public function createStaticTalkshow()
    {
        $credentials = $this->request->validate([
            'video_vendor_code' => 'required|string',
            'title' => 'required|string',
            'teacher_id' => 'required|integer',
            'start_time' => 'required|string',
            'end_time' => 'required|string',
            'banner_url' => 'required|string',
            'type' => 'required|string',
            'live_room_code' => 'string',
            'boardcast_content' => 'required|string',
        ]);

        try {
            $credentials['last_modify_user_id'] = Auth::user()->id;
            $staticTalkshow = $this->talkshow->createStaticTalkshow($credentials);
            $this->operateLog->record('create', 'static_talkshow', $staticTalkshow->id, "用户 ".Auth::user()->name." 创建了一个固定节目 {$staticTalkshow}", $this->request->ip(), Auth::user()->id);

            $ret = [
                'code' => SYS_STATUS_OK,
                'msg' => 'success',
                'data' => [
                    'static_talkshow' => $staticTalkshow,
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
                'msg' => '发生了一个不可预知的错误',
            ];
        }

        return $ret;
    }

    public function updateStaticTalkshow(string $staticTalkshowId)
    {
        $credentials = $this->request->validate([
            'video_vendor_code' => 'string',
            'title' => 'string',
            'teacher_id' => 'integer',
            'start_time' => 'string',
            'end_time' => 'string',
            'banner_url' => 'string',
            'type' => 'string',
            'live_room_code' => 'string',
            'boardcast_content' => 'string',
        ]);

        try {
            if (empty($credentials)) {
                throw new MatrixException('什么都不传您打算让我更新什么呢？', SYS_STATUS_OK);
            }

            $credentials['last_modify_user_id'] = Auth::user()->id;
            $staticTalkshow = $this->talkshow->updateStaticTalkshow($staticTalkshowId, $credentials);

            $this->operateLog->record('update', 'static_talkshow', $staticTalkshow->id, "用户 ".Auth::user()->name." 修改了一个固定节目 {$staticTalkshow}", $this->request->ip(), Auth::user()->id);
            $ret = [
                'code' => SYS_STATUS_OK,
                'msg' => 'success',
                'data' => [
                    'static_talkshow' => $staticTalkshow,
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
                'msg' => '发生了一个不可预知的错误',
            ];
        }

        return $ret;
    }

    public function removeStaticTalkshow(string $staticTalkshowId)
    {
        try {
            $staticTalkshow = $this->talkshow->removeStaticTalkshow($staticTalkshowId);

            $this->operateLog->record('delete', 'static_talkshow', $staticTalkshowId, "用户 ".Auth::user()->name." 删除了一个固定节目 {$staticTalkshowId}", $this->request->ip(), Auth::user()->id);
            $ret = [
                'code' => SYS_STATUS_OK,
                'msg' => 'success',
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
                'msg' => '发生了一个不可预知的错误',
            ];
        }

        return $ret;
    }

    public function getStaticTalkshow(string $staticTalkshowId)
    {
        try {
            $staticTalkshow = $this->talkshow->getStaticTalkshow($staticTalkshowId);

            $ret = [
                'code' => SYS_STATUS_OK,
                'msg' => 'success',
                'data' => [
                    'static_talkshow' => $staticTalkshow,
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
                'msg' => '发生了一个不可预知的错误',
            ];
        }

        return $ret;
    }

    public function getTalkshowList()
    {
        $credentials = $this->request->validate([
            'date' => 'required|string',
            'page_no' => 'required|integer',
            'page_size' => 'required|integer',
        ]);

        try {
            $cond = [
                'date' => (string)array_get($credentials, 'date'),
            ];

            $pageNo = array_get($credentials, 'page_no');
            $pageSize = array_get($credentials, 'page_size');

            $talkshowList = $this->talkshow->getTalkshowList($pageNo, $pageSize, $cond);
            $talkshowCnt = $this->talkshow->getTalkshowCnt($cond);

            $ret = [
                'code' => SYS_STATUS_OK,
                'msg' => 'success',
                'data' => [
                    'talkshow_list' => $talkshowList,
                    'talkshow_cnt' => $talkshowCnt,
                ],
            ];
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => '发生了一个不可预知的错误',
            ];
        }

        return $ret;
    }

    public function importTalkshowList()
    {
        $credentials = $this->request->validate([
            'date' => 'required|string',
        ]);
        try {
            $this->talkshow->importTalkshowList($credentials);

            $this->operateLog->record('create', 'talkshow', 0, "用户 ".Auth::user()->name." 导入了一个节目列表", $this->request->ip(), Auth::user()->id);

            $ret = [
                'code' => SYS_STATUS_OK,
                'msg' => 'success',
            ];
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => '发生了一个不可预知的错误',
            ];
        }

        return $ret;
    }

    public function createTalkshow()
    {
        $credentials = $this->request->validate([
            'video_vendor_code' => 'required|string',
            'title' => 'required|string',
            'teacher_id' => 'required|integer',
            'start_time' => 'required|string',
            'end_time' => 'required|string',
            'banner_url' => 'required|string',
            'type' => 'required|string',
            'live_room_code' => 'string',
            'boardcast_content' => 'required|string',
            'description' => 'required|string',
            'play_url' => 'nullable|string',
        ]);

        $ret = [];

        try {
            $existTalkshow = $this->talkshow->getTalkshowByTime($credentials['start_time'], $credentials['end_time']);
            $ret['code'] = TALKSHOW_TIME_MISTAKE;
            $ret['msg'] = '请注意：这个时间段已经有节目存在！';
        } catch(VideoException $e) {
        } catch(MatrixException $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = [
                'code' => $e->getCode(),
                'msg' => $e->getMessage()
            ];
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => '发生了一个不可预知的错误',
            ];
        }

        try {
            $credentials['last_modify_user_id'] = Auth::user()->id;
            $credentials['play_url'] = (string)array_get($credentials, 'play_url');
            $talkshow = $this->talkshow->createTalkshow($credentials);
            $this->operateLog->record('create', 'talkshow', $talkshow->id, "用户 ".Auth::user()->name." 创建了一个节目 {$talkshow}", $this->request->ip(), Auth::user()->id);

            $ret['code'] = array_key_exists('code', $ret) ? $ret['code'] : SYS_STATUS_OK;
            $ret['msg'] = array_key_exists('msg', $ret) ? $ret['msg'] : 'success';
            $ret['data'] = [
                'talkshow' => $talkshow,
            ];
        } catch(MatrixException $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = [
                'code' => $e->getCode(),
                'msg' => $e->getMessage()
            ];
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => '发生了一个不可预知的错误',
            ];
        }

        return $ret;
    }

    public function updateTalkshow(string $talkshowCode)
    {
        $credentials = $this->request->validate([
            'video_vendor_code' => 'required|string',
            'title' => 'required|string',
            'teacher_id' => 'required|integer',
            'start_time' => 'required|string',
            'end_time' => 'required|string',
            'banner_url' => 'required|string',
            'type' => 'required|string',
            'live_room_code' => 'string',
            'boardcast_content' => 'required|string',
            'description' => 'required|string',
            'play_url' => 'nullable|string',
            'code' => 'string',
        ]);

        $ret = [];

        try {
            $existTalkshow = $this->talkshow->getTalkshowByTime($credentials['start_time'], $credentials['end_time']);
            if ($existTalkshow->code != $talkshowCode) {
                $ret['code'] = TALKSHOW_TIME_MISTAKE;
                $ret['msg'] = '请注意：这个时间段已经有节目存在！';
            }
        } catch(VideoException $e) {
        } catch(MatrixException $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = [
                'code' => $e->getCode(),
                'msg' => $e->getMessage()
            ];
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => '发生了一个不可预知的错误',
            ];
        }

        try {
            $credentials['last_modify_user_id'] = Auth::user()->id;
            $credentials['play_url'] = (string)array_get($credentials, 'play_url');
            $talkshow = $this->talkshow->updateTalkshow($talkshowCode, $credentials);
            $this->operateLog->record('update', 'talkshow', $talkshow->id, "用户 ".Auth::user()->name." 更新了一个节目 {$talkshow}", $this->request->ip(), Auth::user()->id);

            $ret['code'] = array_key_exists('code', $ret) ? $ret['code'] : SYS_STATUS_OK;
            $ret['msg'] = array_key_exists('msg', $ret) ? $ret['msg'] : 'success';
            $ret['data'] = [
                'talkshow' => $talkshow,
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
                'msg' => '发生了一个不可预知的错误',
            ];
        }

        return $ret;
    }

    public function operateTalkshow(string $talkshowCode)
    {
        $operate = (int)$this->request->input('operate');
        if (!in_array($operate, [
            Talkshow::STATUS_PLAY,
            Talkshow::STATUS_DONE,
        ])) {
            abort(405, '只能控制节目开始及结束.');
        }

        try {
            $talkshow = $this->talkshow->operateTalkshow($talkshowCode, $operate);

            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'talkshow' => $talkshow->toArray(),
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
                'msg' => '发生了一个不可预知的错误',
            ];
        }

        return $ret;
    }

    public function removeTalkshow(string $talkshowCode)
    {
        try {
            $talkshow = $this->talkshow->removeTalkshow($talkshowCode);
            $this->operateLog->record('delete', 'talkshow', $talkshowCode, "用户 ".Auth::user()->name." 删除了一个节目 {$talkshowCode}", $this->request->ip(), Auth::user()->id);

            $ret = [
                'code' => SYS_STATUS_OK,
                'msg' => 'success',
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
                'msg' => '发生了一个不可预知的错误',
            ];
        }

        return $ret;
    }

    public function getTalkshow(string $talkshowCode)
    {
        try {
            $talkshow = $this->talkshow->getTalkshow($talkshowCode);

            $ret = [
                'code' => SYS_STATUS_OK,
                'msg' => 'success',
                'data' => [
                    'talkshow' => $talkshow,
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
                'msg' => '发生了一个不可预知的错误',
            ];
        }

        return $ret;
    }

    public function uploadBannerImage()
    {
        if (!$this->request->hasFile('image')) {
            abort(400);
        }
        $path = $this->request->image->store('public/talkshow/banner');

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'path' => config('app.cdn.base_url').Storage::url($path),
            ],
        ];

        return $ret;
    }
}
