<?php

namespace Matrix\Http\Controllers\Api;

use Illuminate\Http\Request;
use Matrix\Contracts\TwitterManager;
use Matrix\Models\TwitterGuard;
use Matrix\Models\PrivateMessageGuard;
use Matrix\Models\PrivateMessage;
use Matrix\Contracts\CategoryManager;
use Exception;
use Cache;
use Log;

class TwitterApiController extends Controller
{
    //
    private $request;
    private $twitterManager;
    private $categoryManager;

    public function __construct(Request $request, TwitterManager $twitterManager, CategoryManager $categoryManager)
    {
        $this->request = $request;
        $this->twitterManager = $twitterManager;
        $this->categoryManager = $categoryManager;
    }

    public function requestTwitter()
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
        $category = $this->request->validate([
            'category_code' => 'string',
        ]);
        $categoryCode = array_get($category, 'category_code');
        try {
            $categoryInfoData = $this->categoryManager->getCategoryInfo($categoryCode, $openId);
            $twitterRequestData = $this->twitterManager->createTwitterRequest($categoryCode, $openId);
            $twitterRequest = array_get($twitterRequestData, 'data.twitter_request');
            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'twitter_request' => $twitterRequest,
                ],
            ];
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = ['code' => SYS_STATUS_ERROR_UNKNOW];
        }

        return $ret;
    }

    public function getTwitterList()
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
        $options = $this->request->validate([
            'category_code' => 'string|nullable',
        ]);
        $categoryCode = array_get($options, 'category_code');
        try {
            $categoryCodeListData = $this->twitterManager->getTwitterApprovedCategoryCodeList($openId);
            $categoryCodeList = array_get($categoryCodeListData, 'data.category_code_list');
            if (!empty($categoryCode)) {
                if (!in_array($categoryCode, $categoryCodeList)) {
                    abort(403);
                }
                $categoryCodeList = [$categoryCode];
            }
            $twitterListData = $this->twitterManager->getTwitterList($categoryCodeList, $openId);
            $twitterList = array_get($twitterListData, 'data.twitter_list');
            $twitterFollow = array_get($twitterListData, 'data.twitter_follow');
            $categoryListData = $this->categoryManager->getCategoryList();
            $categoryList = array_get($categoryListData, 'data.category_list');
            $categoryNameList = array_column($categoryList, 'name', 'code');
            $teacherListData = $this->categoryManager->getTeacherListByCategoryCodeList($categoryCodeList);
            $teacherList = array_get($teacherListData, 'data.teacher_list');
            $categoryIconList = [];
            foreach ($teacherList as $teacher) {
                if (!empty(array_get($teacher, 'primary'))) {
                    $categoryIconList[$teacher['category_code']] = $this->fitDetailUrl(array_get($teacher, 'icon_url'), $this->request);
                }
            }

            foreach ($twitterList as &$twitter) {
                $twitter['category_name'] = array_get($categoryNameList, $twitter['category_code']);
                $twitter['icon_url'] = array_get($categoryIconList, $twitter['category_code']);
            }

            if ($categoryCode) {
                if (empty($twitterFollow) || TwitterGuard::STATUS_APPROVE != $twitterFollow) {
                    $twitterList = [];
                }
                $ret = [
                    'code' => SYS_STATUS_OK,
                    'data' => [
                        'twitter_list' => $twitterList,
                        'twitter_follow' => $twitterFollow,
                    ],
                ];
            } else {
                $ret = [
                    'code' => SYS_STATUS_OK,
                    'data' => [
                        'twitter_list' => $twitterList,
                    ],
                ];
            }
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = ['code' => SYS_STATUS_ERROR_UNKNOW];
        }

        return $ret;
    }

    public function getPrivateMessageList()
    {
        $condition = $this->request->validate([
            'teacher_id' => 'integer',
            'read' => 'integer:0,1|nullable',
        ]);
        $sessionId = $this->request->header('X-SessionId');
        if (empty($sessionId)) {
            abort(401);
        }
        $session = Cache::get($sessionId);
        if (empty($session)) {
            abort(401);
        }
        $openId = array_get($session, 'open_id');

        try {
            $condition['open_id'] = $openId;
            $privateMessageListData = $this->twitterManager->getCustomerPrivateMessageList($condition);
            $privateMessageList = array_get($privateMessageListData, 'data.private_message_list');
            $privateMessageFollow = array_get($privateMessageListData, 'data.private_message_follow');
            if (empty($privateMessageFollow) || PrivateMessageGuard::STATUS_APPROVE != $privateMessageFollow) {
                $privateMessageList = [];
            }
            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'private_message_list' => $privateMessageList,
                    'private_message_follow' => $privateMessageFollow,
                ],
            ];
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = ['code' => SYS_STATUS_ERROR_UNKNOW];
        }

        return $ret;
    }

    public function postPrivateMessage()
    {
        $privateMessage = $this->request->validate([
            'teacher_id' => 'integer',
            'content' => 'string',
        ]);
        $sessionId = $this->request->header('X-SessionId');
        if (empty($sessionId)) {
            abort(401);
        }
        $session = Cache::get($sessionId);
        if (empty($session)) {
            abort(401);
        }
        $openId = array_get($session, 'open_id');

        $privateMessage['direction'] = PrivateMessage::DIRECTION_UP;
        try {
            $privateMessage['open_id'] = $openId;
            $privateMessageData = $this->twitterManager->postPrivateMessage($privateMessage);
            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'private_message' => array_get($privateMessageData, 'data.private_message'),
                ],
            ];
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = ['code' => SYS_STATUS_ERROR_UNKNOW];
        }

        return $ret;
    }

    public function getLastPrivateMessageGuard()
    {
        $teacher = $this->request->validate([
            'teacher_id' => 'integer',
        ]);

        $sessionId = $this->request->header('X-SessionId');
        if (empty($sessionId)) {
            abort(401);
        }
        $session = Cache::get($sessionId);
        if (empty($session)) {
            abort(401);
        }

        $openId = array_get($session, 'open_id');
        $teacherId = array_get($teacher, 'teacher_id');
        $pmRequestData = $this->twitterManager->getLastPrivateMessageRequest($openId, $teacherId);

        return $pmRequestData;
    }

    public function requestPrivateMessage()
    {
        $teacher = $this->request->validate([
            'teacher_id' => 'integer',
        ]);

        $sessionId = $this->request->header('X-SessionId');
        if (empty($sessionId)) {
            abort(401);
        }
        $session = Cache::get($sessionId);
        if (empty($session)) {
            abort(401);
        }

        $openId = array_get($session, 'open_id');
        $teacherId = array_get($teacher, 'teacher_id');
        try {
            $privateMessageRequestData = $this->twitterManager->createPrivateMessageRequest($teacherId, $openId);
            $privateMessageRequest = array_get($privateMessageRequestData, 'data.private_message_request');
            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'private_message_request' => $privateMessageRequest,
                ],
            ];
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = ['code' => SYS_STATUS_ERROR_UNKNOW];
        }

        return $ret;
    }

    public function like($twitterId)
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

        try {
            $twitterData = $this->twitterManager->likeTwitter($twitterId, $openId);
            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'twitter_id' => $twitterId,
                    'like' => array_get($twitterData, 'data.like'),
                ],
            ];
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = ['code' => SYS_STATUS_ERROR_UNKNOW];
        }

        return $ret;
    }

    public function readPrivateMessage(int $privateMessageId)
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

        try {
            $privateMessageRes = $this->twitterManager->readCustomerPrivateMessage($privateMessageId, $openId);
            $privateMessage = array_get($privateMessageRes, 'data.private_message');

            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'private_message' => $privateMessage,
                ],
            ];
        } catch (Exception $e) {
            Log::error("Read private message fail.", [$e->getMessage()]);
            abort(403);
        }

        return $ret;
    }

}
