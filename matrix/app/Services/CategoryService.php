<?php

namespace Matrix\Services;

use Matrix\Contracts\CategoryManager;
use Matrix\Models\Category;
use Matrix\Models\SubCategory;
use Matrix\Models\Teacher;
use Matrix\Models\User;
use Matrix\Models\CategoryGroup;
use Matrix\Models\TwitterGuard;
use Matrix\Models\PrivateMessageGuard;
use Auth;
use Exception;
use Illuminate\Support\Facades\DB;

use Matrix\Exceptions\MatrixException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CategoryService extends BaseService implements CategoryManager
{
    private $category;
    private $teacher;
    private $user;
    private $subCategory;
    private $categoryGroup;
    private $twitterGuard;
    private $privateMessageGuard;

    public function __construct(Category $category, SubCategory $subCategory, Teacher $teacher, User $user, CategoryGroup $categoryGroup, TwitterGuard $twitterGuard, PrivateMessageGuard $privateMessageGuard)
    {
        $this->category = $category;
        $this->teacher = $teacher;
        $this->user = $user;
        $this->subCategory = $subCategory;
        $this->categoryGroup = $categoryGroup;
        $this->twitterGuard = $twitterGuard;
        $this->privateMessageGuard = $privateMessageGuard;
    }

    public function getCategoryInfoByCode(string $code)
    {
        try {
            $categoryInfo = $this->category->getCategoryInfo($code);
        } catch (ModelNotFoundException $e) {
            throw new MatrixException('没找到这个分类', CONTENT_NOT_FOUND);
        }

        return $categoryInfo;
    }

    public function getCategoryList()
    {
        $categoryList = $this->category->getCategoryList();
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'category_list' => $categoryList,
            ],
        ];

        return $ret;
    }

    public function getMyCategoryList()
    {
        $userId = Auth::user()->id;
        $teacherList = $this->teacher->getTeacherListByUserId($userId);
        $categoryCodeList = array_column($teacherList, 'category_code');
        $categoryList = $this->category->getCategoryListByCodeList($categoryCodeList);
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'category_list' => $categoryList,
            ],
        ];

        return $ret;
    }

    public function getSubCategoryList()
    {
        $subCategoryList = $this->subCategory->getSubCategoryList();
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'sub_category_list' => $subCategoryList,
            ],
        ];

        return $ret;
    }

    public function getSubCategoryListByCategoryCode(string $categoryCode)
    {
        $subCategoryList = $this->subCategory->getSubCategoryListByCategoryCode($categoryCode);
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'sub_category_list' => $subCategoryList,
            ],
        ];

        return $ret;
    }

    public function getTeacherListByCategoryCode(string $categoryCode, string $openId = '')
    {
        $teacherList = $this->teacher->getTeacherListByCategoryCode($categoryCode);
        $userList = $this->user->getAllUserList();
        $userList = array_column($userList, NULL, 'id');

        if (!empty($openId)) {
            $privateMessageRequestList = $this->privateMessageGuard->getRequestList([
                'status' => [
                    PrivateMessageGuard::STATUS_REQUEST,
                    PrivateMessageGuard::STATUS_APPROVE,
                    PrivateMessageGuard::STATUS_REJECT,
                ],
                'open_id' => [$openId],
            ]);

            $pmRequestList = [];
            foreach ($privateMessageRequestList as $privateMessageRequest) {
                if (array_key_exists($privateMessageRequest['teacher_id'], $pmRequestList)) {
                    continue;
                }
                $pmRequestList[$privateMessageRequest['teacher_id']] = $privateMessageRequest['status'];
            }
        }

        foreach ($teacherList as &$teacher) {
            $teacher['name'] = $userList[$teacher['user_id']]['name'];
            $teacher['icon_url'] = empty($teacher['icon_url']) ? $userList[$teacher['user_id']]['icon_url'] : $teacher['icon_url'];
            if (!empty($openId) && array_key_exists($teacher['id'], $pmRequestList)) {
                $teacher['private_message'] = $pmRequestList[$teacher['id']];
            }
        }

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'teacher_list' => $teacherList,
            ],
        ];

        return $ret;
    }

    public function getTeacherListByCategoryCodeList(array $categoryCodeList)
    {
        $teacherList = $this->teacher->getTeacherListByCategoryCodeList($categoryCodeList);

        $userIdList = array_column($teacherList, 'user_id');
        $userList = $this->user->getUserListByUserIdList($userIdList);
        $userList = array_column($userList, NULL, 'id');

        foreach ($teacherList as &$teacher) {
            $teacher['icon_url'] = empty($teacher['icon_url']) ? $userList[$teacher['user_id']]['icon_url'] : $teacher['icon_url'];
        }

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'teacher_list' => $teacherList,
            ],
        ];

        return $ret;
    }

    public function getActiveSubCategoryListByCategoryCode(string $categoryCode)
    {
        $subCategoryList = $this->subCategory->getActiveSubCategoryListByCategoryCode($categoryCode);
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'sub_category_list' => $subCategoryList,
            ],
        ];

        return $ret;
    }

    public function getCategoryInfo(string $categoryCode, $openId)
    {
        $categoryInfo = $this->category->getCategoryInfo($categoryCode);
        $categoryInfo['follow_count'] = $this->twitterGuard->getTwitterFollowCount($categoryCode);

        try {
            $twitterGuard = $this->twitterGuard->getLastTwitterGuard((string)$openId, $categoryInfo['code']);
            if (TwitterGuard::STATUS_REQUEST == $twitterGuard['status'] && TwitterGuard::SOURCE_AUTO_PROGRAM == $twitterGuard['source_type']) {
                $categoryInfo['follow'] = TwitterGuard::STATUS_APPROVE;
            } else {
                $categoryInfo['follow'] = $twitterGuard['status'];
            }
        } catch (Exception $e) {}

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'category_info' => $categoryInfo,
            ],
        ];

        return $ret;
    }

    public function getTeacherById(int $teacherId)
    {
        $teacherInfo = $this->teacher->getTeacherInfo($teacherId);

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'teacher' => $teacherInfo,
            ],
        ];

        return $ret;
    }

    public function getCategoryListByGroupCode(string $categoryGroupCode, int $active = 0)
    {
        $categoryGroupList = $this->categoryGroup->getCategoryGroupListByCode($categoryGroupCode);

        $categoryCodeList = array_column($categoryGroupList, 'category_code');
        $categoryList = $this->category->getCategoryListByCodeList($categoryCodeList, $active);

        $twitterFollowCountList = $this->twitterGuard->getTwitterFollowCountList($categoryCodeList);
        $twitterFollowCountList = array_column($twitterFollowCountList, 'follow_cnt', 'category_code');

        foreach ($categoryList as &$category) {
            $category['follow_count'] = array_key_exists($category['code'], $twitterFollowCountList) ? $twitterFollowCountList[$category['code']] : 0;
        }

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'category_list' => $categoryList,
            ],
        ];

        return $ret;
    }

    public function getCategoryListByCodeList(array $codeList)
    {
        $categoryList = $this->category->getCategoryListByCodeList($codeList, 1);
        return $categoryList;
    }

    public function getTeacherListByIdList(array $teacherIdList)
    {
        $teacherList = $this->teacher->getTeacherListByIdList($teacherIdList);
        $userIdList = array_column($teacherList, 'user_id');
        $userList = $this->user->getUserListByUserIdList($userIdList);
        $userList = array_column($userList, NULL, 'id');

        foreach ($teacherList as &$teacher) {
            $teacher['name'] = $userList[$teacher['user_id']]['name'];
            $teacher['icon_url'] = empty($teacher['icon_url']) ? $userList[$teacher['user_id']]['icon_url'] : $teacher['icon_url'];
        }

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'teacher_list' => $teacherList,
            ],
        ];

        return $ret;
    }

    public function getCategoryListByUserId(int $userId)
    {
        $teacherList = $this->teacher->getTeacherListByUserId($userId);
        $categoryCodeList = array_column($teacherList, 'category_code');
        $categoryList = $this->category->getCategoryListByCodeList($categoryCodeList);

        return $categoryList;
    }

    public function getTeacherListByUserIdList(array $teacherUserIdList)
    {
        $teacherList = $this->teacher->getTeacherListByUserIdList($teacherUserIdList);

        return $teacherList;
    }

    public function searchCategoryList(array $condition)
    {
        $cond = [];
        $name = array_get($condition, 'name', NULL);
        if (!empty($name)) {
            $cond[] = ['name', 'like', "%$name%"];
        }

        $serviceKey = array_get($condition, 'service_key', NULL);
        if (!empty($serviceKey)) {
            $cond[] = ['service_key', '=', $serviceKey];
        }

        $categoryList = $this->category->getCateoryListByCondition($cond);

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'category_list' => $categoryList,
            ],
        ];

        return $ret;
    }

    public function getCategoryListOfPaging(int $pageNo, int $pageSize, array $credentials)
    {
        $cond = [];
        
        $name = array_get($credentials, 'name');
        if (!empty($name)) {
            $cond[] = ['name', 'like', "%$name%"];
        }

        $serviceKey = array_get($credentials, 'service_key');
        if (!empty($serviceKey)) {
            $cond[] = ['service_key', '=', $serviceKey];
        }

        $cond[] = ['is_system_generation', '<>', Category::IS_SYSTEM_GENERATION];

        $categoryList = Category::where($cond)
            ->orderBy('created_at', 'desc')
            ->skip($pageSize * ($pageNo - 1))
            ->take($pageSize)
            ->get()
            ->toArray();
        
        return $categoryList;
    }

    public function getCategoryCnt (array $credentials)
    {
        $cond = [];
        
        $name = array_get($credentials, 'name');
        if (!empty($name)) {
            $cond[] = ['name', 'like', "%$name%"];
        }

        $serviceKey = array_get($credentials, 'service_key');
        if (!empty($serviceKey)) {
            $cond[] = ['service_key', '=', $serviceKey];
        }

        $cond[] = ['is_system_generation', '<>', Category::IS_SYSTEM_GENERATION];

        $categoryCnt = Category::where($cond)->count();
        
        return $categoryCnt;
    }

    public function checkCategoryCodeUnique(string $categoryCode)
    {
        $checkRes = $this->category->checkCategoryCodeUnique($categoryCode);
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'category_code_check_res' => $checkRes,
            ],
        ];
        return $ret;
    }

    public function createCategory(array $newCategory)
    {
        $category = $this->category->createCategory($newCategory);
        if (empty($category)) {
            $ret = [
                'code' => COLUMN_CATEGORY_EXISTS
            ];
            return $ret;
        }

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'category_info' => $category,
            ],
        ];
        return $ret;
    }

    public function getCategoryInfoByCategoryId(int $categoryId)
    {
        $categoryInfo = $this->category->getCategoryInfoByCategoryId($categoryId);
        if (empty($categoryInfo)) {
            $ret = [
                'code' => COLUMN_CATEGORY_NOT_FOUND,
            ];
        } else {
            $categoryCode = array_get($categoryInfo, 'code');
            $teacherInfo = $this->teacher->getPrimaryTeacher($categoryCode);
            $categoryInfo['primary_teacher_id'] = empty($teacherInfo) ? NULL : array_get($teacherInfo, 'user_id');
            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'category_info' => $categoryInfo,
                ],
            ];
        }
        
        return $ret;
    }

    public function updateCategory(int $categoryId, array $categoryInfo)
    {
        $oldCategoryInfo = $this->category->getCategoryInfoByCategoryId($categoryId);
        if ( empty($oldCategoryInfo) ) {
            $ret = [
                'code' => COLUMN_CATEGORY_UPDATE_FAILED,
            ];
            return $ret;
        }

        try {
            DB::beginTransaction();
            $category = $this->category->updateCategory($categoryId, $categoryInfo);

            $parimaryTeacherId = array_get($categoryInfo, 'primary_teacher_id');
            $categoryCode = array_get($category, 'code');
            if ( !empty($parimaryTeacherId) ) {
                $this->teacher->setPrimaryTeacher($categoryCode, $parimaryTeacherId);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
            ];
            return $ret;
        }

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'category_info' => $category,
            ],
        ];

        return $ret;
    }

    public function getTeacherList(string $categoryCode)
    {
        $teacherListOfCategoryCode = $this->teacher->getAllTeacherListByCategoryCode($categoryCode);
        $teacherListOfCategoryCode = array_column($teacherListOfCategoryCode, null, 'user_id');

        $userListOfTeacher = $this->user->getTeacherInfo();

        $teacherList = [];
        foreach ($userListOfTeacher as $user) {
            if ( array_key_exists(array_get($user, 'id'), $teacherListOfCategoryCode) ) {
                $user['teacher_id'] = $teacherListOfCategoryCode[$user['id']]['id'];
                $teacherList[] = $user;
            }
        }

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'teacher_list' => $teacherList,
            ],
        ];

        return $ret;
    }
}
