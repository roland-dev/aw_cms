<?php

namespace Matrix\Services;

use Matrix\Contracts\UserGroupManager;

use Matrix\Models\UserGroup;
use Matrix\Models\User;

use Exception;
use Illuminate\Support\Facades\DB;
use Matrix\Exceptions\MatrixException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Matrix\Exceptions\UserException;

class UserGroupService extends BaseService implements UserGroupManager
{
    private $user;
    private $userGroup;
    
    public function __construct(UserGroup $userGroup, User $user)
    {
        $this->userGroup = $userGroup;
        $this->user = $user;
    }

    public function getUserGroupList(int $pageNo, int $pageSize)
    {
        
        $userGroupList = UserGroup::select('code', 'name')
            ->groupBy('code')
            ->skip($pageSize * ($pageNo - 1))
            ->take($pageSize)
            ->get()
            ->toArray();

        return $userGroupList;
    }

    public function getUserGroupCnt()
    {
        $userGroupList = UserGroup::groupBy('code')
            ->get()
            ->toArray();
        
        $userGroupCnt = count($userGroupList);

        return $userGroupCnt;
    }

    public function getUserGroup(string $userGroupCode)
    {
        try {
            $userGroup = UserGroup::select('code', 'name')
                ->where('code', $userGroupCode)
                ->groupBy('code')
                ->firstOrFail()
                ->toArray();
        } catch (ModelNotFoundException $e) {
            throw new MatrixException('当前用户组不存在', USER_GROUP_NOT_FOUND);
        }

        return $userGroup;
    }

    public function getUserGroupMemberList(int $pageNo, int $pageSize, string $userGroupCode)
    {
        $userListData = [];
        $userList = UserGroup::where('code', $userGroupCode)
            ->skip($pageSize * ($pageNo - 1))
            ->take($pageSize)
            ->get()
            ->toArray();

        $userIdList = array_column($userList, 'user_id');
        $userInfoList = $this->user->getUserListByUserIdList($userIdList);
        $userInfoList = array_column($userInfoList, NULL, 'id');
        foreach($userList as $user) {
            if (!isset($userInfoList[$user['user_id']])) {
                throw new UserException("对应的用户不存在，请找系统管理员进行处理", USER_NOT_FOUND);
            }
            $userData = $userInfoList[$user['user_id']];
            $userData['sort'] = array_get($user, 'sort');
            $userData['user_group_id'] = array_get($user, 'id');
            $userListData[] = $userData;
        }

        return $userListData;
    }

    public function getUserGroupMemberCnt(string $userGroupCode)
    {
        $userCnt = UserGroup::where('code', $userGroupCode)->count();
        return $userCnt;
    }

    public function getUserListByUserGroupCode(string $userGroupCode)
    {
        $userGroup = $this->userGroup->getUserGroupByCode($userGroupCode);
        if (empty($userGroup)) {
            $ret = [
                'code' => USER_GROUP_NOT_FOUND,
                'msg' => '当前用户组不存在',
            ];
            return $ret;
        }

        $userListData = [];
        $userList = $this->userGroup->getUserListByCode($userGroupCode);
        $userIdList = array_column($userList, 'user_id');
        $userInfoList = $this->user->getUserListByUserIdList($userIdList);
        $userInfoList = array_column($userInfoList, NULL, 'id');
        foreach($userList as $user) {
            if (!isset($userInfoList[$user['user_id']])) {
                throw new UserException("对应的用户不存在，请找系统管理员进行处理", USER_NOT_FOUND);
            }
            $userData = $userInfoList[$user['user_id']];
            $userData['sort'] = array_get($user, 'sort');
            $userData['user_group_id'] = array_get($user, 'id');
            $userListData[] = $userData;
        }

        return [
            'code' => SYS_STATUS_OK,
            'user_group' => $userGroup,
            'user_list' => $userListData,
        ];
    }

    public function createUserGroup(array $newUserGroup)
    {
        $userGroupCode = array_get($newUserGroup, 'code');
        $userGroupData = $this->userGroup->getUserGroupByCode($userGroupCode);
        if (!empty($userGroupData)) {
            throw new MatrixException("当前用户组已存在", USER_GROUP_EXISTS);
        }

        $condition = [];

        $userGroup = [
            'code' => array_get($newUserGroup, 'code'),
            'name' => array_get($newUserGroup, 'name'),
        ];
        $userList = array_get($newUserGroup, 'user_list');
        DB::beginTransaction();
        foreach ($userList as $user) {
            $condition = array_merge($userGroup, $user);
            $userGroupData = $this->userGroup->createUserGroup($condition);
            if (empty($userGroupData)) {
                DB::rollback();
                throw new Exception("服务器错误", SYS_STATUS_ERROR_UNKNOW);
            }
        }
        
        DB::commit();

        $userGroupCode = array_get($newUserGroup, 'code');
        return self::getUserListByUserGroupCode($userGroupCode);
    }

    public function updateUserGroup(array $updateUserGroup)
    {
        $userGroupCode = array_get($updateUserGroup, 'code');
        $userGroupData = $this->userGroup->getUserGroupByCode($userGroupCode);
        if (empty($userGroupData)) {
            throw new MatrixException("当前用户组不存在", USER_GROUP_NOT_FOUND);
        }

        $condition = [];
        $condition['name'] = array_get($updateUserGroup, 'name');

        UserGroup::where('code', $userGroupCode)->update($condition);

        return self::getUserListByUserGroupCode($userGroupCode);
    }


    public function removeUserGroup(string $userGroupCode)
    {
        $delUserGroupRet = $this->userGroup->removeUserGroup($userGroupCode);
        if (empty($delUserGroupRet) || $delUserGroupRet === 0) {
            return [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => '服务器错误',
            ];
        }
        
        return [
            'code' => SYS_STATUS_OK,
            'msg' => '删除成功',
        ];
    }

    public function getUserGroupListByCode(string $code)
    {
        $userGroupList = UserGroup::where('code', $code)->get();
        $userGroupList = $userGroupList->filter(function ($item, $key) {
            return empty($item->deleted_at);
        });

        return $userGroupList;
    }

    public function createUserGroupMember(array $newUserGroupMember)
    {
        try {
            $userId = array_get($newUserGroupMember, 'user_id');
            if (!empty($userId)) {
                $user = User::where('id', $userId)->firstOrFail();
            } else {
                throw new MatrixException("所选用户不存在", USER_NOT_FOUND);
            }
        } catch (ModelNotFoundException $e) {
            throw new MatrixException("所选用户不存在", USER_NOT_FOUND);
        }
        

        try {
            $userGroupCode = array_get($newUserGroupMember, 'code');
            if (!empty($userGroupCode) && !empty($userId)) {
                $userGroupMember = UserGroup::where('code', $userGroupCode)->where('user_id', $userId)->take(1)->firstOrFail();
                throw new MatrixException("当前组中组成员已存在", USER_GROUP_MEMBER_EXISTS);
            } else {
                throw new MatrixException("所选用户组不存在", USER_GROUP_NOT_FOUND);
            }
        } catch (ModelNotFoundException $e) {
            $condition = [
                'code' => $userGroupCode,
                'user_id' => $userId
            ];
            try {
                $userGroupMember = UserGroup::onlyTrashed()->where($condition)->take(1)->firstOrFail();
                UserGroup::onlyTrashed()->where($condition)->restore();
                foreach ($newUserGroupMember as $k => $v) {
                    $userGroupMember->{$k} = $v;
                }
                $userGroupMember->save();
            } catch (ModelNotFoundException $e) {
                $userGroupMember = UserGroup::create($newUserGroupMember);
            }
        }

        return $userGroupMember;
    }

    public function getUserGroupMember(int $userGroupId)
    {
        try {
            $userGroupMember = UserGroup::where('id', $userGroupId)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new MatrixException("当前组成员数据不存在", USER_GROUP_MEMBER_NOT_FOUND);
        }

        return $userGroupMember;
    }
    
    public function updateUserGroupMember(int $userGroupId, array $newUserGroupMember)
    {
        try {
            $userId = array_get($newUserGroupMember, 'user_id');
            if (!empty($userId)) {
                $user = User::where('id', $userId)->firstOrFail();
            } else {
                throw new MatrixException("所选用户不存在", USER_NOT_FOUND);
            }
        } catch (ModelNotFoundException $e) {
            throw new MatrixException("所选用户不存在", USER_NOT_FOUND);
        }
        
        try {
            $userGroupCode = array_get($newUserGroupMember, 'code');
            if (!empty($userGroupCode) && !empty($userId)) {
                $userGroupMember = UserGroup::where('code', $userGroupCode)->where('user_id', $userId)->where('id', '<>', $userGroupId)->take(1)->firstOrFail();
                throw new MatrixException("当前组中组成员已存在", USER_GROUP_MEMBER_EXISTS);
            } else {
                throw new MatrixException("所选用户组不存在", USER_GROUP_NOT_FOUND);
            }
        } catch (ModelNotFoundException $e) {
            $condition = [
                'code' => $userGroupCode,
                'user_id' => $userId,
            ];
            try {
                $userGroupMember = UserGroup::onlyTrashed()->where($condition)->take(1)->firstOrFail();
                if (!empty($userGroupMember)) {
                    $userGroupMember->forceDelete();
                }
                try {
                    $userGroupMember = UserGroup::where('id', $userGroupId)->firstOrFail();
                    foreach ($newUserGroupMember as $k => $v) {
                        $userGroupMember->{$k} = $v;
                    }
                    $userGroupMember->save();
                } catch (ModelNotFoundException $e) {
                    new MatrixException("当前组成员数据不存在", USER_GROUP_MEMBER_NOT_FOUND);
                }

            } catch (ModelNotFoundException $e) {
                try {
                    $userGroupMember = UserGroup::where('id', $userGroupId)->firstOrFail();
                    foreach ($newUserGroupMember as $k => $v) {
                        $userGroupMember->{$k} = $v;
                    }
                    $userGroupMember->save();
                } catch (ModelNotFoundException $e) {
                    new MatrixException("当前组成员数据不存在", USER_GROUP_MEMBER_NOT_FOUND);
                }
            }
            $userGroupMember = $this->userGroup->createUserGroup($newUserGroupMember);
            if (empty($userGroupMember)) {
                throw new Exception("发生未知错误", SYS_STATUS_OK);
            }
        }

        return $userGroupMember;
    }

    public function deleteUserGroupMember(int $userGroupId)
    {
        try {
            $userGroupMember = UserGroup::where('id', $userGroupId)->firstOrFail()->delete();
        } catch (ModelNotFoundException $e) {
            throw new MatrixException("当前组成员数据不存在", USER_GROUP_MEMBER_NOT_FOUND);
        }
    }

    public function getUserIdListOfUserGroupCode(string $userGroupCode)
    {
        $userIdList = UserGroup::where('code', $userGroupCode)->pluck('user_id')->toArray();
        return $userIdList;
    }
}
