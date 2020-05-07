<?php

namespace Matrix\Contracts;

interface UserManager extends BaseInterface
{
    public function loginByEnterpriseUserId(string $enterpriseUserId);
    public function logout();
    public function getTeacherList();
    public function getUserInfo(int $userId);
    public function getUserList(int $pageNo, int $pageSize, array $credentials);
    public function getUserCnt(array $credentials);

    public function getAllUserList();

    public function createUser(array $newUser);
    public function activeUser(int $userId, int $active);
    public function updateUser(int $userId, array $userInfo);

    public function getUserListByUserIdList(array $userIdList);

    public function getUserListByGroupCode(string $groupCode);
    public function getUcListByUserIdList(array $userIdList);
    public function getUcListByEnterpriseUserIdList(array $enterpriseUserIdList);

    public function selectedUser(int $userId, int $selected);

    public function getTeacherTabList();
    public function getSignTypeList();
}
