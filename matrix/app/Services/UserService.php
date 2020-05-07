<?php

namespace Matrix\Services;

use Matrix\Contracts\UserManager;

use Matrix\Models\Ucenter;
use Matrix\Models\UserGroup;
use Matrix\Models\User;

use Illuminate\Support\Facades\Auth;
use Exception;
use Matrix\Models\TeacherTab;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Matrix\Exceptions\UserException;
use Matrix\Exceptions\MatrixException;
use Matrix\Models\Tab;

class UserService extends BaseService implements UserManager
{
    private $user;
    private $ucenter;
    private $userGroup;
    private $teacherTab;
    const USER_GROUP_STOCK_A = 'teacher_stock_a';
    const USER_GROUP_SELECT_STOCK_A = 'teacher_select_stock_a';
    const TEACHER_TYPE = 'teacher';

    public function __construct(User $user, Ucenter $ucenter, UserGroup $userGroup, TeacherTab $teacherTab)
    {
        $this->user = $user;
        $this->ucenter = $ucenter;
        $this->userGroup = $userGroup;
        $this->teacherTab = $teacherTab;
    }

    public function logout()
    {
        Auth::logout();

        $ret = [
            'code' => SYS_STATUS_OK,
        ];
        return $ret;
    }

    public function getUserByEnterpriseUserId(string $enterpriseUserId)
    {
        try{
            $ret = [];
            $ucenterInfo = $this->ucenter->getUcByEnterpriseUserId($enterpriseUserId);
            if (empty($ucenterInfo) || trim($ucenterInfo['enterprise_userid']) == '') {
                $ret['code'] = USER_NOT_FOUND;
                return $ret;
            }

            $userId = array_get($ucenterInfo, 'user_id', 0);
            $userInfo = $this->user->getUserInfo($userId);

            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => $userInfo,
            ];
        }catch(ModelNotFoundException $e){
            throw new UserException('UC 用户没有查询到', USER_NOT_FOUND);
        }
        return $ret;
    }

    public function loginByEnterpriseUserId(string $enterpriseUserId)
    {
        $ret = [];
        $userInfoData = $this->getUserByEnterpriseUserId($enterpriseUserId);
        if (SYS_STATUS_OK !== array_get($userInfoData, 'code') || 1 !== array_get($userInfoData, 'data.active')) {
            $ret['code'] = USER_LOGIN_FAILED;
            return $ret;
        }

        $userId = array_get($userInfoData, 'data.id');
        $loginResult = Auth::loginUsingId($userId);

        $ret = [
            'code' => $loginResult ? SYS_STATUS_OK : USER_LOGIN_FAILED,
        ];
        return $ret;
    }

    public function getTeacherList()
    {
        $condition = [
            ['type', '=', 'teacher'],
        ];
        $teacherList = $this->user->getUserList($condition); 
        $teacherUserIdList = array_column($teacherList, 'id');

        $ucList = $this->ucenter->getUcListByUserIdList($teacherUserIdList);
        $ucList = array_column($ucList, 'enterprise_userid', 'user_id');

        foreach ($teacherList as &$teacher) {
            $teacher['enterprise_userid'] = array_get($ucList, $teacher['id']);
        }

        return [
            'code' => SYS_STATUS_OK,
            'teacherList' => $teacherList,
        ];
    }

    public function getVideoAuthorList()
    {
        $condition = [];
        $videoAuthorList = $this->user->getAllUserList($condition);
        $collection = collect($videoAuthorList);        
        $List = $collection->whereIn('type', ['teacher', 'organization']);
        return [
            'code' => SYS_STATUS_OK,
            'videoAuthorList' => $List,
        ];
    }

    public function getUserInfo(int $userId)
    {
        $userInfo = $this->user->getUserInfo($userId); 
        $ucBind = $this->ucenter->getUcByUserId($userId);
        $teacherTabList = $this->teacherTab->getTabListByUserId($userId);
        if (empty($teacherTabList)) {
            $tabList = [];
        } else {
            $tabList = array_column($teacherTabList, 'code');
        }
        $ret = [
            'code' => empty($userInfo) ? USER_NOT_FOUND : SYS_STATUS_OK,
            'userInfo' => $userInfo,
            'ucInfo' => $ucBind,
            'teacherTabs' => $tabList,
        ];

        return $ret;
    }

    public function getAllUserList()
    {
        $userList = $this->user->getAllUserList();
        $selectUserIds = self::getSelectUserId();
        foreach ($userList as &$user) {
            if (in_array(array_get($user, 'id'), $selectUserIds)) {
                $user['is_can_selected'] = 1;
            } else {
                $user['is_can_selected'] = 0;
            }
        }

        return $userList;
    }

    public function getUserList(int $pageNo, int $pageSize, array $credentials)
    {
        $cond = [];
        $name = array_get($credentials, 'name', NULL);
        if (!empty($name)) {
            $cond[] = ['name', 'like', "%$name%"];
        }

        $type = array_get($credentials, 'type', NULL);
        if (!empty($type)) {
            $cond[] = ['type', '=', $type];
        }

        $userList = User::where($cond)
            ->orderBy('created_at', 'desc')
            ->skip($pageSize * ($pageNo - 1))
            ->take($pageSize)
            ->get()
            ->toArray();

        $selectUserIds = self::getSelectUserId();
        foreach ($userList as &$user) {
            if (in_array(array_get($user, 'id'), $selectUserIds)) {
                $user['is_can_selected'] = 1;
            } else {
                $user['is_can_selected'] = 0;
            }
        }

        return $userList;
    }



    public function getUserCnt(array $credentials)
    {
        $cond = [];
        $name = array_get($credentials, 'name', NULL);
        if (!empty($name)) {
            $cond[] = ['name', 'like', "%$name%"];
        }

        $type = array_get($credentials, 'type', NULL);
        if (!empty($type)) {
            $cond[] = ['type', '=', $type];
        }

        $userCnt = User::where($cond)->count();

        return $userCnt;
    }

    public function createUser(array $newUser)
    {
        $enterpriseUserId = (string)array_get($newUser, 'enterprise_userid');
        $enterpriseBind = $this->ucenter->getUcByEnterpriseUserId($enterpriseUserId);

        if (!empty($enterpriseBind) && '' != $enterpriseUserId) { // enterprise user id already exists
            $ret = ['code' => ENTERPRISE_USERID_EXISTS];
            return $ret;
        }

        DB::beginTransaction();
        $user = $this->user->createUser($newUser);
        if (empty($user)) { // user already exists
            DB::rollback();
            $ret = ['code' => USER_EXISTS];
            return $ret;
        }

        if (self::TEACHER_TYPE == array_get($user, 'type')) {
            $teacherTabs = array_get($newUser, 'teacher_tabs');
            $userId = array_get($user, 'id');
            
            foreach ($teacherTabs as $item) {
                $condition = [
                    'code' => $item,
                    'teacher_user_id' => $userId,
                ];
                $teacherTab = $this->teacherTab->createTeacherTab($condition);
                if (empty($teacherTab)) {
                    DB::rollback();
                    $ret = [
                        'code' => SYS_STATUS_ERROR_UNKNOW,
                        'msg' => '服务器错误',
                    ];
                    return $ret;
                }
            }
        }
        
        DB::commit();

        $userId = array_get($user, 'id');
        $enterpriseBind = $this->ucenter->bindEnterpriseUserId($userId, $enterpriseUserId);

        $teacherTabList = $this->teacherTab->getTabListByUserId($userId);
        if (empty($teacherTabList)) {
            $tabList = [];
        } else {
            $tabList = array_column($teacherTabList, 'code');
        }

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'userInfo' => $user,
                'enterpriseBind' => $enterpriseBind,
                'teacherTabs' => $tabList,
            ],
        ];
        return $ret;
    }


    public function activeUser(int $userId, int $active)
    {
        $userActive = ['active' => $active];
        $userInfo = $this->user->updateUser($userId, $userActive);

        if (empty($userInfo)) {
            $ret = [
                'code' => USER_UPDATE_FAILED,
            ];
            return $ret;
        }

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'userInfo' => $userInfo,
            ],
        ];

        return $ret;
    }

    public function updateUser(int $userId, array $userInfo)
    {
        $ret = [
            'code' => USER_UPDATE_FAILED,
        ];
        $enterpriseUserId = array_get($userInfo, 'enterprise_userid');
        $oldUserInfo = $this->user->getUserInfo($userId);
        if (empty($oldUserInfo)) {
            return $ret;
        }

        if ( self::TEACHER_TYPE == array_get($oldUserInfo, 'type') && self::TEACHER_TYPE != array_get($userInfo, 'type') ) {
            $userGroup = $this->userGroup->getUserGroup($userId, self::USER_GROUP_STOCK_A);
            if ( !empty($userGroup) ) {
                $ret = [
                    'code' => USER_UPDATE_PARAMS_LOGIC_ERROR,
                ];
                return $ret;
            }
        }

        if (!empty($enterpriseUserId)) {
            $ucInfo = $this->ucenter->getUcByEnterpriseUserId($enterpriseUserId);
            if (!empty($ucInfo) && $ucInfo['user_id'] != $userId) {
                $ret['code'] = ENTERPRISE_USERID_EXISTS;
                return $ret;
            }
        }

        try {
            DB::beginTransaction();
            $tabList = [];
            if (self::TEACHER_TYPE == array_get($userInfo, 'type')) {
                $teacherTabs = array_get($userInfo, 'teacher_tabs');

                $teacherTabList = $this->teacherTab->getTabListByUserId($userId);
                if (empty($teacherTabList)) {
                    $tabList = [];
                } else {
                    $tabList = array_column($teacherTabList, 'code');
                }

                $createTecherTabs = array_diff($teacherTabs, $tabList);
                $delTeacherTabs = array_diff($tabList, $teacherTabs);

                foreach ($createTecherTabs as $item) {
                    $condition = [
                        'code' => $item,
                        'teacher_user_id' => $userId,
                    ];
                    $teacherTab = $this->teacherTab->createTeacherTab($condition);
                    if (empty($teacherTab)) {
                        DB::rollback();
                        $ret = [
                            'code' => SYS_STATUS_ERROR_UNKNOW,
                            'msg' => '服务器错误',
                        ];
                        return $ret;
                    }
                }

                $delTeacherTabData = $this->teacherTab->deleteTeacherTab($userId, $delTeacherTabs);
            } else {
                $delTeacherTabData = $this->teacherTab->deleteTeacherTab($userId, array_get($userInfo, 'teacher_tabs'));
            }

            $userInfo = $this->user->updateUser($userId, $userInfo);
            if (!empty($enterpriseUserId)) {
                $ucInfo = $this->ucenter->updateEnterpriseUserId($userId, $enterpriseUserId);
            } else {
                $ucInfo = $this->ucenter->getUcByUserId($userId);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return $ret;
        }


        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'userInfo' => $userInfo,
                'ucInfo' => $ucInfo,
                'teacherTabs' => $tabList,
            ],
        ];

        return $ret;
    }

    public function getUserListByUserIdList(array $userIdList)
    {
        $userList = $this->user->getUserListByUserIdList($userIdList);

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'user_list' => $userList,
            ],
        ];

        return $ret;
    }

    public function getUserListByGroupCode(string $groupCode)
    {
        $userIdList = $this->userGroup->getUserIdListByCode($groupCode);
        $userList = $this->user->getUserListByUserIdList($userIdList);

        return $userList;
    }

    public function getUcListByUserIdList(array $userIdList)
    {
        $ucList = $this->ucenter->getUcListByUserIdList($userIdList);

        return $ucList;
    }

    public function getUcListByEnterpriseUserIdList(array $enterpriseUserIdList)
    {
        $ucList = $this->ucenter->getUcListByEnterpriseUserIdList($enterpriseUserIdList);

        return $ucList;
    }

    public function selectedUser(int $userId, int $selected)
    {
        $selectUserIds = self::getSelectUserId();
        if ( !in_array($userId, $selectUserIds) ) {
            $ret = [
                'code' => UPDATE_SELECTED_PARAMS_ERROR,
            ];
            return $ret;
        }

        $userSelected = ['selected' => $selected];
        $userInfo = $this->user->updateUser($userId, $userSelected);

        if (empty($userInfo)) {
            $ret = [
                'code' => USER_UPDATE_FAILED,
            ];
            return $ret;
        }

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'userInfo' =>$userInfo,
            ],
        ];

        return $ret;
    }

    public function getSelectUserId()
    {
        $result = [];
        
        $userGroupStockAUserId = $this->userGroup->getUserIdListByCode(self::USER_GROUP_STOCK_A);

        $userGroupSelectStockAUserId = $this->userGroup->getUserIdListByCode(self::USER_GROUP_SELECT_STOCK_A);

        $result = array_intersect($userGroupStockAUserId, $userGroupSelectStockAUserId);

        return $result;
    }

    public function getTeacherTabList()
    {
        $teacherTabList = Tab::select('code', 'name', 'sort')->where('active', 1)->orderBy('sort', 'desc')->get();

        if (empty($teacherTabList)) {
            $ret = [
                'code' => TEACHER_TAB_NOT_FOUND,
            ];
            return $ret;
        }

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'teacher_tab_list' => $teacherTabList,
            ],
        ];

        return $ret;
    }

    public function getSignTypeList()
    {
        $signTypeList = User::groupBy('type')->pluck('type')->toArray();
        return $signTypeList;
    }
}
