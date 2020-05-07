<?php

namespace Matrix\Services;

use Matrix\Contracts\TeacherManager;

use Matrix\Models\TeacherFollow;
use Matrix\Models\TeacherTab;
use Matrix\Models\Teacher;
use Matrix\Models\User;

use Exception;
use Matrix\Exceptions\MatrixException;

class TeacherService extends BaseService implements TeacherManager
{
    private $teacherFollow;
    private $teacherTab;
    private $teacher;
    private $user;

    public function __construct(TeacherFollow $teacherFollow, TeacherTab $teacherTab, Teacher $teacher, User $user)
    {
        $this->teacherFollow = $teacherFollow;
        $this->teacherTab = $teacherTab;
        $this->teacher = $teacher;
        $this->user = $user;
    }

    public function getAllTabList()
    {
        return config('teacher.tabs');
    }

    public function getTeacherFollowCount(int $teacherUserId, string $business = 'default')
    {
        return $this->teacherFollow->getFollowCount($teacherUserId, $business);
    }

    public function getTeacherFollow(int $teacherUserId, string $openId, string $business = 'default')
    {
        $teacherFollow = $this->teacherFollow->getFollow($teacherUserId, $openId, $business);

        return empty($teacherFollow) ? 0 : 1;
    }

    public function getTeacherTabList(int $teacherUserId)
    {
        $tabList = $this->teacherTab->getTabListByUserId($teacherUserId);

        return $tabList;
    }

    public function getFollowCountList(string $business = 'default')
    {
        $followCountList = $this->teacherFollow->getFollowCountList($business);
        $followCountList = array_column($followCountList, 'cnt', 'user_id');

        return $followCountList;
    }

    public function getFollowListByOpenId(string $openId, string $business = 'default')
    {
        $followList = $this->teacherFollow->getFollowListByOpenId($openId, $business);

        return $followList;
    }

    public function followTeacher(int $userId, string $openId, string $business = 'default')
    {
        $this->teacherFollow->follow($userId, $openId, $business);
    }

    public function unFollowTeacher(int $userId, string $openId, string $business = 'default')
    {
        $this->teacherFollow->unfollow($userId, $openId, $business);
    }

    public function getUnsyncUcFollowList(string $business = 'default', int $size = 1000)
    {
        $teacherFollowList = $this->teacherFollow->getUnSyncUcFollowList($business, $size);

        return $teacherFollowList;
    }

    public function searchTeacherList(array $condition)
    {
        $cond = [];
        $categoryCode = array_get($condition, 'category_code', NULL);
        if (!empty($categoryCode)) {
            $cond[] = ['category_code', '=', $categoryCode];
        }

        $teacherList = $this->teacher->getTeacherListByCondition($cond);

        $allUserList = self::getAllUserList();

        foreach ($teacherList as &$teacher) {
            $user = array_get($allUserList, $teacher['user_id'], NULL);
            $teacher['user_name'] = empty($user) ? '' : $user['name'];
        }
        
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'teacher_list' => $teacherList,
            ],
        ];

        return $ret;
    }

    private function getAllUserList()
    {
        $result = [];

        $userList = $this->user->getAllUserList();

        foreach ($userList as $user) {
            $result[$user['id']] = $user;
        }

        return $result;
    }

    public function getTeacherListOfPaging(int $pageNo, int $pageSize, string $categoryCode)
    {
        $cond = [];
        
        if (!empty($categoryCode)) {
            $cond[] = ['category_code', '=', $categoryCode];
        }

        $teacherList = Teacher::where($cond)
            ->orderBy('created_at', 'desc')
            ->skip($pageSize * ($pageNo - 1))
            ->take($pageSize)
            ->get()
            ->toArray();
        
        $userIdList = array_column($teacherList, 'user_id');
        $userList = $this->user->getUserListByUserIdList($userIdList);
        $userList = array_column($userList, NULL, 'id');
        $userIdList = array_column($userList, 'id');

        foreach ($teacherList as &$teacher) {
            if (in_array(array_get($teacher, 'user_id'), $userIdList)) {
                $teacher['user_name'] = $userList[$teacher['user_id']]['name'];
            }
        }

        return $teacherList;
    }

    public function getTeacherCnt(string $categoryCode)
    {
        $cond = [];

        if (!empty($categoryCode)) {
            $cond[] = ['category_code', '=', $categoryCode];
        }

        $teacherCnt = Teacher::where($cond)->count();

        return $teacherCnt;
    }

    public function getUserList(array $condition)
    {
        $cond = [];
        $categoryCode = array_get($condition, 'category_code', NULL);
        $cond[] = ['category_code', '=', $categoryCode];

        $teacherId = array_get($condition, 'teacher_id', NULL);
        if ( !empty($teacherId) ) {
            $cond[] = ['id', '<>', $teacherId];
        }

        // 获取当前栏目包含的老师列表 （编辑时，当前记录除外）
        $teacherList = $this->teacher->getTeacherListByCondition($cond);

        $allUserList = self::getAllUserList();

        foreach ($teacherList as $teacher) {
            $user = array_get($allUserList, $teacher['user_id'], NULL);
            if ( !empty($user) ) {
                $allUserList = array_diff_key($allUserList, [
                    $teacher['user_id'] => $user,
                ]);
            }
        }

        $userList = array_values($allUserList);
        
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'user_list' => $userList,
            ],
        ];

        return $ret;
    }

    public function getTeacherInfo(int $teacherId)
    {
        try {
            $teacherInfo = $this->teacher->getTeacherInfo($teacherId);
            if (empty($teacherInfo)) {
                $ret = [
                    'code' => COLUMN_TEACHER_NOT_FOUND,
                ]; 
            } else {
                $ret = [
                    'code' => SYS_STATUS_OK,
                    'data' => [
                        'teacher_info' => $teacherInfo,
                    ],
                ];
            }
        } catch (Exception $e) {
            $ret = [
                'code' => COLUMN_TEACHER_NOT_FOUND,
            ];
        }

        return $ret;
    }

    public function getUserInfo(int $teacherId)
    {
        $teacherInfo = $this->teacher->getTeacherInfo($teacherId);
        if (empty($teacherInfo)) {
            throw new MatrixException('栏目老师不存在', COLUMN_TEACHER_NOT_FOUND);
        } else {
            $userId = array_get($teacherInfo, 'user_id');
            $userInfo = $this->user->getUserInfo($userId);
            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'teacher_info' => $teacherInfo,
                    'user_info' => $userInfo,
                ],
            ];
        }

        return $ret;
    }

    public function createTeacher(array $newTeacher)
    {
        $teacher = $this->teacher->createTeacher($newTeacher);
        if (empty($teacher)) {
            $ret = [
                'code' => COLUMN_TEACHER_EXISTS
            ];
            return $ret;
        }

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'teacher_info' => $teacher,
            ],
        ];

        return $ret;
    }

    public function updateTeacher(int $teacherId, array $teacherInfo)
    {
        $oldTeacherInfo = $this->teacher->getTeacherInfo($teacherId);
        if ( empty($oldTeacherInfo) ) {
            $ret = [
                'code' => COLUMN_TEACHER_UPDATE_FAILED,
            ];
            return $ret;
        }

        try {
            $teacher = $this->teacher->updateTeacher($teacherId, $teacherInfo);

            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'teacher_info' => $teacher,
                ],
            ];
        } catch (Exception $e) {
            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
            ];
        }

        return $ret;
    }

    public function activeTeacher(int $teacherId, int $active)
    {
        $oldTeacherInfo = $this->teacher->getTeacherInfo($teacherId);
        if ( empty($oldTeacherInfo) ) {
            $ret = [
                'code' => COLUMN_TEACHER_NOT_FOUND,
            ];

            return $ret;
        }

        try {
            $teacher = $this->teacher->activeTeacher($teacherId, $active);

            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'teacher_info' => $teacher,
                ],
            ];
        } catch (Exception $e) {
            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
            ];
        }

        return $ret;
    }

    public function getTeacherInfoByUserIdAndCategoryCode(int $teacherUserId, string $categoryCode)
    {
        $teacherInfo = $this->teacher->getTeacherInfoByUserIdAndCategoryCode($teacherUserId, $categoryCode);

        return $teacherInfo;
    }
}
