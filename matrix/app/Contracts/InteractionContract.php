<?php
namespace Matrix\Contracts;

interface InteractionContract extends BaseInterface
{
    public function getReplyList(string $type, string $articleId, int $status, string $myOpenId, int $lastReplyId, int $pageSize, int $isAllVisible);
    public function getReplyListOfClient(string $type, string $articleId, int $status, string $myOpenId, int $lastReplyId, int $pageSize, int $isAllVisible);
    public function reply(string $type, string $articleId, string $articleTitle, int $articleAuthorUserId, string $content, array $userInfo, int $refId, string $refContent, string $refOpenId, int $isAllVisible, int $forwardToTwitter);
    public function like(string $articleId, string $type, string $openId, $udid, $sessionId, $userType);
    public function likeStatistic(string $articleId, string $type, string $userType, int $isLike);
    public function getLikeRecord(string $articleId, string $type, $openId, $udid);
    public function getLikeRecordList(array $articleIdList, string $type, string $openId, string $udid);
    public function getLikeSum(string $articleId, string $type);
    public function getLikeSumList(array $articleIdList, string $type);

    public function getExamineReplyList(int $pageNo, int $pageSize, int $status, int $authorUserId, string $articleTitle, string $articleType);
    public function examineReply(int $replyId, int $operate);
    public function batchExamineReply(array $replyIdList, int $operate);
    public function getReplyCnt(string $type, string $articleId);
    public function getTeacherList();
    public function getExamineReplyCnt(int $status, int $authorUserId, string $articleTitle, string $articleType);

    public function getReplyListBySend(string $openId, int $index, int $pageSize);
    public function getReplyListByReceive(string $openId, int $index, int $pageSize, int $userId);

    public function getReplyInfo(int $replyId);
    public function examineReplyOfClient(int $replyId, int $operate, int $examineUserId);
}
