<?php
namespace Matrix\Contracts;

interface FeedManager extends BaseInterface
{
    public function syncInFeed(array $feedList);
    public function getFeedListPageination(array $credentials);
    public function getFeedListCount(array $credentials);
    public function eliteFeedList(array $feedIdList, int $operate);
    public function bypassFeedList(array $feedIdList, int $operate, array $typeList);
    public function tjWxSendLogRemove(int $id);
    public function removeFeed(int $feedId);
    public function getFeedInfo(int $feedId);
    public function getFeedInfoByCategoryAndSourceId(string $categoryKey, string $sourceId);
    public function getTjWxSendLogDetail(int $detailId);

    public function getFeedTypeList();
    public function getFeedListOfDay(array $credentials);
    public function getFeedCntOfDay(array $credentials);
}
