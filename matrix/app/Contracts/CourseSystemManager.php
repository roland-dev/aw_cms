<?php

namespace Matrix\Contracts;

interface CourseSystemManager extends BaseInterface
{
    public function create(string $name, string $code, int $userId, int $sortNo, string $categoryCode);
    public function getRecordsBeforeModify(array $condition);
    public function update(int $courseSystemId, string $name, string $code, int $userId, string $categoryCode);
    public function remove(int $courseSystemId);
    public function getOneInfo(int $courseSystemId);
    public function show();
    public function checkCourseSystemCodeUnique(string $courseSystemCode);
    public function getCourseSystemList();
    public function getCourseSystemByCode(string $courseSystemCode);

    public function getCourseSystemListOfPaging(int $pageNo, int $pageSize);
    public function getCourseSystemCnt();
}
