<?php

namespace Matrix\Contracts;

interface TeacherManager extends BaseInterface
{
    public function getAllTabList();
    public function getTeacherFollowCount(int $teacherUserId, string $business);
    public function getTeacherFollow(int $teacherUserId, string $openId, string $business);
    public function getFollowListByOpenId(string $openId, string $business);
    public function getTeacherTabList(int $teacherUserId);
    public function getFollowCountList(string $business);
    public function followTeacher(int $userId, string $openId, string $business);
    public function unFollowTeacher(int $userId, string $openId, string $business);
    public function searchTeacherList(array $condition);
    public function getUserList(array $condition);
    public function getTeacherInfo(int $teacherId);
    public function getUserInfo(int $teacherId);
    public function createTeacher(array $newTeacher);
    public function updateTeacher(int $teacherId, array $teacherInfo);
    public function activeTeacher(int $teacherId, int $active);

    public function getTeacherListOfPaging(int $pageNo, int $pageSize, string $categoryCode);
    public function getTeacherCnt(string $categoryCode);
}
