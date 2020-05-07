<?php

namespace Matrix\Http\Controllers\Interaction;

use Illuminate\Http\Request;
use Matrix\Http\Controllers\Controller;
use Matrix\Contracts\InteractionContract;
use Matrix\Contracts\UcManager;
use Matrix\Contracts\CustomerManager;
use Matrix\Contracts\UserManager;
use Matrix\Contracts\CategoryManager;
use Matrix\Contracts\TwitterManager;

use Matrix\Models\ArticleReply;

use Illuminate\Validation\ValidationException;
use Matrix\Exceptions\InteractionException;
use Matrix\Exceptions\PermissionException;
use Matrix\Exceptions\MatrixException;
use Matrix\Exceptions\UcException;
use Matrix\Exceptions\UserException;
use Exception;
use Log;

class ContentReplyController extends Controller
{
    //

    const USER_GROUP_CODE_SUPERMAN_TAG = 'teacher_superman_tag';
    const IS_ALL_VISIBLE = 1; //0: 非全员可见, 1:全员可见
    const REPLY_TYPE = 'article_reply';//评论类型

    protected $request;
    protected $user;
    protected $ucenter;
    protected $customer;
    protected $interaction;
    protected $twitter;
    protected $category;

    public function __construct(Request $request,
                                UcManager $ucenter,
                                CustomerManager $customer,
                                UserManager $user,
                                InteractionContract $interaction,
                                TwitterManager $twitter,
                                CategoryManager $category)
    {
        $this->request = $request;
        $this->interaction = $interaction;
        $this->ucenter = $ucenter;
        $this->customer = $customer;
        $this->user = $user;
        $this->twitter = $twitter;
        $this->category = $category;
    }

    //过期接口，但会继续应用于 app < 2.8 的版本
    public function getReplyList()
    {
        $credentials = $this->request->validate([
            'type' => 'required|string',
            'article_id' => 'required|string',
            'last_reply_id' => 'required|integer',
            'page_size' => 'required|integer',
            'udid' => 'nullable|string',
        ]);

        $contentType = array_get($credentials, 'type');
        $contentId = array_get($credentials, 'article_id');
        $udid = array_get($credentials, 'udid');

        $lastReplyId = array_get($credentials, 'last_reply_id');
        $pageSize = array_get($credentials, 'page_size');

        $sessionId = $this->request->header('X-SessionId');
        $openId = '';
        $userId = 0;
        if (!empty($sessionId)) {
            try {
                $ucUserInfo = $this->ucenter->getUserInfoBySessionId($sessionId);
                $openId = (string)array_get($ucUserInfo, 'data.user.openId');

                $qyUserId = (string)array_get($ucUserInfo, 'data.user.qyUserId');
                $userInfo = $this->user->getUserByEnterpriseUserId($qyUserId);
                $userId = (int)array_get($userInfo, 'data.id');
            } catch (UcException $e) {
                $openId = '';
            }
        }

        $replyList = $this->interaction->getReplyList($contentType, $contentId, ArticleReply::STATUS_APPROVE, $openId, $lastReplyId, $pageSize, self::IS_ALL_VISIBLE);

        if (!empty($replyList->toArray())) {
            $customerOpenIdList = $replyList->pluck('open_id'); // 评论人
            $customerRefOpenIdList = $replyList->filter(function ($value, $key) {
                                        return !empty($value);
                                    })->pluck('ref_open_id'); //引用评论人
            $customerAllOpenIdList = collect( [$customerOpenIdList->unique()->all(), $customerRefOpenIdList->unique()->all() ] )->collapse()->unique();
            $customerList = $this->customer->getCustomerList($customerAllOpenIdList->all());
            $customerMap = array_column($customerList, NULL, 'open_id');

            $supermanUserList = $this->user->getUserListByGroupCode(self::USER_GROUP_CODE_SUPERMAN_TAG);
            $supermanUserIdList = array_column($supermanUserList, 'id');
            $supermanUcList = $this->user->getUcListByUserIdList($supermanUserIdList);
            $supermanQyUseridList = (array)array_column($supermanUcList, 'enterprise_userid');

            foreach ($replyList as &$reply) {
                $likeStatus = $this->interaction->getLikeRecord($reply['id'], self::REPLY_TYPE, $openId, $udid);//获取点赞状态
                $likeSum = $this->interaction->getLikeSum($reply['id'], self::REPLY_TYPE);//获取点赞总数
                $reply['is_like'] = (int)array_get($likeStatus, 'data.like');//点赞状态
                $reply['like_sum'] = (int)array_get($likeSum, 'data.statisticInfo.like_sum');//点赞总数

                // ZYAPP-840 修改is_auth字段逻辑
                if (!empty($userId) && $reply['article_author_user_id'] === $userId) {
                    $reply['is_auth'] = 1;
                } else {
                    $reply['is_auth'] = 0;
                }

                $customer = array_get($customerMap, $reply['open_id']);
                if (!empty($customer)) {
                    $reply['nickname'] = $customer['name'];
                    if (!empty($openId) && $openId == $customer['open_id']) {
                        $reply['nickname'] .= '(我)';
                    }
                    $reply['icon_url'] = $customer['icon_url'];

                    if (!empty($supermanQyUseridList) && in_array($customer['qy_userid'], $supermanQyUseridList)) {
                        $reply['teacher_qy_userid'] = $customer['qy_userid'];
                        $reply['is_teacher'] = 1;
                    } else {
                        $reply['is_teacher'] = 0;
                    }
                }

                if (!empty($reply['ref_open_id'])) {
                    $refCustomer = array_get($customerMap, $reply['ref_open_id']);
                    $reply['ref_nickname'] = $refCustomer['name'];
                    if (!empty($openId) && $openId == $refCustomer['open_id']) {
                        $reply['ref_nickname'] .= '(我)';
                        $reply['is_auth'] = 1;//是否是作者 0:否，1:是
                    } else {
                        $reply['is_auth'] = 0;//是否是作者 0:否，1:是
                    }
                    $reply['ref_icon_url'] = $refCustomer['icon_url'];

                    if (!empty($supermanQyUseridList) && in_array($refCustomer['qy_userid'], $supermanQyUseridList)) {
                        $reply['ref_is_teacher'] = 1;
                        $reply['ref_teacher_qy_userid'] = $refCustomer['qy_userid'];
                    } else {
                        $reply['ref_is_teacher'] = 0;
                    }
                }

                $reply['send_time_text'] = '';
                if (!empty($reply['created_at'])) {
                    $sendTimeStamp = strtotime($reply['created_at']);
                    $nowTime = time();
                    $diffTime = $nowTime - $sendTimeStamp;
                    if ($diffTime < 60) { // 一分钟内
                        $reply['send_time_text'] = '刚刚';
                    } elseif ($diffTime <= 3600) { // 一小时内
                        $reply['send_time_text'] = intval($diffTime / 60).'分钟前';
                    } elseif ($diffTime <= 86400) { // 一天内
                        $reply['send_time_text'] = intval($diffTime / 3600).'小时前';
                    } else { // 大于一天
                        $oneYearAgoTime = strtotime('-1 year');
                        if ($sendTimeStamp >= $oneYearAgoTime) { // 一年之内
                            $reply['send_time_text'] = date('m月d日 H:i', $sendTimeStamp);
                        } else { // 一年以上
                            $reply['send_time_text'] = date('Y年m月d日 H:i', $sendTimeStamp);
                        }
                    }
                }

                unset($reply['session_id']);
            }
              //$replyCnt = $this->interaction->getReplyCnt($contentType, $contentId);
        } else {
              //$replyCnt = 0;
        }

        $ret = [
            'code' => SYS_STATUS_OK,
            'msg' => '',
            'data' => [
                //'reply_cnt' => $replyCnt,
                'reply_list' => $replyList,
            ],
        ];

        return $ret;
    }

    /**
    *获取评论列表 (应用于app >= 2.8版本)
    *
    *@return $ret array
    */
    public function getReplyNewList()
    {
        $credentials = $this->request->validate([
            'type' => 'required|string',
            'article_id' => 'required|string',
            'last_reply_id' => 'required|integer',
            'page_size' => 'required|integer',
            'udid' => 'nullable|string',
        ]);

        $contentType = array_get($credentials, 'type');
        $contentId = array_get($credentials, 'article_id');
        $udid = array_get($credentials, 'udid');

        $lastReplyId = array_get($credentials, 'last_reply_id');
        $pageSize = array_get($credentials, 'page_size');

        $sessionId = $this->request->header('X-SessionId');
        $openId = '';
        $userId = 0;
        if (!empty($sessionId)) {
            try {
                $ucUserInfo = $this->ucenter->getUserInfoBySessionId($sessionId);
                $openId = (string)array_get($ucUserInfo, 'data.user.openId');

                $qyUserId = (string) array_get($ucUserInfo, 'data.user.qyUserId');
                $userInfo = $this->user->getUserByEnterpriseUserId($qyUserId);
                $userId = (int)array_get($userInfo, 'data.id');
            } catch (UcException $e) {
                $openId = '';
            }
        }

        $replyListData = $this->interaction->getReplyListOfClient($contentType, $contentId, ArticleReply::STATUS_APPROVE, $openId, $lastReplyId, $pageSize, self::IS_ALL_VISIBLE);
        $replyList = array_get($replyListData, 'reply_list');
        $this->getReplyInfoList($replyList, $openId, $userId);
        $refReplyList = array_get($replyListData, 'ref_reply_list');
        $this->getReplyInfoList($refReplyList, $openId, $userId);
        foreach ($replyList as &$reply) {
            foreach ($refReplyList as $refReply) {
                if ($reply['id'] === $refReply['ref_id']) {
                    $reply['ref_content_list'][] = $refReply;
                }
            }
        }

        $ret = [
            'code' => SYS_STATUS_OK,
            'msg' => '',
            'data' => [
                //'reply_cnt' => $replyCnt,
                //'reply_list' => $replyList,
                'reply_list' => $replyList,
            ],
        ];

        return $ret;
    }

    public function getReplyInfoList (array &$replyList, string $openId, int $userId) {
        if (!empty($replyList)) {
            $customerOpenIdList = array_column($replyList, 'open_id');
            $customerRefOpenIdList = array_column($replyList, 'ref_open_id');
            $customerAllOpenIdList = array_merge($customerOpenIdList, $customerRefOpenIdList);
            $customerAllOpenIdList = array_unique($customerAllOpenIdList);

            // 获取对应的点赞状态
            $replyIdList = array_column($replyList, 'id');
            $replyLikeList = $this->interaction->getLikeRecordList($replyIdList, self::REPLY_TYPE, $openId);
            $replyIdOfLike = array_column($replyLikeList, 'article_id');

            // 获取对应评论的点赞总数
            $replyLikeSumList = $this->interaction->getLikeSumList($replyIdList, self::REPLY_TYPE);
            $likeSumOfReplyId = array_column($replyLikeSumList, NULL, 'article_id');

            $customerList = $this->customer->getCustomerList($customerAllOpenIdList);
            $customerMap = array_column($customerList, NULL, 'open_id');

            $supermanUserList = $this->user->getUserListByGroupCode(self::USER_GROUP_CODE_SUPERMAN_TAG);
            $supermanUserIdList = array_column($supermanUserList, 'id');
            $supermanUcList = $this->user->getUcListByUserIdList($supermanUserIdList);
            $supermanQyUseridList = array_column($supermanUcList, 'enterprise_userid');

            foreach ($replyList as &$reply) {
                $customer = array_get($customerMap, $reply['open_id']);
                if (!empty($customer)) {
                    $reply['nickname'] = $customer['name'];
                    if (!empty($openId) && $openId == $customer['open_id']) {
                        $reply['nickname'] .= '(我)';
                    }
                    $reply['icon_url'] = $customer['icon_url'];

                    if (!empty($supermanQyUseridList) && in_array($customer['qy_userid'], $supermanQyUseridList)) {
                        $reply['is_teacher'] = 1;
                        $reply['teacher_qy_userid'] = $customer['qy_userid'];
                    } else {
                        $reply['is_teacher'] = 0;
                    }
                }

                // ZYAPP-840 修改is_auth字段逻辑
                if (!empty($userId) && $reply['article_author_user_id'] === $userId) {
                    $reply['is_auth'] = 1;
                } else {
                    $reply['is_auth'] = 0;
                }

                // 本人是否店在哪
                if (in_array($reply['id'], $replyIdOfLike)) {
                    $reply['is_like'] = 1;
                } else {
                    $reply['is_like'] = 0;
                }

                // 点赞总数
                if (empty($likeSumOfReplyId[$reply['id']])) {
                    $reply['like_sum'] = 0;
                } else {
                    $likeSum = (int)array_get($likeSumOfReplyId[$reply['id']], 'like_sum');
                    $reply['like_sum'] = $likeSum;
                }

                if (!empty($reply['ref_open_id'])) {
                    $refCustomer = array_get($customerMap, $reply['ref_open_id']);
                    $reply['ref_nickname'] = $refCustomer['name'];
                    if (!empty($openId) && $openId == $refCustomer['open_id']) {
                        $reply['ref_nickname'] .= '(我)';
                    }
                    $reply['ref_icon_url'] = $refCustomer['icon_url'];

                    if (!empty($supermanQyUseridList) && in_array($refCustomer['qy_userid'], $supermanQyUseridList)) {
                        $reply['ref_is_teacher'] = 1;
                        $reply['ref_teacher_qy_userid'] = $refCustomer['qy_userid'];
                    } else {
                        $reply['ref_is_teacher'] = 0;
                    }
                }

                $reply['send_time_text'] = '';
                if (!empty($reply['created_at'])) {
                    $sendTimeStamp = strtotime($reply['created_at']);
                    $nowTime = time();
                    $diffTime = $nowTime - $sendTimeStamp;
                    if ($diffTime < 60) { // 一分钟内
                        $reply['send_time_text'] = '刚刚';
                    } elseif ($diffTime <= 3600) { // 一小时内
                        $reply['send_time_text'] = intval($diffTime / 60).'分钟前';
                    } elseif ($diffTime <= 86400) { // 一天内
                        $reply['send_time_text'] = intval($diffTime / 3600).'小时前';
                    } else { // 大于一天
                        $oneYearAgoTime = strtotime('-1 year');
                        if ($sendTimeStamp >= $oneYearAgoTime) { // 一年之内
                            $reply['send_time_text'] = date('m月d日 H:i', $sendTimeStamp);
                        } else { // 一年以上
                            $reply['send_time_text'] = date('Y年m月d日 H:i', $sendTimeStamp);
                        }
                    }
                }
                unset($reply['session_id']);
            }
        }
    }


    public function postReply()
    {
        $credentials = $this->request->validate([
            'type' => 'required|string',
            'article_id' => 'required|string',
            'article_title' => 'required|string',
            'article_author_user_id' => 'required|integer',
            'content' => 'required|string',
            'ref_id' => 'integer|nullable',
            'ref_content' => 'string|nullable',
            'ref_open_id' => 'string|nullable',
            'is_all_visible' => 'integer|nullable',
        ]);

        try {
            $sessionId = $this->request->header('X-SessionId');
            $ucUserInfo = $this->ucenter->getUserInfoBySessionId($sessionId, 'default', true);

            $nickName = array_get($ucUserInfo, 'data.user.nickName');
            if (empty($nickName)) {
                throw new InteractionException('回复失败，没有设置昵称', INTERACTION_NICKNAME_NOT_SET);
            }

            //只有发布该节目视频/课程视频/文章内容的老师，才能回复评论区中用户的评论
            if(!empty($credentials['ref_id'])){//判断是否为回复，而不是评论
                //获取登录人user_id
                $qyUserId = (string)array_get($ucUserInfo, 'data.user.qyUserId');
                $userInfo = $this->user->getUserByEnterpriseUserId($qyUserId);
                $userId = array_get($userInfo, 'data.id');

                //获取文章作者Id
                $replyInfo = $this->interaction->getReplyInfo($credentials['ref_id']);
                $authorId = array_get($replyInfo, 'article_author_user_id');

                // 在判定是否是内容生成老师，有权限回复该评论
                if((int)$authorId !== (int)$userId){
                    throw new PermissionException('不是内容作者无权限回复该评论', USER_OPERATE_PERMISSION_DENY);
                }
            }

            $customerData = array_get($ucUserInfo, 'data.user');

            $customerData = [
                'open_id' => (string)array_get($ucUserInfo, 'data.user.openId'),
                'code' => (string)array_get($ucUserInfo, 'data.user.customerCode'),
                'qy_userid' => (string)array_get($ucUserInfo, 'data.user.qyUserId'),
                'name' => (string)array_get($ucUserInfo, 'data.user.name'),
                'mobile' => (string)array_get($ucUserInfo, 'data.user.mobile'),
                'nickname' => (string)array_get($ucUserInfo, 'data.user.nickName'),
                'icon_url' => (string)array_get($ucUserInfo, 'data.user.iconUrl'),
            ];

            $customer = $this->customer->updateCustomer($customerData);

            $type = $this->request->input('type');
            $articleId = $this->request->input('article_id');
            $articleTitle = $this->request->input('article_title');
            $articleAuthorUserId = $this->request->input('article_author_user_id');
            $content = $this->request->input('content');
            $refId = (int)$this->request->input('ref_id');
            $refContent = (string)$this->request->input('ref_content');
            $refOpenId = (string)$this->request->input('ref_open_id');

            if(!empty($refId)){
                $isAllVisible = (int)$this->request->input('is_all_visible');
            }else{
                $isAllVisible = 1;
            }

            $newReply = $this->interaction->reply($type, $articleId, $articleTitle, $articleAuthorUserId, $content, array_get($ucUserInfo, 'data'), $refId, $refContent, $refOpenId, $isAllVisible);

            unset($newReply['session_id']);

            $ret = [
                'code' => SYS_STATUS_OK,
                'msg' => '回复成功',
                'data' => [
                    'reply' => $newReply,
                ],
            ];

            if (!empty($refId) || !empty(array_get($credentials, 'article_author_user_id'))) {
                try {
                    if (empty($refId)) {
                        $userInfo = $this->user->getUserInfo(array_get($credentials, 'article_author_user_id'));
                        $qyUserId = array_get($userInfo, 'ucInfo.enterprise_userid');
                        $userIds = [$qyUserId];
                    } else {
                        $userIds = [$refOpenId];
                    }

                    // 针对 解盘 进行处理 解盘类型数据没有 title
                    if ($type == 'twitter') {
                        $twitterInfo = $this->twitter->getTwitterInfo($articleId);
                        $categoryCode = (string)array_get($twitterInfo, 'category_code');
                        $categoryInfo = $this->category->getCategoryInfoByCode($categoryCode);
                        $articleTitle = (string)array_get($categoryInfo, 'name');
                    }
    
                    $messageFormData = [
                        'appCode' => 62,
                        'boxCode' => '',
                        'boxIconUrl' => '',
                        'boxTitle' => '',
                        'title' => '点评：' . $articleTitle,
                        'opTitle' => '点评',
                        'content' => array_get($credentials, 'content'),
                        'msgKind' => 'commentReply',
                        'toAll' => 0,
                        'traceId' => array_get($newReply, 'id'),
                        'traceType' => array_get($credentials, 'type'),
                        'userIds' => $userIds,
                        'sender' => $nickName,
                        'senderUserId' => array_get($ucUserInfo, 'data.user.qyUserId'),
                    ];

                    $this->ucenter->sendMessageToUc($messageFormData);
                } catch (UcException $e) {
                    Log::error('消息同步到UC 出错'.$e->getMessage(), $e->getCode());
                    $ret['error'] = '消息同步到 UC 出错';
                }
            }
        } catch (PermissionException $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = [
                'code' => $e->getCode(),
                'msg' => $e->getMessage(),
            ];
        } catch (MatrixException $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = [
                'code' => $e->getCode(),
                'msg' => $e->getMessage(),
            ];
        } catch (Exception $e) {
            Log::error('回复失败', [$e]);
            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => '回复失败',
            ];
        }

        return $ret;
    }

    /**
    *评论置顶接口
    *
    *@param reply_id integer 评论id
    *@return array
    */
    public function topReplace()
    {
        try{
            $reqData = $this->request->validate([
                'reply_id' => 'required|integer',
            ]);

            $replyId = array_get($reqData, 'reply_id');

            $sessionId = $this->request->header('X-SessionId');

            if(empty($sessionId)){
                $sessionId = $this->request->cookie('X-SessionId');
            }

            if(empty($sessionId)){
                throw new InteractionException('X-SessionId 不能为空', USER_X_SESSIONID_NOT_VALIDATE);
            }

            $ucUserInfo = $this->ucenter->getUserInfoBySessionId($sessionId);
            $qyUserId = (string)array_get($ucUserInfo, 'data.user.qyUserId');

            //获取登录人user_id
            $userInfo = $this->user->getUserByEnterpriseUserId($qyUserId);
            $userId = array_get($userInfo, 'data.id');

            //获取文章作者Id
            $replyInfo = $this->interaction->getReplyInfo($replyId);
            $authorId = array_get($replyInfo, 'article_author_user_id');
            $status = array_get($replyInfo, 'status');

            if((int)$authorId !== (int)$userId){
                throw new PermissionException('当前用户没有权限执行该操作', USER_OPERATE_PERMISSION_DENY);
            }

            $topReplyRep = $this->interaction->topReply($replyId, $userId, $status);

            $replyInfo = $this->interaction->getReplyInfo($replyId);

            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => $replyInfo,
                'msg' => 'success',
            ];
        }catch(ValidationException $e){//入参不合法
            $ret = [
                'code' => $e->status,
                'data' => '',
                'msg' => $e->errors(),
            ];
        }catch(InteractionException $e){//功能不合法
            $ret = [
                'code' => $e->getCode(),
                'data' => '',
                'msg' => $e->getMessage(),
            ];
        }catch(PermissionException $e){//权限异常
            $ret = [
                'code' => $e->getCode(),
                'data' => '',
                'msg' => $e->getMessage(),
            ];
        }catch(UcException $e){//uc异常
            $ret = [
                'code' => $e->getCode(),
                'data' => '',
                'msg' => $e->getMessage(),
            ];
        }catch(UserException $e){//查询用户信息
            $ret = [
                'code' => $e->getCode(),
                'data' => '',
                'msg' => $e->getMessage(),
            ];
        }catch(Exception $e){//系统位置错误
            $ret = [
                'code' => $e->getCode(),
                'data' => '',
                'msg' => $e->getMessage(),
            ];
        }

        return $ret;
    }
}
