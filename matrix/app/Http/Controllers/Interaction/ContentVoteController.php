<?php

namespace Matrix\Http\Controllers\Interaction;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Matrix\Exceptions\InteractionException;
use Matrix\Exceptions\PermissionException;
use Matrix\Exceptions\MatrixException;
use Matrix\Contracts\UcManager;
use Matrix\Contracts\UserManager;
use Matrix\Contracts\InteractionContract;
use Matrix\Contracts\TalkshowContract;
use Matrix\Http\Controllers\Controller;
use Log;
use Exception;

class ContentVoteController extends Controller
{
    const REPLY_LIKE_TYPE = 'article_reply';
    const LIVE_LIKE = 'live_discuss';
    //
    protected $request;
    protected $ucManager;
    protected $interactionContract;
    protected $user;
    protected $talkshow;

    public function __construct(Request $request, UcManager $ucManager, InteractionContract $interactionContract, UserManager $user, TalkshowContract $talkshow)
    {
        $this->request = $request;
        $this->ucManager = $ucManager;
        $this->interactionContract = $interactionContract;
        $this->user = $user;
        $this->talkshow = $talkshow;
    }

    /*
    *点赞接口
    *@param type string 记录类型
    *@param article_id integer 记录id  
    *@param udid string 内容点赞识别app字端  
    */
    public function putVote()
    {
        try {
            $reqData = $this->request->validate([
                'type' => 'required|string',
                'article_id' => 'required|string',
                'udid' => 'required|string',
            ]);

            $type = array_get($reqData, 'type');

            $articleId = array_get($reqData, 'article_id');

            $udid = array_get($reqData, 'udid');

            $sessionId = (string)$this->request->header('X-SessionId');

            $openId = '';

            $userType = '';

            if(!empty($sessionId)){
                $currentUserInfo = $this->ucManager->getUserInfoBySessionId($sessionId);

                $openId = (string)array_get($currentUserInfo, 'data.user.openId');

                $userType = (string)array_get($currentUserInfo, 'data.user.roleCode');
            }

            $articleData = $this->interactionContract->like($articleId, $type,  $openId, $udid, $sessionId, $userType);

            $likeStatus = array_get($articleData, 'data.like');
            $effectRows = array_get($articleData, 'data.effect_rows');

            if (!empty($effectRows)) {
                $likeStatistic = $this->interactionContract->likeStatistic($articleId, $type, $userType, $likeStatus);
            }

            $likeSum = $this->interactionContract->getLikeSum($articleId, $type);

            $resp[] = [
                'open_id' => $openId,
                'article_id' => $articleId,
                'type' => $type,
                'is_like' => $likeStatus,
                'like_sum' => (int)array_get($likeSum, 'data.statisticInfo.like_sum'),
            ];

            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => $resp,
            ];

        } catch (ValidationException $e) {
            Log::error($e->getMessage(), [$e]);

            $errors = array_column($e->errors(), 0);
            $errorStr = implode("\n", $errors);

            $ret = [
                'code' => OPEN_API_PARAMS_ERROR,
                'msg' => $errorStr,
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
                'msg' => $e->getMessage(),
            ];
        }

        return $ret;
    }

    /**
    *UGC 点赞接口
    *
    *@param reply_id integer 评论id
    *@param type string 记录类型
    *@param udid string 内容点赞识别app字端  
    *
    *@param ret array
    */
    public function ugcPutVote()
    {
        try{
            $reqData = $this->request->validate([
                'record_id' => 'required|integer',
                'type' => 'required|string',//评论点赞:article_reply 直播互动: live_vote
                'udid' => 'nullable|string',
            ]);

            $replyId = array_get($reqData, 'record_id');
            //$type = empty(array_get($reqData, 'type')) ? self::REPLY_LIKE_TYPE : array_get($reqData, 'type');//可以传入评论类型,默认为
            $type = array_get($reqData, 'type');
            $udid = empty(array_get($reqData, 'udid')) ? '' : array_get($reqData, 'udid');

            $openId = '';
            $userType = '';
            $sessionId = '';

            $sessionId = (string)$this->request->header('X-SessionId');

            if(empty($sessionId)){
                $sessionId = $this->request->cookie('X-SessionId');
            }

            if($type === self::REPLY_LIKE_TYPE){//如果是评论类型点赞
                //获取评论信息
                $replyInfo = $this->interactionContract->getReplyInfo($replyId);
                $status = array_get($replyInfo, 'status');//审核状态
                $isAllVisible = array_get($replyInfo, 'is_all_visible');//是否全员可见
                $articleAuthorUserId = array_get($replyInfo, 'article_author_user_id');//文章作者
                $userOpenId = array_get($replyInfo, 'open_id');//用户open_id
                $refOpenId = array_get($replyInfo, 'ref_open_id');// ref_open_id

                //匿名用户：能看到的所有审核通过的评论+所有回复,匿名用户不能提交评论
                if(empty($sessionId) && !empty($udid)){
                    if( 20 !== (int)$status){//不是审核通过的评论
                        throw new PermissionException('匿名用户不能对未审核通过评论点赞', USER_OPERATE_PERMISSION_DENY);
                    }

                    if( 1 !== (int)$isAllVisible){//不是审核通过的评论
                        throw new PermissionException('匿名用户不能对非全员可见回复进行点赞', USER_OPERATE_PERMISSION_DENY);
                    }
                }else if(empty($sessionId) && empty($udid)){
                    throw new PermissionException('匿名用户都不是，udid和sessionId都不存在', USER_OPERATE_PERMISSION_DENY);
                }
            }else if($type === self::LIVE_LIKE){//如果是直播互动类型点赞
                if(empty($sessionId)){//直播互动点赞不存在匿名用户
                    throw new PermissionException('直播互动匿名用户不能进行点赞', USER_OPERATE_PERMISSION_DENY);
                }

                $discussInfo = $this->talkshow->getDiscussInfo($replyId);
                $userOpenId = array_get($discussInfo, 'open_id');//评论人的open_id
                $status = array_get($discussInfo, 'status');//评论当前状态
            }else{
                throw new InteractionException('当前传入点赞类型暂不支持', USER_OPERATE_PERMISSION_DENY);
            }

            //正式用户：能看到的所有审核通过的评论+自己提交的评论（待审核、审核通过、审核失败）+所有回复
            //老师提交的评论和回复，都是审核通过的状态
            if(!empty($sessionId)){
                $currentUserInfo = $this->ucManager->getUserInfoBySessionId($sessionId);
                $openId = (string)array_get($currentUserInfo, 'data.user.openId');
                $userType = (string)array_get($currentUserInfo, 'data.user.roleCode');

                if( 20 !== (int)$status && $userOpenId !== $openId){//不是审核通过并且不是自己的评论,  该条评论没有审核通过， 并且不是评论当事人点赞
                    throw new PermissionException('用户不能对未审核通过并且用户非评论人评论点赞', USER_OPERATE_PERMISSION_DENY);
                }

                if($type === self::REPLY_LIKE_TYPE){//如果是评论类型点赞
                    if( 1 !== (int)$isAllVisible && $userOpenId !== $openId && $refOpenId !== $openId){//不是全员可见并且不是自己的评论
                        throw new PermissionException('用户不能对非全员可见并且用户非评论人回复点赞', USER_OPERATE_PERMISSION_DENY);
                    }
                }
            }

            $articleData = $this->interactionContract->like($replyId, $type,  $openId, $udid, $sessionId, $userType);

            $likeStatus = array_get($articleData, 'data.like');
            $effectRows = array_get($articleData, 'data.effect_rows');

            if (!empty($effectRows)) {
                $likeStatistic = $this->interactionContract->likeStatistic($replyId, $type, $userType, $likeStatus);
            }

            $likeSum = $this->interactionContract->getLikeSum($replyId, $type);

            $resp[] = [
                'open_id' => $openId,
                'reply_id' => $replyId,
                'type' => $type,
                'is_like' => $likeStatus,
                'like_sum' => (int)array_get($likeSum, 'data.statisticInfo.like_sum'),
            ];

            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => $resp,
            ];
        }catch(ValidationException $e){//入参不合法
            $ret = [
                'code' => $e->status,
                'data' => '',
                'msg' => $e->errors(),
            ];
        }catch (PermissionException $e){//无权限
            $ret = [
                'code' => $e->getCode(),
                'data' => '',
                'msg' => $e->getMessage(),
            ];
        }catch (InteractionException $e){//
            $ret = [
                'code' => $e->getCode(),
                'data' => '',
                'msg' => $e->getMessage(),
            ];
        }catch (MatrixException $e){//
            $ret = [
                'code' => $e->getCode(),
                'data' => '',
                'msg' => $e->getMessage(),
            ];
        }catch (Exception $e) {//系统异常捕获
            $ret = [
                'code' => $e->getCode(),
                'data' => '',
                'msg' => $e->getMessage(),
            ];
        }

        return $ret;
    }

    /*
    *点赞接口
    *@param type string 记录类型
    *@param article_id integer 记录id  
    *@param udid string 内容点赞识别app字端  
    */
    public function likeStatistic(string $type, string $articleId, string $udid)
    {
        $openId = '';

        $sessionId = '';

        $sessionId = $this->request->header('X-SessionId');

        try {
            if(!empty($sessionId)){
                $currentUserInfo = $this->ucManager->getUserInfoBySessionId($sessionId);

                $openId = (string)array_get($currentUserInfo, 'data.user.openId');
            }

            $ret = [];

            $isLike = $this->interactionContract->getLikeRecord($articleId, $type, $openId, $udid);

            $likeSum = $this->interactionContract->getLikeSum($articleId, $type);

            $resp[] = [
                'is_like' => array_get($isLike, 'data.like'),
                'like_sum' => empty(array_get($likeSum, 'data.statisticInfo.like_sum')) ? 0 : array_get($likeSum, 'data.statisticInfo.like_sum'),
            ];

            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => $resp,
            ];
        }catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => $e->getMessage(),
            ];
        }

        return $ret;
    }
}
