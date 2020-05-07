<?php
namespace Matrix\Contracts;

interface TwitterManager extends BaseInterface
{
    public function createTwitterRequest(string $categoryCode, string $openId, string $sourceType);
    public function processTwitterRequest(int $twitterGuardId, int $operate);
    public function addTwitterRequest(array $condition);
    public function createTwitter(array $twitterData);
    public function createrTwitterByKgsRequest(array $twitterDataByKgs);
    public function getTwitterList(array $categoryCodeList, string $openId);
    public function getTwitterApprovedCategoryCodeList(string $openId);
    public function getCustomerPrivateMessageList(array $condition);
    public function postPrivateMessage(array $privateMessageData);
    public function createPrivateMessageRequest(int $teacherId, string $openId, string $sourceType);
    public function processPrivateMessageRequest(int $privateMessageGuardId, int $operate);
    public function getPrivateMessageList(array $condition);
    public function getSessionList(int $teacherId);

    public function getTwitterRequestList(array $cond);
    public function getPrivateMessageRequestList(array $cond);
    public function readManagePrivateMessage(int $privateMessageId);
    public function readCustomerPrivateMessage(int $privateMessageId, $openId);
    public function getLastPrivateMessageRequest(string $openId, int $teacherId);

    public function getPageTwitterList(array $categoryCodeList, int $twitterId, int $pageSize, string $openId, array $operatorUserIdList, bool $hasReferContent, int $month);
    public function getUnfeedTwitterList(array $categoryCodeList);
    public function setTwitterFeed(array $twitterIdList);

    public function twitterRemove(int $twitterId);
    public function getTwitterInfo(int $twitterId);
    public function forward2Twitter(array $refInfo, string $enterpriseUserId, string $business);
    public function getTwitterInfoBySourceId(string $sourceId);
    public function likeTwitter(int $twitterId, string $openId, $udid, $sessionId, $userType);
    public function likeStatistic($twitterId, $type, $userType, $isLike);
    public function getTeacherListByIdList(array $teacherIdList);
    public function getPageTwitterListByRoomId($roomId, $startTime, $endTime, $hasReferContent);
    public function getLikeSumOfTwitter($articleId, $type);
    public function getLikeOfTwitter($articleId, $openId, $udid);

    public function getCategoryCodeList(string $openId, string $categoryGroupCode);

    // 内容管理 -> 动态管理
    public function getTwitterListOfPaging(int $pageNo, int $pageSize, string $categoryCode);
    public function getTwitterCnt(string $categoryCode);

    // 审批管理 -> 动态关注申请
    public function getTwitterRequestListOfPaging(int $pageNo, int $pageSize, array $credentials);
    public function getTwitterRequestCnt(array $credentials);

    // 审批管理 -> 私信聊天申请
    public function getPrivateMessageRequestListOfPaging(int $pageNo, int $pageSize, array $credentials);
    public function getPrivateMessageRequestCnt(array $credentials);
}
