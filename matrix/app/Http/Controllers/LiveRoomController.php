<?php

namespace Matrix\Http\Controllers;

use Illuminate\Http\Request;
use Matrix\Contracts\TalkshowContract;
use Matrix\Contracts\OperateLogContract;

use Matrix\Exceptions\MatrixException;
use Exception;
use Auth;
use Log;

class LiveRoomController extends Controller
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

    public function getLiveRoomList()
    {
        $credentials = $this->request->validate([
            'page_no' => 'required|integer',
            'page_size' => 'required|integer',
        ]);

        $pageNo = array_get($credentials, 'page_no');
        $pageSize = array_get($credentials, 'page_size');

        try {
            $liveRoomList = $this->talkshow->getLiveRoomList($pageNo, $pageSize);
            $liveRoomCnt = $this->talkshow->getLiveRoomCnt();

            $ret = [
                'code' => SYS_STATUS_OK,
                'msg' => 'success',
                'data' => [
                    'live_room_list' => $liveRoomList,
                    'live_room_cnt' => $liveRoomCnt,
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

    public function createLiveRoom()
    {
        $credentials = $this->request->validate([
            'code' => 'required|string',
            'name' => 'required|string',
            'password' => 'string|nullable',
        ]);

        $credentials['last_modify_user_id'] = Auth::user()->id;
        $credentials['password'] = (string)array_get($credentials, 'password');

        try {
            $liveRoom = $this->talkshow->createLiveRoom($credentials);
            $this->operateLog->record('create', 'live_room', $liveRoom->id, "用户 ".Auth::user()->name." 创建了一个直播室 {$liveRoom}", $this->request->ip(), Auth::user()->id);

            $ret = [
                'code' => SYS_STATUS_OK,
                'msg' => 'success',
                'data' => [
                    'live_room' => $liveRoom,
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

    public function updateLiveRoom(string $roomCode)
    {
        $credentials = $this->request->validate([
            'code' => 'string',
            'name' => 'string',
            'password' => 'string|nullable',
        ]);

        try {
            $credentials['password'] = (string)array_get($credentials, 'password');
            $liveRoom = $this->talkshow->updateLiveRoom($roomCode, $credentials);
            $this->operateLog->record('update', 'live_room', $liveRoom->id, "用户 ".Auth::user()->name." 更新了一个直播室 {$liveRoom}", $this->request->ip(), Auth::user()->id);

            $ret = [
                'code' => SYS_STATUS_OK,
                'msg' => 'success',
                'data' => [
                    'live_room' => $liveRoom,
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

    public function removeLiveRoom(string $roomCode)
    {
        try {
            $this->talkshow->removeLiveRoom($roomCode);
            $ret = [
                'code' => SYS_STATUS_OK,
                'msg' => '删除成功',
            ];

            $this->operateLog->record('delete', 'live_room', $roomCode, "用户 ".Auth::user()->name." 删除了一个直播室 {$roomCode}", $this->request->ip(), Auth::user()->id);
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

    public function getLiveRoom(string $roomCode)
    {
        try {
            $liveRoom = $this->talkshow->getLiveRoom($roomCode);
            $ret = [
                'code' => SYS_STATUS_OK,
                'msg' => 'success',
                'data' => [
                    'live_room' => $liveRoom,
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
}
