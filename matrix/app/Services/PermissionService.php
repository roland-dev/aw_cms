<?php

namespace Matrix\Services;

use Illuminate\Support\Facades\Auth;

use Matrix\Exceptions\PermissionException;

use Matrix\Contracts\PermissionManager;

use Matrix\Models\Permission;
use Matrix\Models\Grant;
use Matrix\Models\User;

class PermissionService extends BaseService implements PermissionManager
{
    private $permission;
    private $grant;
    private $user;

    public function __construct(Permission $permission, Grant $grant, User $user)
    {
        $this->permission = $permission;
        $this->grant = $grant;
        $this->user = $user;
    }

    public function getPermissionTree()
    {
        $permissionTree = $this->permission->getPermissionTree();
        return [
            'code' => SYS_STATUS_OK,
            'data' => [
                'permission_tree' => $permissionTree,
            ],
        ];
    }

    public function getGrantedPermissionTree(array $permissionTree, array $grantedCodeList)
    {
        if (empty($permissionTree)) {
            return [];
        }

        foreach ($permissionTree as &$permission) {
            $permission['granted'] = in_array($permission['code'], $grantedCodeList);
            $permission['child'] = $this->getGrantedPermissionTree($permission['child'], $grantedCodeList);
            if (!empty($permission['child'])) {
                $childGrantedList = array_column($permission['child'], 'granted');
                if (count(array_unique($childGrantedList)) === 1) {
                    $permission['granted'] = $childGrantedList[0];
                } else {
                    $permission['granted'] = 1;
                }
            }
        }

        return $permissionTree;
    }

    public function getUserPermission(int $userId)
    {
        $grantedList = $this->grant->getOneGrantedList(Auth::id());
        $grantedCodeList = array_column($grantedList, 'permission_code');
        if (Auth::id() !== $userId) {
            if (!in_array('permission', $grantedCodeList)) {
                throw new PermissionException("Permission Denied");
            }
            $grantedList = $this->grant->getOneGrantedList($userId);
            $grantedCodeList = array_column($grantedList, 'permission_code');
        }

        $permissionTree = $this->permission->getPermissionTree();
        $grantedPermissionTree = $this->getGrantedPermissionTree($permissionTree, $grantedCodeList);

        return [
            'code' => SYS_STATUS_OK,
            'data' => [
                'grantedPermissionTree' => $grantedPermissionTree,
            ],
        ];
    }

    public function checkPermission(int $userId, $operate)
    {
        $grantedList = $this->grant->getOneGrantedList($userId);
        $grantedCodeList = empty($grantedList) ? [] : array_column($grantedList, 'permission_code');

        if (empty($grantedCodeList) || !((is_string($operate) && in_array($operate, $grantedCodeList)) || (is_array($operate) && !empty(array_intersect($operate, $grantedCodeList))))) {
            throw new PermissionException("Permission Denied");
        }
    }

    //coded by Jiangzd
    public function grant(int $userId, array $permissionCodeList)
    {

        $addPermissionList = [];
        $reGrantedPermissionList = [];

        $onePermissionList = $this->grant->getOneList($userId);
        $onePermissionListChange = array_column($onePermissionList, NULL, 'permission_code');
        $onePermissionCodeList = array_column($onePermissionList, 'permission_code');
        $removePermissionList = array_diff($onePermissionCodeList, $permissionCodeList);
        foreach($permissionCodeList as $permissionCode) {
          if (array_key_exists($permissionCode, $onePermissionListChange)) {
             if ($onePermissionListChange[$permissionCode]['active'] !== 1) {
                $reGrantedPermissionList[] = $permissionCode;
             }
          }else{
             $addPermissionList[] = $permissionCode; 
          }
        }           

        $this->grant->addUserPermission($userId, $addPermissionList);
        $this->grant->removeGrantedList($userId, $removePermissionList);
        $this->grant->reGrantedList($userId, $reGrantedPermissionList);
    
        $ret = [
           'code'=>SYS_STATUS_OK
        ];
   
        return $ret;
    }

    public function getMoreUserGrantedList(int $pageNo, int $pageSize, array $condition)
    {
        $userCondition = [];
        if (array_key_exists('name', $condition) && !empty($condition['name'])) {
            $name = array_get($condition, 'name');
            $userCondition[] = ['name', 'like', "%$name%"];
        }
        if (array_key_exists('type', $condition) && !empty($condition['type'])) {
            $type = array_get($condition, 'type');
            $userCondition[] = ['type', '=', $type];
        }

        $userList = User::where($userCondition)
            ->orderBy('created_at', 'desc')
            ->skip($pageSize * ($pageNo - 1))
            ->take($pageSize)
            ->get()
            ->toArray();

        $userIdList = array_column($userList, 'id');
        $grantCondition = [];
        if (array_key_exists('permission_code', $condition) && !empty($condition['permission_code'])) {
            $permissionCode = array_get($condition, 'permission_code');
            $grantCondition[] = ['permission_code', '=', $permissionCode];
        }
        $grantedList = $this->grant->getGrantedList($userIdList, $grantCondition);

        $moreGrantedList = [];
        foreach ($grantedList as $granted) {
            if (!array_key_exists($granted['user_id'], $moreGrantedList)) {
                $moreGrantedList[$granted['user_id']] = [];
            }
            $moreGrantedList[$granted['user_id']][] = $granted['permission_code'];
        }

        foreach ($userList as &$user) {
            if (array_key_exists($user['id'], $moreGrantedList)) {
                $user['permission_code_list'] = $moreGrantedList[$user['id']];
            } else {
                $user['permission_code_list'] = [];
            }
        }

        return $userList;
    }

    public function getMoreUserGrantedCnt(array $condition)
    {
        $userCondition = [];
        if (array_key_exists('name', $condition) && !empty($condition['name'])) {
            $name = array_get($condition, 'name');
            $userCondition[] = ['name', 'like', "%$name%"];
        }
        if (array_key_exists('type', $condition) && !empty($condition['type'])) {
            $type = array_get($condition, 'type');
            $userCondition[] = ['type', '=', $type];
        }

        $userCnt = User::where($userCondition)->count();

        return $userCnt;
    }
}

