<?php

namespace Matrix\Contracts;

interface PermissionManager extends BaseInterface
{
    public function getPermissionTree();
    public function getUserPermission(int $userId);
    public function checkPermission(int $userId, $operate);
    public function grant(int $userId, array $permissionCodeList);

    public function getMoreUserGrantedList(int $pageNo, int $pageSize, array $condition);
    public function getMoreUserGrantedCnt(array $condition);
}

