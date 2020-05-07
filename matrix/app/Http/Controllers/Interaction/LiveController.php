<?php

namespace Matrix\Http\Controllers\Interaction;

use Illuminate\Http\Request;
use Matrix\Http\Controllers\Controller;

use Matrix\Contracts\TalkshowContract;
use Matrix\Contracts\UcManager;
use Matrix\Contracts\UserManager;
use Matrix\Contracts\UserGroupManager;

use Matrix\Models\Discuss;
use Matrix\Models\UserGroup;

use Matrix\Exceptions\InteractionException;
use Matrix\Exceptions\MatrixException;
use Exception;
use Log;
use Matrix\Contracts\InteractionContract;

class LiveController extends Controller
{
    const LIVE_DISCUSS_LIKE_TYPE = 'live_discuss';

    //
    private $request;
    private $talkshow;
    private $ucenter;
    private $user;
    private $userGroup;
    private $interaction;

    public function __construct(Request $request, TalkshowContract $talkshow, UcManager $ucenter, UserManager $user, UserGroupManager $userGroup, InteractionContract $interaction)
    {
        $this->request = $request;
        $this->talkshow = $talkshow;
        $this->ucenter = $ucenter;
        $this->user = $user;
        $this->userGroup = $userGroup;
        $this->interaction = $interaction;
    }

    public function getLiveDiscussList()
    {
        $credentials = $this->request->validate([
            'talkshow_code' => 'required|string',
            'live_room_code' => 'required|string',
            'last_discuss_id' => 'required|integer',
            'page_size' => 'required|integer',
        ]);
        $sessionId = $this->request->header('X-SessionId');
        if (empty($sessionId)) {
            $sessionId = $this->request->cookie('X-SessionId');
        }

        if (empty($sessionId)) {
            $ret = [
                'code' => CMS_API_X_SESSIONID_NOT_FOUND,
                'msg' => 'X-SessionId not found'
            ];
            return $ret;
        }

        $ucUserInfo = $this->ucenter->getUserInfoBySessionId($sessionId);

        $myOpenId = (string)array_get($ucUserInfo, 'data.user.openId');

        $lastDiscussId = array_get($credentials, 'last_discuss_id');
        unset($credentials['last_discuss_id']);
        $pageSize = array_get($credentials, 'page_size');
        unset($credentials['page_size']);
        $credentials['status'] = Discuss::STATUS_APPROVED;

        try {
            $discussList = $this->talkshow->getLiveDiscussListApp($lastDiscussId, $pageSize, $credentials, $myOpenId);

            // 兼容 点赞功能 -- http://task.daohehui.com/browse/ZYAPP-840
            if (!empty($discussList->toArray())) {
                // 获取对应的点赞状态
                $discussIdList = array_column($discussList->toArray(), 'id');
                $discussLikeList = $this->interaction->getLikeRecordList($discussIdList, self::LIVE_DISCUSS_LIKE_TYPE, $myOpenId);
                $discussIdOfLike = array_column($discussLikeList, 'article_id');
                
                // 获取对应对话的点赞总数
                $discussLikeSumList = $this->interaction->getLikeSumList($discussIdList, self::LIVE_DISCUSS_LIKE_TYPE);
                $likeSumOfDiscussId = array_column($discussLikeSumList, NULL, 'article_id');

                foreach ($discussList as &$discuss) {
                    // 本人是否点赞
                    if (in_array($discuss['id'], $discussIdOfLike)) {
                        $discuss['is_like'] = 1;
                    } else {
                        $discuss['is_like'] = 0;
                    }

                    // 点赞总数
                    if (empty($likeSumOfDiscussId[$discuss['id']])) {
                        $discuss['like_sum'] = 0;
                    } else {
                        $likeSum = (int)array_get($likeSumOfDiscussId[$discuss['id']], 'like_sum');
                        $discuss['like_sum'] = $likeSum;
                    }
                }
            }

            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'live_discuss_list' => $discussList,
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
            'talkshow_code' => 'required|string',
            'live_room_code' => 'required|string',
            'content' => 'required|string',
            'reply_to_open_id' => 'string|nullable',
            'reply_to_name' => 'string|nullable',
        ]);

        try {
            $sessionId = $this->request->header('X-SessionId');
            if (empty($sessionId)) {
                $sessionId = $this->request->cookie('X-SessionId');
            }

            if (empty($sessionId)) {
                throw new MatrixException('X-SessionId not found', CMS_API_X_SESSIONID_NOT_FOUND);
            }

            $ucUserInfo = $this->ucenter->getUserInfoBySessionId($sessionId, 'default', true);

            $credentials['open_id'] = (string)array_get($ucUserInfo, 'data.user.openId');
            $credentials['customer_name'] = (string)array_get($ucUserInfo, 'data.user.nickName');
            $credentials['icon_url'] = (string)array_get($ucUserInfo, 'data.user.iconUrl');
            $credentials['reply_to_open_id'] = (string)array_get($credentials, 'reply_to_open_id');
            $credentials['reply_to_name'] = (string)array_get($credentials, 'reply_to_name');

            if (empty($credentials['customer_name'])) {
                Log::info($ucUserInfo);
                throw new InteractionException('发表失败，没有设置昵称', INTERACTION_NICKNAME_NOT_SET);
            }

            try {
                $qyUserId = (string)array_get($ucUserInfo, 'data.user.qyUserId');
                if (!empty($qyUserId)) {
                    $userData = $this->user->getUserByEnterpriseUserId($qyUserId);
                    $userId = array_get($userData, 'data.id');
                    if (!empty($userId)) {
                        $userGroupList = $this->userGroup->getUserGroupListByCode(UserGroup::USER_GROUP_CODE_APPROVED_REPLY)->toArray();
                        if (!empty($userGroupList)) {
                            $userIdList = array_column($userGroupList, 'user_id');
                            if (in_array($userId, $userIdList)) { // is teacher
                                $credentials['status'] = Discuss::STATUS_APPROVED;
                                $credentials['examine_user_id'] = $userId;
                                $credentials['examine_at'] = (string)date('Y-m-d H:i:s');
                            }
                        }
                    }
                }
            } catch (MatrixException $e) {
            }

            $discuss = $this->talkshow->createDiscuss($credentials);
            $discuss = $discuss->toArray();
            $discuss['send_time'] = date('H:i', strtotime($discuss['created_at']));

            unset($discuss['created_at']);
            unset($discuss['updated_at']);
            unset($discuss['id']);

            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'discuss' => $discuss,
                ]
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
