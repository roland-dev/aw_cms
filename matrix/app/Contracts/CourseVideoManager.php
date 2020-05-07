<?php

namespace Matrix\Contracts;

interface CourseVideoManager extends BaseInterface
{
    //public function create(string $imagePath, string $thumbnailPath, string $thumbnailPreviewPath, int $isDisplay, int $videoSigninId, array $pvUv, string $courseCode, int $sortNo, $tag);
    public function create(string $thumbnailPreviewPath, int $isDisplay, int $videoSigninId, array $pvUv, string $courseCode, int $sortNo, $tag, $demoUrl, $adGuide);
    //public function update(int $courseVideoId, string $imagePath, string $thumbnailPath, string $thumbnailPreviewPath, int $isDisplay, int $videoSigninId, array $pvUv, $tag);
    public function update(int $courseVideoId, string $thumbnailPath, int $isDisplay, int $videoSigninId, array $pvUv, $tag, $demoUrl, $adGuide);
    public function destory(int $courseVideoId);
    public function getPvUvCount(string $videoKey);
    public function getCourseVideoInfo(int $courseVideoId, int $videoSigninId);
    public function getCourseVideoList(string $courseCode, array $videoList);
    public function getRecordsBeforeModify(array $condition);
    public function removeCourseVideoByCode(array $courseCodeList);
    public function removeCourseVideoBySingleCode(string $courseCode);
    public function updateRecordByCode(string $newCode, string $oldCode);
    public function getVideoSigninIdList(array $courseCodeList);
    public function getVideoListByCode(string $courseCode);

    public function getCourseVideoListOfPaging(int $pageNo, int $pageSize, string $courseCode);
    public function getCourseVideoCnt(string $courseCode);
}
