<?php

namespace Matrix\Services;

use Illuminate\Support\Facades\Auth;
use Matrix\Exceptions\CourseSystemException;
use Matrix\Contracts\CourseSystemManager;
use Matrix\Models\CourseSystem;
use Matrix\Models\Course;
use Matrix\Models\CourseVideo;
use Matrix\Models\User;

class CourseSystemService extends BaseService implements CourseSystemManager
{
    const CATEGORY = 'xuezhanfa';
    private $courseSystem;
    private $user;
    private $course;

    public function __construct(CourseSystem $courseSystem, User $user, Course $course)
    {
        $this->courseSystem = $courseSystem; 
        $this->user = $user; 
        $this->course = $course;
    }

    public function create(string $name, string $code, int $userId, int $sortNo, string $categoryCode)
    {
        $createData = $this->courseSystem->createRecord($name, $code, $userId, $sortNo, $categoryCode);
        $code = array_get($createData, 'resp_code', []);
        if(!empty($code)){
            $ret = [
                'code' => $code,
                'data' => [
                      'create_data' => $createData,
                ],
            ];
        }else{
            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                      'create_data' => $createData,
                ],
            ];
        }

        return $ret;
    }

    public function getRecordsBeforeModify(array $condition)
    {
        $repData = $this->courseSystem->getRecordsBeforeModify($condition); 
        $ret = [
            'code' => SYS_STATUS_OK,
            'repData' => $repData,
        ];

        return $ret;
    }

    public function update(int $courseSystemId, string $name, string $code, int $userId, string $categoryCode)
    {
        $courseSystemInfo = $this->courseSystem->findRecordByCode($code);
        if(empty($courseSystemInfo)){
            $courseSystemInfo = $this->courseSystem->findRecordByCourseSystemId($courseSystemId);
            $active = array_get($courseSystemInfo[0], 'active');
            if( 0 === $active){
                return $ret = ['code' => SYS_STATUS_COURSE_SYSTEM_DELETE];
            }
            //$courseSystemCode = array_get($courseSystemInfo, 'code');
            //$courseList = $this->course->findRecordByCode($courseSystemCode);
            //if(!empty($courseList)){
            //    $updateCourse = $this->course->updateRecordByCode($code, $courseSystemCode);
            //}
            $updateData = $this->courseSystem->updateRecord($courseSystemId, $name, $code, $userId, $categoryCode);
        }else{
            $id = array_get($courseSystemInfo[0], 'id');
            $active = array_get($courseSystemInfo[0], 'active');
            if($courseSystemId === $id){
                if( 0 === $active){
                    return $ret = ['code' => SYS_STATUS_COURSE_SYSTEM_DELETE];
                }
                //$courseSystemCode = array_get($courseSystemInfo[0], 'code');
                //$courseList = $this->course->findRecordByCode($courseSystemCode);
                //if(!empty($courseList)){
                //    $updateCourse = $this->course->updateRecordByCode($code, $courseSystemCode);
                //}
                $updateData = $this->courseSystem->updateRecord($courseSystemId, $name, $code, $userId, $categoryCode);
            }else{
                if( 1 === $active){
                    return $ret = ['code' => SYS_STATUS_COURSE_SYSTEM_EXISTS];
                }
                //$courseSystemCode = array_get($courseSystemInfo[0], 'code');
                //$courseList = $this->course->findRecordByCode($courseSystemCode);
                //if(!empty($courseList)){
                //    $updateCourse = $this->course->updateRecordByCode($code, $courseSystemCode);
                //}
                $updateData = $this->courseSystem->updateRecord($courseSystemId, $name, $code, $userId, $categoryCode);
            }
        }

        if(!empty($updateData)){
            $ret = [
                'code' => SYS_STATUS_OK, 
                'data' => [
                    'update_data' => $courseSystemInfo,
                ],
            ];
        }else{
            $ret = [
                'code' => SYS_STATUS_SERVICE_ERROR,
            ];
        }

        return $ret;
    }

    public function remove(int $courseSystemId)
    {
        $removeData = $this->courseSystem->remove($courseSystemId);  
        $ret['code'] = empty($removeData) ? SYS_STATUS_SERVICE_ERROR : SYS_STATUS_OK;
        return $ret;
    }

    public function show()
    {
        $List = [];
        $courseSystemList = $this->courseSystem->show(); 
        $userList = $this->user->getUserList();
         
        $courseSystemList = array_column($courseSystemList, NULL, 'id');
        $userList = array_column($userList, NULL, 'id');

        foreach ($courseSystemList as $courseSystem) {
            $courseSystem['user_name'] = $userList[$courseSystem['creator_user_id']]['name'];
            $List[] = $courseSystem;
        }
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'course_system_list' => $List,
            ],
        ];
        return $ret;
     
    }

    public function getOneInfo(int $courseSystemId)
    {
        $courseSystemInfo = $this->courseSystem->getOneInfo($courseSystemId);
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'one_course_system' => $courseSystemInfo,
            ], 
        ];
 
        return $ret; 
    }

    public function apiGetCourseSystemCodeList(array $courseSystemCodeList)
    {
        $list = [];
        $courseSystemList = $this->courseSystem->showList(self::CATEGORY);
        $collection = collect($courseSystemList);
        $courseSystemList = $collection->sortBy('sort_no')->values()->all();
        $courseSystemList = array_column($courseSystemList, NULL, 'code');
        $codeList = array_column($courseSystemList, 'code');
        foreach($codeList as $index => $code){
            $list[$index]['category_key'] = $code;
            $list[$index]['category_name'] = $courseSystemList[$code]['name'];
            $list[$index]['granted'] = in_array($code, $courseSystemCodeList) ? 1 : 0 ;
        }
        return $list;
    }

    public function apiGetCourseSystemStruct(array $courseSystemStruct)
    {
        if(empty($courseSystemStruct)){
            return [
                ['column_key' => 'dapanfenxi', 'column_name' => '大盘分析'],
                ['column_key' => 'xuazhanfa', 'column_name' => '学战法', 'categories' => []],
            ];
        }
        $courseSystemCodeList = array_keys($courseSystemStruct);
        $courseSystemList = $this->apiGetCourseSystemCodeList($courseSystemCodeList);
        $list[] = ['column_key' => 'dapanfenxi', 'column_name' => '大盘分析'];
        $list[] = [
            'column_key' => 'xuezhanfa',
            'column_name' => '学战法',
            'categories'  => $courseSystemList,
        ];
        return $list;
    }

    public function checkCourseSystemCodeUnique(string $courseSystemCode)
    {
        $checkRes = $this->courseSystem->checkCourseSystemCodeUnique($courseSystemCode);
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'course_system_code_check_res' => $checkRes,
            ]
        ];

        return $ret;
    }

    public function updateOrder(int $sequence, int $courseSystemId)
    {
        $updateResp = $this->courseSystem->updateOrder($sequence, $courseSystemId);
        $ret = [
            'code' => $updateResp,
        ];
        return $ret;
    }


    public function getCourseSystemList()
    {
        $courseSystemList = $this->courseSystem->show(); 

        return $courseSystemList;
    }

    public function getCourseSystemByCode(string $courseSystemCode)
    {
        try {
            $courseSystem = CourseSystem::where('code', $courseSystemCode)->take(1)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new MatrixException("这个课程体系没找着: {$courseSystemCode}", SYS_STATUS_COURSE_NOT_EXISTS);
        }

        return $courseSystem;
    }

    public function getCourseSystemListOfPaging(int $pageNo, int $pageSize)
    {
        $courseSystemList = CourseSystem::where('active', 1)
            ->orderBy('updated_at', 'desc')
            ->skip($pageSize * ($pageNo - 1))
            ->take($pageSize)
            ->get()
            ->toArray();

            $userIdList = array_column($courseSystemList, 'creator_user_id');
            $userList = $this->user->getUserListByUserIdList($userIdList);
            $userList = array_column($userList, NULL, 'id');
            $userIdList = array_column($userList, 'id');

            foreach ($courseSystemList as &$courseSystem) {
                if (in_array(array_get($courseSystem, 'creator_user_id'), $userIdList)) {
                    $courseSystem['user_name'] = $userList[$courseSystem['creator_user_id']]['name'];
                }
            }

        return $courseSystemList;
    }


    public function getCourseSystemCnt()
    {
        $courseSystemCnt = CourseSystem::where('active', 1)->count();
        return $courseSystemCnt;
    }
}
