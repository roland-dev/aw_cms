<?php

namespace Matrix\Http\Controllers;

use Illuminate\Http\Request;
use Matrix\Contracts\TalkshowContract;
use Matrix\Contracts\OperateLogContract;
use Matrix\Contracts\UserManager;
use Matrix\Contracts\CustomerManager;

use Matrix\Exceptions\MatrixException;

use Matrix\Models\Discuss;
use Exception;
use Auth;
use Log;


class DiscussController extends Controller
{
    //
    private $request;
    private $user;
    private $customer;
    private $talkshow;
    private $operateLog;

    public function __construct(Request $request, TalkshowContract $talkshow, OperateLogContract $operateLog, UserManager $user, CustomerManager $customer)
    {
        $this->request = $request;
        $this->talkshow = $talkshow;
        $this->operateLog = $operateLog;
        $this->user = $user;
        $this->customer = $customer;
    }

    public function getDiscussList()
    {
        $credentials = $this->request->validate([
            'page_no' => 'required|integer',
            'page_size' => 'required|integer',
            'status' => 'required|integer',
            'live_room_code' => 'string',
            'category_code' => 'string',
            'title' => 'string',
            'start_time' => 'string',
            'end_time' => 'string',
        ]);

        $pageNo = array_get($credentials, 'page_no');
        $pageSize = array_get($credentials, 'page_size');

        try {
            $discussList = $this->talkshow->getDiscussList($pageNo, $pageSize, $credentials);
            $discussCnt = $this->talkshow->getDiscussCnt($credentials);

            $ret = [
                'code' => SYS_STATUS_OK,
                'msg' => 'success',
                'data' => [
                    'discuss_list' => $discussList,
                    'discuss_cnt' => $discussCnt,
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

    public function createDiscuss()
    {
        $credentials = $this->request->validate([
            'reply_discuss_id' => 'integer',
            'live_room_code' => 'required|string',
            'talkshow_code' => 'required|string',
            'content' => 'required|string',
            'reply_to_open_id' => 'required|string',
            'reply_to_name' => 'required|string',
        ]);

        try {
            $userId = Auth::user()->id;

            $userInfoData = $this->user->getUserInfo($userId);
            $userInfo = array_get($userInfoData, 'userInfo');
            $qyUserid = array_get($userInfoData, 'ucInfo.enterprise_userid');
            $customer = $this->customer->getCustomerByQyUserid($qyUserid);

            $credentials['open_id'] = $customer->open_id;
            $credentials['customer_name'] = $customer->name;
            $credentials['icon_url'] = $customer->icon_url;

            if (array_key_exists('reply_discuss_id', $credentials)) {
                $discuss = $this->talkshow->examineDiscuss($credentials['reply_discuss_id'], Discuss::STATUS_APPROVED);
                $this->operateLog->record('approve', 'discuss', $credentials['reply_discuss_id'], "用户 ".Auth::user()->name." 审批了一个直播讨论 {$credentials['reply_discuss_id']}", $this->request->ip(), Auth::user()->id);
            }

            $credentials['status'] = Discuss::STATUS_APPROVED;
            $credentials['examine_user_id'] = $userId;
            $credentials['examine_at'] = (string)date('Y-m-d H:i:s');
            $discuss = $this->talkshow->createDiscuss($credentials);
            $this->operateLog->record('create', 'discuss', $discuss->id, "用户 ".Auth::user()->name." 回复了一个直播讨论 {$discuss->id}", $this->request->ip(), Auth::user()->id);

            $ret = [
                'code' => SYS_STATUS_OK,
                'msg' => 'success',
                'data' => [
                    'discuss' => $discuss,
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

    public function examine(int $discussId)
    {
        $credentials = $this->request->validate([
            'operate' => 'required|integer',
        ]);

        $operate = array_get($credentials, 'operate');
        try {
            $discuss = $this->talkshow->examineDiscuss($discussId, $operate);
            $logOperate = $operate == Discuss::STATUS_APPROVED ? 'approve' : 'reject';
            $this->operateLog->record($logOperate, 'discuss', $discussId, "用户 ".Auth::user()->name." 审批了一个直播讨论 {$discussId}", $this->request->ip(), Auth::user()->id);

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

    public function batchExamine()
    {
        $credentials = $this->request->validate([
            'discuss_id_list' => 'required|array',
            'operate' => 'required|integer',
        ]);

        $discussIdList = array_get($credentials, 'discuss_id_list');
        $operate = array_get($credentials, 'operate');


        try {
            collect($discussIdList)->each(function ($discussId, $key) use ($operate) {
                $discuss = $this->talkshow->examineDiscuss($discussId, $operate);
                $logOperate = $operate == Discuss::STATUS_APPROVED ? 'approve' : 'reject';
                $this->operateLog->record($logOperate, 'discuss', $discussId, "用户 ".Auth::user()->name." 审批了一个直播讨论 {$discussId}", $this->request->ip(), Auth::user()->id);
            });

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
}
