<?php
namespace Matrix\Contracts;

interface TalkshowContract extends BaseInterface
{
    /**
     * Video vendor service
     */
    public function getVideoVendorList(int $pageNo, int $pageSize, array $credentials);
    public function createVideoVendor(array $credentials);
    public function updateVideoVendor(string $vendorCode, array $credentials);
    public function removeVideoVendor(string $vendorCode);
    public function getVideoVendor(string $vendorCode);

    /**
     * Live room service
     */
    public function getLiveRoomList(int $pageNo, int $pageSize);
    public function getLiveRoomCnt();
    public function createLiveRoom(array $credentials);
    public function updateLiveRoom(string $roomCode, array $credentials);
    public function removeLiveRoom(string $roomCode);
    public function getLiveRoom(string $roomCode);

    /**
     * Static Talkshow service
     */
    public function getStaticTalkshowList(int $pageNo, int $pageSize);
    public function getStaticTalkshowCnt();
    public function createStaticTalkshow(array $credentials);
    public function updateStaticTalkshow(string $staticTalkshowId, array $credentials);
    public function removeStaticTalkshow(string $staticTalkshowId);
    public function getStaticTalkshow(string $staticTalkshowId);

    /**
     * Talkshow service
     */
    public function getTalkshowList(int $pageNo, int $pageSize, array $credentials);
    public function getTalkshowCnt(array $credentials);
    public function createTalkshow(array $credentials);
    public function updateTalkshow(string $talkshowCode, array $credentials);
    public function removeTalkshow(string $talkshowCode);
    public function getTalkshow(string $talkshowCode);
    public function importTalkshowList(array $credentials);
    public function getTalkshowByTime(string $startTime, string $endTime);
    public function operateTalkshow(string $talkshowCode, int $operate);

    /**
     * Discuss service
     */
    public function getDiscussList(int $pageNo, int $pageSize, array $credentials);
    public function getDiscussCnt(array $credentials);
    public function examineDiscuss(int $discussId, int $operate);
    public function createDiscuss(array $credentials);

    /**
     * For App Service
     */

    // Get Predict Info
    public function getPredictInfo();

    // Get live discuss list
    public function getLiveDiscussListApp(int $lastDiscussId, int $pageSize, array $credentials, string $myOpenId);

    // H5 Get Last Talkshow
    public function getLastTalkshow();
}
