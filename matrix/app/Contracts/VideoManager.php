<?php

namespace Matrix\Contracts;

interface VideoManager extends BaseInterface
{
    public function create(string $url, int $author, string $categoryCode, string $title, string $publishedAt, $description, int $isPublicPlayer);
    public function show(array $categoryCodeList);
    public function destory(int $videoId);
    public function update(int $userId, int $videoId, string $url, int $author, string $categoryCode, string $title, string $publishedAt,  $description);
    public function getOneSigninDetail(int $videoId);
    public function getCategoriesList();
    public function getCategoryListByCategoryGroupCode(string $videoSigninGroupCode);
    public function getTeachersList(string $categoryCode);
    public function getRecordsBeforeModify(array $condition);
    public function searchVideoSignin($categoryCode, $author, $title, $sTime, $eTime, $categoryCodeList);
    public function removeVideoSigninById(array $videoSigninIdList);
    public function getVideoListByIdListAndUserId(array $videoIdList, int $userId);
    public function getHistoryData(int $detailId);
    public function getVideoListByVideoIds(array $videoIds);

    public function getDapanfenxiVideoList(array $categoryCodeList, int $showOfDay);

    public function getVideoSigninList(int $pageNo, int $pageSize, array $categoryCodeList);
    public function getVideoSigninCnt(array $categoryCodeList);

    public function searchVideoSigninList(int $pageNo, int $pageSize, array $categoryCodeList, array $credentials);
    public function searchVideoSigninCnt(array $categoryCodeList, array $credentials);
}
