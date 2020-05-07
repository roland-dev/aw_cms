<?php

namespace Matrix\Contracts;

interface CourseManager extends BaseInterface
{
    public function create(string $name, string $code, $description, string $courseSystemCode, string $serviceKey, int $userId, string $backgroundPic, int $sortNo, string $fullTextDescription);
    public function update(int $courseId, string $name, string $code, $description, string $courseSystemCode, int $userId, string $serviceCode, string $backgroundPic, string $fullTextDescription);
    public function getRecordsBeforeModify(array $condition); 
    public function remove(int $courseId);
    public function getOneInfo(int $courseId, int $courseSystemId, string $courseCode);
    public function show();
    public function search($courseName, $courseSystemCode);
    public function getCourseList(string $courseSystemCode);
    public function removeCourseByCode(string $courseSystemCode);
    public function updateRecordByCode(string $newCode, string $oldCode);
    public function getCourseListByCodeList(array $codeList);

    public function getCourseListByCodeListOrderSort(array $codeList);

    public function getCourseListOfPaging(int $pageNo, int $pageSize);
    public function getCourseCnt();

    public function searchCourseList(int $pageNo, int $pageSize, array $credentials);
    public function searchCourseCnt(array $credentials);
}
