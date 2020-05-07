<?php

namespace Matrix\Contracts;

interface UserGroupManager extends BaseInterface
{
    public function getUserGroupList(int $pageNo, int $pageSize);
    public function getUserGroupCnt();
    public function getUserListByUserGroupCode(string $userGroupCode);
    public function createUserGroup(array $newUserGroup);
    public function updateUserGroup(array $updateUserGroup);
    public function removeUserGroup(string $userGroupCode);

    public function getUserGroup(string $userGroupCode);
    public function getUserGroupMemberList(int $pageNo, int $pageSize, string $userGroupCode);
    public function getUserGroupMemberCnt(string $userGroupCode);
    
    public function createUserGroupMember(array $newUserGroupMember);
    public function getUserGroupMember(int $userGroupId);
    public function updateUserGroupMember(int $userGroupId, array $newUserGroupMember);
    public function deleteUserGroupMember(int $userGroupId);

    public function getUserIdListOfUserGroupCode(string $userGroupCode);
}
