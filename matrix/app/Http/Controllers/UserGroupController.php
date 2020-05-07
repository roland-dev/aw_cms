<?php

namespace Matrix\Http\Controllers;

use Illuminate\Http\Request;

use Matrix\Contracts\UserGroupManager;

use Log;
use Exception;
use Matrix\Exceptions\MatrixException;

class UserGroupController extends Controller
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getUserGroupList(UserGroupManager $userGroupManager)
    {
        $credentials = $this->request->validate([
            'page_no' => 'nullable|integer',
            'page_size' => 'nullable|integer',
        ]);

        try {
            $pageNo = array_get($credentials, 'page_no', 1);
            $pageSize = array_get($credentials, 'page_size', 10);

            $userGroupList = $userGroupManager->getUserGroupList($pageNo, $pageSize);
            $userGroupCnt = $userGroupManager->getUserGroupCnt();

            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'user_group_list' => $userGroupList,
                    'user_group_cnt' => $userGroupCnt,
                ],
            ];
        } catch (MatrixException $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = [
                'code' => $e->getCode(),
                'msg' => $e->getMessage(),
            ];
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => '未知错误',
            ];
        }

        return $ret;
    }

    public function getUserListByUserGroupCode(UserGroupManager $userGroupManager, $userGroupCode)
    {
        $credentials = $this->request->validate([
            'page_no' => 'nullable|integer',
            'paeg_size' => 'nullable|integer',
        ]);

        try {
            $pageNo = array_get($credentials, 'page_no', 1);
            $pageSize = array_get($credentials, 'page_size', 10);

            $userGroup = $userGroupManager->getUserGroup($userGroupCode);
            $userList = $userGroupManager->getUserGroupMemberList($pageNo, $pageSize, $userGroupCode);
            $usrCnt = $userGroupManager->getUserGroupMemberCnt($userGroupCode);

            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'user_group' => $userGroup,
                    'user_list' => $userList,
                    'user_cnt' => $usrCnt,
                ],
            ];
        } catch (MatrixException $e) {
            Log::error("获取用户组信息错误：{$e->getMessage()}", [$e]);
            $ret = [
                'code' => $e->getCode(),
                'msg' => $e->getMessage()
            ];
        } catch (Exception $e) {
            Log::error("获取用户组信息错误：{$e->getMessage()}", [$e]);
            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => '未知错误'
            ];
        }
        
        return $ret;
    }

    public function create(UserGroupManager $userGroupManager)
    {
        $reqData = $this->request->validate([
            'code' => 'required|string|max:191',
            'name' => 'required|string|max:191',
            'user_list' => 'required|array',
            'user_list.*.user_id' => 'required|int',
            'user_list.*.sort' => 'required|int',
        ]);
        try {
            $userListData = $userGroupManager->createUserGroup($reqData);
            $userGroup = array_get($userListData, 'user_group');
            $userList = array_get($userListData, 'user_list');
            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'user_group' => $userGroup,
                    'user_list' => $userList,
                ],
                'msg' => '添加成功',
            ];
        } catch (MatrixException $e) {
            Log::error("创建用户组错误：{$e->getMessage()}", [$e]);
            $ret = [
                'code' => $e->getCode(),
                'msg' => $e->getMessage()
            ];
        } catch (Exception $e) {
            Log::error("创建用户组错误：{$e->getMessage()}", [$e]);
            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => '未知错误'
            ];
        }
        
        return $ret;
    }

    public function update(UserGroupManager $userGroupManager)
    {
        $reqData = $this->request->validate([
            'code' => 'required|string|max:191',
            'name' => 'required|string|max:191',
        ]);

        try {
            $userListData = $userGroupManager->updateUserGroup($reqData);
            $userGroup = array_get($userListData, 'user_group');
            $userList = array_get($userListData, 'user_list');
            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'user_group' => $userGroup,
                    'user_list' => $userList,
                ],
                'msg' => '修改成功'
            ];
        } catch (MatrixException $e) {
            Log::error("更新用户组错误：{$e->getMessage()}", [$e]);
            $ret = [
                'code' => $e->getCode(),
                'msg' => $e->getMessage()
            ];
        } catch (Exception $e) {
            Log::error("更新用户组错误：{$e->getMessage()}", [$e]);
            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => '未知错误'
            ];
        }

        return $ret;
    }

    public function remove(UserGroupManager $userGroupManager, $userGroupCode)
    {
        $delUserGroupRet = $userGroupManager->removeUserGroup($userGroupCode);
        $this->checkServiceResult($delUserGroupRet, 'UserGroupService');
        return [
            'code' => SYS_STATUS_OK,
            'msg' => array_get($delUserGroupRet, 'msg'),
        ];
    }

    public function createUserGroupMember(UserGroupManager $userGroupManager)
    {
        $reqData = $this->request->validate([
            'code' => 'required|string|max:191',
            'name' => 'required|string|max:191',
            'user_id' => 'required|integer',
            'sort' => 'required|integer',
        ]);
        try {
            $userGroupMember = $userGroupManager->createUserGroupMember($reqData);
            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'user_group_member' => $userGroupMember
                ],
                'msg' => '添加成功',
            ];
        } catch (MatrixException $e) {
            Log::error("添加组成员错误：{$e->getMessage()}", [$e]);
            $ret = [
                'code' => $e->getCode(),
                'msg' => $e->getMessage()
            ];
        } catch (Exception $e) {
            Log::error("添加组成员错误：{$e->getMessage()}", [$e]);
            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => '未知错误',
            ];
        }

        return $ret;
    }

    public function getUserGroupMember(UserGroupManager $userGroupManager, $userGroupId)
    {
        try {
            $userGroupMember = $userGroupManager->getUserGroupMember($userGroupId);

            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'user_group_member' => $userGroupMember
                ],
                'msg' => 'success',
            ];
        } catch (MatrixException $e) {
            Log::error("查询组成员信息错误：{$e->getMessage()}", [$e]);
            $ret = [
                'code' => $e->getCode(),
                'msg' => $e->getMessage()
            ];
        } catch (Exception $e) {
            Log::error("查询组成员错误：{$e->getMessage()}", [$e]);
            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => '未知错误'
            ];
        }

        return $ret;
    }

    public function updateUserGroupMember(UserGroupManager $userGroupManager, $userGroupId)
    {
        $reqData = $this->request->validate([
            'code' => 'required|string|max:191',
            'name' => 'required|string|max:191',
            'user_id' => 'required|integer',
            'sort' => 'required|integer'
        ]);

        try {
            $userGroupMember = $userGroupManager->updateUserGroupMember($userGroupId, $reqData);
            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'user_group_member' => $userGroupMember
                ],
                'msg' => '更新成功'
            ];
        } catch (MatrixException $e) {
            Log::error("编辑组成员错误：{$e->getMessage()}", [$e]);
            $ret = [
                'code' => $e->getCode(),
                'msg' => $e->getMessage()
            ];
        } catch (Exception $e) {
            Log::error("编辑组成员错误: {$e->getMessage()}", [$e]);
            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => "未知错误"
            ];
        }

        return $ret;
    }

    public function deleteUserGroupMember(UserGroupManager $userGroupManager, $userGroupId)
    {
        try {
            $userGroupMember = $userGroupManager->deleteUserGroupMember($userGroupId);

            $ret = [
                'code' => SYS_STATUS_OK,
                'msg' => 'success'
            ];
        } catch (MatrixException $e) {
            Log::error("删除组成员失败：{$e->getMessage()}", [$e]);
            $ret = [
                'code' => $e->getCode(),
                'msg' => $e->getMessage()
            ];
        } catch (Exception $e) {
            Log::error("删除组成员失败：{$e->getMessage()}", [$e]);
            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => "未知错误"
            ];
        }

        return $ret;
    }

    public function getUserIdListOfUserGroupCode(UserGroupManager $userGroupManager, $userGroupCode)
    {
        try {
            $userIdList = $userGroupManager->getUserIdListOfUserGroupCode($userGroupCode);

            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'user_id_list' => $userIdList,
                ],
            ];
        } catch (MatrixException $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = [
                'code' => $e->getCode(),
                'msg' => $e->getMessage(),
            ];
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => '未知错误',
            ];
        }
        return $ret;
    }
}