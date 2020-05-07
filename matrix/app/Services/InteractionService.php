<?php
namespace Matrix\Services;

use Matrix\Contracts\InteractionContract;

use Matrix\Models\ArticleReply;
use Matrix\Models\Ucenter;
use Matrix\Models\UserGroup;
use Matrix\Models\ArticleLike;
use Matrix\Models\LikeStatistic;
use Matrix\Models\ReplyCnt;
use Matrix\Models\User;

use Matrix\Exceptions\InteractionException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use Exception;
use Auth;
use Log;
use DB;
use Matrix\Exceptions\MatrixException;

class InteractionService extends BaseService implements InteractionContract
{
    protected $ucenter;
    protected $userGroup;
    protected $articleLike;
    protected $likeStatistic;

    public function __construct(Ucenter $ucenter, UserGroup $userGroup, ArticleLike $articleLike, LikeStatistic $likeStatistic)
    {
        $this->ucenter = $ucenter;
        $this->userGroup = $userGroup;
        $this->articleLike = $articleLike;
        $this->likeStatistic = $likeStatistic;
    }

    public function getReplyList(string $type, string $articleId, int $status = 0, string $myOpenId = '', int $lastReplyId = 0, int $pageSize = 0, int $isAllVisible = 1)
    {

        $cond = [
            ['type', '=', $type],
            ['article_id', '=', $articleId],
        ];

        $orCond = [];
        $orCond2 = [];

        if (!empty($lastReplyId)) {
            $cond[] = ['id', '<', $lastReplyId];
        }

        //用户自己的所有评论和对用户评论的所有回复
        if (!empty($myOpenId)) {
            $orCond = $cond;
            $orCond[] = ['open_id', '=', $myOpenId];//用户自己所有的评论
            //$orCond[] = ['status', '<', ArticleReply::STATUS_DENIED];

            $orCond2 = $cond;
            $orCond2[] = ['ref_open_id', '=', $myOpenId];//对用户评论的所有回复
        }

        //已经包含了被审核通过的评论及对应老师回复,因为老师的评论和回复都是审核通过的
        //主要查询非用户自身的评论和回复
        if (!empty($status)) {
            $cond[] = ['status', '=', $status];
            $cond[] = ['is_all_visible', '=', $isAllVisible];//全员可见
        } else {
            $cond[] = ['status', '<', ArticleReply::STATUS_DENIED];
            $cond[] = ['is_all_visible', '=', $isAllVisible];//全员可见
        }

        //以上查询条件取并集
        $model = ArticleReply::where($cond)->orWhere($orCond)->orWhere($orCond2)->orderBy('placed_status', 'desc')->orderBy('id', 'desc');

        if (!empty($pageSize)) {
            $model = $model->take($pageSize);
        }

        $replyList = $model->get();

        return $replyList;
    }

    public function getReplyListOfClient(string $type, string $articleId, int $status = 0, string $myOpenId = '', int $lastReplyId = 0, int $pageSize = 0, int $isAllVisible = 1)
    {
        $lastReplyInfo = ArticleReply::where('id', $lastReplyId)->first();
        if ($lastReplyId === 0 || (int)array_get($lastReplyInfo, 'placed_status') === ArticleReply::PLACED_TOP) {
            $lastReplyIdOfPlaced = $lastReplyId;
            $lastReplyIdOfUnplaced = 0;
            $placedReplyList = $this->getReplyListOfCond($type, $articleId, ArticleReply::PLACED_TOP, $myOpenId, $status, $isAllVisible, 'placed_at', $pageSize, $lastReplyIdOfPlaced);
        } else {
            $lastReplyIdOfUnplaced = $lastReplyId;
            $placedReplyList = [];
        }

        if (count($placedReplyList) < $pageSize) {
            $size = $pageSize - count($placedReplyList);
            $unPlacedReplyList = $this->getReplyListOfCond($type, $articleId, ArticleReply::UN_PLACED_TOP, $myOpenId, $status, $isAllVisible, 'created_at', $size, $lastReplyIdOfUnplaced);
            $replyList = array_merge($placedReplyList, $unPlacedReplyList);
        } else {
            $replyList = $placedReplyList;
        }

        $refReplyIdList = array_column($replyList, 'id');

        $refReplyList = ArticleReply::whereIn('ref_id', $refReplyIdList)
            ->where(function ($query) use ($isAllVisible, $myOpenId) {
                $query->where('is_all_visible', $isAllVisible)
                    ->orWhere('ref_open_id', $myOpenId)
                    ->orWhere('open_id', $myOpenId);
            })
            ->orderBy('created_at')
            ->get()
            ->toArray();

        $ret = [
            'reply_list' => $replyList,
            'ref_reply_list' => $refReplyList,
        ];

        return $ret;
    }

    public function getReplyListOfCond(string $type, string $articleId, int $placedStatus, string $myOpenId, int $status, int $isAllVisible, string $orderParams, int $pageSize, int $lastReplyId)
    {
        $cond = [
            ['type', '=', $type],
            ['article_id', '=', $articleId],
            ['placed_status', '=', $placedStatus],
            ['ref_id', '=', 0]
        ];

        if (!empty($status)) {
            $orWhere = [
                ['status', '=', $status]
            ];
        } else {
            $orWhere = [
                ['status', '<', ArticleReply::STATUS_DENIED]
            ];
        }


        $replyList = ArticleReply::where($cond)
            ->where(function ($query) use ($myOpenId, $orWhere) {
                $query->where([
                        ['open_id', '=', $myOpenId]
                    ])
                    ->orWhere($orWhere);
            });
        if (!empty($lastReplyId)) {
            $lastReplyInfo = ArticleReply::where('id', $lastReplyId)->first()->toArray();
            $date = (string)array_get($lastReplyInfo, $orderParams);
            $replyList = $replyList->where(function ($query) use ($orderParams, $date, $lastReplyId) {
                $query->where([
                        [$orderParams, '<', $date]
                    ])
                    ->orWhere([
                        [$orderParams, '=', $date],
                        ['id', '<', $lastReplyId],
                    ]);
            });
        }

        $replyList = $replyList->orderBy($orderParams, 'desc')
            ->orderBy('id', 'desc')
            ->take($pageSize)
            ->get()
            ->toArray();

        return $replyList;
    }

    public function getReplyListBySend(string $openId, int $index, int $pageSize)
    {
        $cond = [
            'open_id' => $openId,
        ];

        $model = ArticleReply::where($cond);

        if ($index !== 0) {
            $model = $model->where('id', '<', $index);
        }
        

        $replyList = $model->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->take($pageSize)
            ->get();

        return $replyList;
    }

    public function getReplyListByReceive(string $openId, int $index, int $pageSize, int $userId = 0)
    {
        $cond = [
            'ref_open_id' => $openId,
        ];

        if (!empty($userId)) {
            $model = ArticleReply::where(function ($query) use($cond, $userId) {
                $query->where($cond)->orWhere([
                    ['article_author_user_id', '=', $userId],
                    ['ref_id', '=', 0]
                ]);
            });
        } else {
            $model = ArticleReply::where($cond);
        }

        if ($index !== 0) {
            $model = $model->where('id', '<', $index);
        }

        $replyList = $model->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->take($pageSize)
            ->get();

        return $replyList;
    }

    public function getReplyCnt(string $type, string $articleId)
    {
        try {
            $replyCnt = ReplyCnt::where('content_type', $type)->where('content_id', $articleId)->firstOrFail();
            return $replyCnt->cnt;
        } catch (ModelNotFoundException $e) {
            return 0;
        }
    }

    public function reply(string $type, string $articleId, string $articleTitle, int $articleAuthorUserId, string $content, array $userInfo, int $refId = 0, string $refContent = '', string $refOpenId = '', int $isAllVisible = 1, int $forwardToTwitter = 0)
    {
        $replyData = [
            'open_id' => (string)array_get($userInfo, 'user.openId'),
            'session_id' => (string)array_get($userInfo, 'sessionId'),
            'type' => $type,
            'article_id' => $articleId,
            'article_title' => $articleTitle,
            'article_author_user_id' => $articleAuthorUserId,
            'content' => $content,
            'ref_id' => $refId,
            'ref_content' => $refContent,
            'ref_open_id' => $refOpenId,
            'status' => ArticleReply::STATUS_NEW,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'is_all_visible' => $isAllVisible,
            'forward_to_twitter' => $forwardToTwitter,
        ];

        $qyUserId = array_get($userInfo, 'user.qyUserId');
        if (!empty($qyUserId)) {
            $localUc = $this->ucenter->getUcByEnterpriseUserId($qyUserId);
            if (!empty($localUc)) {
                $userId = array_get($localUc, 'user_id');
                $approvedReplyUserList = $this->userGroup->getUserListByCode(UserGroup::USER_GROUP_CODE_APPROVED_REPLY);
                if (!empty($approvedReplyUserList)) {
                    $approvedReplyUserIdList = array_column($approvedReplyUserList, 'user_id');
                    if (in_array($userId, $approvedReplyUserIdList)) {
                        $replyData['status'] = ArticleReply::STATUS_APPROVE;
                        $replyData['examine_user_id'] = $userId;
                        $replyData['examine_at'] = date('Y-m-d H:i:s');

                        if (!empty($refId)) {
                            $refReply = ArticleReply::find($refId);
                            if (empty($refReply)) {
                                throw new InteractionException("回复失败:回复目标 $refId 不存在.", SYS_STATUS_ERROR_UNKNOW);
                            }
                            if ($refReply->status == ArticleReply::STATUS_NEW) {
                                $refReply->status = ArticleReply::STATUS_APPROVE;
                                $refReply->examine_user_id = $userId;
                                $refReply->examine_at = date('Y-m-d H:i:s');
                                $refReply->save();
                            } else {
                                $replyData['status'] = $refReply->status;
                            }
                        }
                    } else {
                        if (!empty($refId) || !empty($refContent) || !empty($refOpenId)) {
                            throw new InteractionException('回复失败:只有牛人可以回复他人评论.', SYS_STATUS_ERROR_UNKNOW);
                        }
                    }
                }
            }
        }

        DB::beginTransaction();
        try {
            $newReply = ArticleReply::create($replyData);
            $replyCnt = ReplyCnt::where('content_type', $type)->where('content_id', $articleId)->first();
            if (empty($replyCnt)) {
                $replyCnt = ReplyCnt::create([
                    'content_type' => $type,
                    'content_id' => $articleId,
                    'cnt' => 1,
                ]);
            } else {
                $replyCnt->increment('cnt');
            }

            DB::commit();
            return $newReply;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("回复失败: {$e->getMessage()}", [$e]);
            throw new InteractionException('回复失败', SYS_STATUS_ERROR_UNKNOW);
        }


    }

    public function like(string $articleId, string $type, string $openId, $udid = '', $sessionId = '', $userType = '')
    {
        $like = $this->articleLike->record($articleId, $type, $openId, $udid, $sessionId, $userType);

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'like' => (int)array_get($like, 'status'),
                'effect_rows' => (int)array_get($like, 'effect_rows'),
            ],
        ];

        return $ret;
    }

    public function likeStatistic(string $articleId, string $type, string $userType, int $isLike)
    {
        $likeStatistic = $this->likeStatistic->likeSum($articleId, $type, $userType, $isLike);

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'like_statistic' => $likeStatistic,
            ],
        ];

        return $ret;
    }

    public function getLikeRecord(string $articleId, string $type,  $openId = '', $udid = '')
    {

        if (!empty($openId) || !empty($udid)) {
            $like = $this->articleLike->getRecord($articleId, $type, $openId, $udid);
        } else {
            $like = 0;
        }


        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'like' => $like,
            ],
        ];

        return $ret;
    }

    public function getLikeRecordList(array $articleIdList, string $type, string $openId = '', string $udid = '')
    {
        if (!empty($openId) || !empty($udid)) {
            $articleLikeList = $this->articleLike->getRecordList($articleIdList, $type, $openId, $udid);
        } else {
            $articleLikeList = [];
        }

        return $articleLikeList;
    }

    public function getLikeSum(string $articleId, string $type)
    {
        $statisticInfo = $this->likeStatistic->getLikeStatisticInfo($articleId, $type);

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'statisticInfo' => $statisticInfo,
            ],
        ];

        return $ret;

    }

    public function getLikeSumList(array $articleIdList, string $type)
    {
        $statisticList = $this->likeStatistic->getLikeStatisticList($articleIdList, $type);
        return $statisticList;
    }

    public function getExamineReplyList(int $pageNo = 1, int $pageSize = 10, int $status = ArticleReply::STATUS_NEW, int $authorUserId = 0, string $articleTitle = '', string $articleType = '')
    {
        $model = ArticleReply::where('status', $status);

        if (!empty($authorUserId)) {
            $model = $model->where('article_author_user_id', $authorUserId);
        }

        if (!empty($articleType)) {
            $model = $model->where('type', $articleType);
        }

        if (!empty($articleTitle)) {
            $model = $model->where('article_title', 'like', "%$articleTitle%");
        }

        $orderBy = $status == ArticleReply::STATUS_NEW ? 'asc' : 'desc';

        $replyList = $model->orderBy('created_at', $orderBy)
            ->skip($pageSize * ($pageNo - 1))->take($pageSize)->get();

        return $replyList;
    }

    public function examineReply(int $replyId, int $operate)
    {
        try {
            $reply = ArticleReply::findOrFail($replyId);

            if (!in_array($operate, [ArticleReply::STATUS_APPROVE, ArticleReply::STATUS_DENIED])) {
                throw new InteractionException('别逗，你这是让我胡来', SYS_STATUS_ERROR_UNKNOW);
            }

            // 以下注释代码为重复审批防御，由于业务需要变更审批策略，需要开放重复审批.
            // -- modify by purehow
//            if ($reply->status !== ArticleReply::STATUS_NEW) {
//                throw new InteractionException('别逗，这条评论已经审过了', SYS_STATUS_ERROR_UNKNOW);
//            }

            $reply->status = $operate;
            $reply->examine_user_id = Auth::user()->id;
            $reply->examine_at = date('Y-m-d H:i:s');
            // 审核拒绝时, 置顶状态变为 未置顶
            if ($operate === ArticleReply::STATUS_DENIED && $reply->placed_status === ArticleReply::PLACED_TOP) {
                $reply->placed_status = ArticleReply::UN_PLACED_TOP;
                $reply->placed_at = date('Y-m-d H:i:s');
            }

            if ($reply->forward_to_twitter === 1) {
                throw new InteractionException('该老师已将评论内容转发至解盘，不可变更状态', INTERACTION_REPLY_FORWARD_TO_TWITTER);
            }

            $reply->save();

            return $reply;
        } catch (ModelNotFoundException $e) {
            throw new InteractionException('找不到这条评论', SYS_STATUS_ERROR_UNKNOW);
        }
    }

    public function examineReplyOfClient(int $replyId, int $operate, int $examineUserId)
    {
        try {
            $reply = ArticleReply::findOrFail($replyId);

            if (!in_array($operate, [ArticleReply::STATUS_APPROVE, ArticleReply::STATUS_DENIED])) {
                throw new InteractionException('传入的状态操作码不正确', SYS_STATUS_ERROR_UNKNOW);
            }

            $reply->status = $operate;
            $reply->examine_user_id = $examineUserId;
            $reply->examine_at = date('Y-m-d H:i:s');
            // 审核拒绝时, 置顶状态变为 未置顶
            if ($operate === ArticleReply::STATUS_DENIED && $reply->placed_status === ArticleReply::PLACED_TOP) {
                $reply->placed_status = ArticleReply::UN_PLACED_TOP;
                $reply->placed_at = date('Y-m-d H:i:s');
            }
            $reply->save();

            return $reply;
        } catch (ModelNotFoundException $e) {
            throw new InteractionException('找不到这条评论', SYS_STATUS_ERROR_UNKNOW);
        }
    }

    public function batchExamineReply(array $replyIdList, int $operate)
    {
        $replyList = ArticleReply::whereIn('id', $replyIdList)->get();

        if (!in_array($operate, [ArticleReply::STATUS_APPROVE, ArticleReply::STATUS_DENIED])) {
            throw new InteractionException('别逗，你这是让我胡来', SYS_STATUS_ERROR_UNKNOW);
        }

        $replyList->each(function ($reply, $key) use ($operate) {
            $reply->status = $operate;
            $reply->examine_user_id = Auth::user()->id;
            $reply->examine_at = date('Y-m-d H:i:s');
            $reply->save();
        });

        return $replyList->count();
    }

    public function getTeacherList()
    {
        $userList = $this->userGroup->getUserListByCode(UserGroup::USER_GROUP_CODE_APPROVED_REPLY);
        $userIdList = array_column($userList, 'user_id');

        $userList = User::whereIn('id', $userIdList)->where('active', 1)->get();

        return $userList;
    }

    public function getExamineReplyCnt(int $status = ArticleReply::STATUS_NEW, int $authorUserId = 0, string $articleTitle = '', string $articleType = '')
    {
        $model = ArticleReply::where('status', $status);

        if (!empty($authorUserId)) {
            $model = $model->where('article_author_user_id', $authorUserId);
        }

        if (!empty($articleType)) {
            $model = $model->where('type', $articleType);
        }

        if (!empty($articleTitle)) {
            $model = $model->where('article_title', 'like', "%$articleTitle%");
        }

        $replyCnt = $model->count();

        return $replyCnt;
    }

    /**
    *获取回复记录
    *
    *@param replay_id integer 回复内容id
    *
    *@return array
    */
    public function getReplyInfo(int $replyId)
    {
        try{
            $replyInfo = ArticleReply::find($replyId);

            return $replyInfo;
        }catch(Exception $e){
            throw new InteractionException('获取评论信息失败:'.$e->getMessage(), $e->getCode());
        }
    }

    /**
    *评论置顶/取消置顶
    *@param replyId integer 评论id
    *@param userId integer 操作人userId
    *@param status integer 审核状态
    *
    *@return array
    */
    public function topReply(int $replyId, int $userId, int $status)
    {
        try{
            $replyInfo = self::getReplyInfo($replyId);

            $placeStatus = array_get($replyInfo, 'placed_status');

            if(empty($placeStatus)){
                $placeStatus = 1;
                $dataTime = date("Y-m-d H:i:s");
            }else{
                $placeStatus = 0;
                $dataTime = null;
            }

            $cond = [
                'placed_status' =>  $placeStatus,
                'placed_at' => $dataTime,
            ];

            //置顶评论默认审核成功
            if( 10 === (int)$status){
                $cond['status'] = 20;
                $cond['examine_at'] = date("Y-m-d H:i:s");
                $cond['examine_user_id'] = $userId;
            }

            $replyInfo = ArticleReply::where(['id' => $replyId])->update($cond);

            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => $replyInfo,
                'msg' =>'success',
            ];

            return $ret;
        }catch(InteractionException $e){
            throw new InteractionException($e->getMessage(), $e->getCode());
        }catch(Exception $e){
            throw new InteractionException('更新置顶状态失败:'.$e->getMessage(), $e->getCode());
        }
    }
}
