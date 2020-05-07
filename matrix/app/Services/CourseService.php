<?php

namespace Matrix\Services;

use Illuminate\Support\Facades\Auth;
use Matrix\Exceptions\CourseException;
use Matrix\Contracts\CourseManager;
use Matrix\Models\Course;
use Matrix\Models\CourseSystem;
use Matrix\Models\CourseVideo;
use Matrix\Models\User;
use Matrix\Models\ContentGuard;
use Matrix\Models\VideoSignin;

class CourseService extends BaseService implements CourseManager
{
    const URI = '/api/v2/courseSystem/{courseSystemCode}/course/{courseCode}';
    const NOT_EXISTS = 0;
    const ACTIVE_EXISTS = 1;
    const ACTIVE_NOT_EXISTS = 2;
    const COURSE_CODE = NULL;

    private $course;
    private $user;
    private $contentGuard;
    private $courseSystem;
    private $courseVideo;
    private $videoSignin;

    public function __construct(Course $course, CourseSystem $courseSystem, CourseVideo $courseVideo, User $user, ContentGuard $contentGuard, VideoSignin $videoSignin)
    {
        $this->course = $course;
        $this->user = $user;
        $this->contentGuard = $contentGuard;
        $this->courseSystem = $courseSystem;
        $this->courseVideo = $courseVideo;
        $this->videoSignin = $videoSignin;
    }

    public function findRecordByCode(string $code)
    {
        $courseInfo =  $this->course->findRecordByCode($code); 
        if(empty($courseInfo)){
            $ret = [
                'code' => self::NOT_EXISTS,
            ];
        }else{
            $active = array_get($courseInfo, 'active');
            empty($active) ? $ret['code'] = self::ACTIVE_EXISTS : $ret['code'] = self::ACTIVE_NOT_EXISTS;
        }
        return $ret;
    }

    public function create(string $name, string $code, $description, string $courseSystemCode, string $serviceCode, int $userId, string $backgroundPic, int $sortNo, string $fullTextDescription)
    {
        $createCourseData = $this->course->createCourse($name, $code, $description, $courseSystemCode, $userId, $backgroundPic, $sortNo, $fullTextDescription);
        $code = array_get($createCourseData, 'resp_code', []);
        if(empty($code)){
            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'create_data' => $createCourseData,
                ],
            ];
        }else{
            $ret = [ 'code' => $code ];   
        }
        return $ret;
    }

    public function getCourseVideoIdListByCode($courseCode)
    {
        $list = [];
        $courseVideoList = $this->courseVideo->getCourseVideoListByCode($courseCode);
        foreach($courseVideoList as $courseVideo){
            $list[] = $courseVideo['video_signin_id'];
        }
        return $list;
    }

    public function update(int $courseId, string $name, string $code, $description, string $courseSystemCode, int $userId, string $serviceCode, string $backgroundPic, string $fullTextDescription)
    {
        $courseInfo = $this->course->findRecordByCode($code);
        if(empty($courseInfo)){
            $courseInfo = $this->course->findRecordByCourseId($courseId);
            $active = array_get($courseInfo[0], 'active');
            if( 0 === $active){
                return $ret = ['code' => SYS_STATUS_COURSE_DELETE];
            }
            $updateData = $this->course->updateRecord($courseId, $name, $code, $description, $courseSystemCode, $userId, $backgroundPic, $fullTextDescription);
        }else{
            $id = array_get($courseInfo[0], 'id');
            $active = array_get($courseInfo[0], 'active');
            if($courseId === $id){
                if( 0 === $active){
                    return $ret = ['code' => SYS_STATUS_COURSE_DELETE];
                }
                $updateData = $this->course->updateRecord($courseId, $name, $code, $description, $courseSystemCode, $userId, $backgroundPic, $fullTextDescription);
            }else{
                if( 1 === $active){
                    return $ret = ['code' => SYS_STATUS_COURSE_EXISTS];
                }
                $updateData = $this->course->updateRecord($courseId, $name, $code, $description, $courseSystemCode, $userId, $backgroundPic, $fullTextDescription);
            }
        }
        if(empty($updateData)){
            $ret = [
                'code' => SYS_STATUS_SERVICE_ERROR,
            ];
        }else{
            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'update_data' => $courseInfo,
                ],
            ];
        }
        return $ret;
    }

    public function remove(int $courseId)
    {
        $deleteData = $this->course->remove($courseId);
        $ret['code'] = empty($deleteData) ? SYS_STATUS_SERVICE_ERROR : SYS_STATUS_OK;
        return $ret;
    }

    public function show()
    {
        $List = [];
        $courseList = $this->course->show(self::COURSE_CODE);
        $userList = $this->user->getUserList();
        //$contentGuardList = $this->contentGuard->show();
        $courseSystemList = $this->courseSystem->show();
        $courseList = array_column($courseList, NULL, 'id');
        $userList = array_column($userList, NULL, 'id');
        //$contentGuardList = array_column($contentGuardList, NULL, 'param2');
        $courseSystemList = array_column($courseSystemList, NULL, 'code');
        foreach($courseList as $course){
            $course['user_name'] = $userList[$course['creator_user_id']]['name'];
            $course['course_system_name'] = $courseSystemList[$course['course_system_code']]['name'];
            $course['course_system_id'] = $courseSystemList[$course['course_system_code']]['id'];
            //$course['param2'] = $contentGuardList[$course['code']]['param2'];
            //$course['content_guard_id'] = $contentGuardList[$course['code']]['id'];
            $List[] = $course;
        }
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'course_list' => $List,
            ],
        ];
        return $ret;
    }

    public function getRecordsBeforeModify(array $condition)
    {
        $repData = $this->course->getRecordsBeforeModify($condition);
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => $repData,
        ];
 
        return $ret;
    }

    public function search($courseName, $courseSystemCode)
    {
        $List = [];
        $searchCourseList = $this->course->search($courseName, $courseSystemCode);
        $searchCourseList = array_column($searchCourseList, NULL, 'id');
        if(!empty($searchCourseList)){
            $userList = $this->user->getUserList();
            $courseSystemList = $this->courseSystem->show();
            //$contentGuardList = $this->contentGuard->show();
            $userList = array_column($userList, NULL, 'id');
            $courseSystemList = array_column($courseSystemList, NULL, 'code');
            //$contentGuardList = array_column($contentGuardList, NULL, 'param2');
            foreach($searchCourseList as $course){
                $course['user_name'] = $userList[$course['creator_user_id']]['name'];
                $course['course_system_name'] = $courseSystemList[$course['course_system_code']]['name'];
                $course['course_system_id'] = $courseSystemList[$course['course_system_code']]['id'];
                //$course['param2'] = $contentGuardList[$course['code']]['param2'];
                //$course['content_guard_id'] = $contentGuardList[$course['code']]['id'];
                $List[] = $course;
            }
        }
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'course_list' => $List,
            ], 
        ];
        return $ret;
    }

    public function getOneInfo(int $courseId, int $courseSystemId, string $courseCode)
    {
        $oneCourseInfo = $this->course->getOneInfo($courseId);
        $oneCourseSystemInfo = $this->courseSystem->getOneInfo($courseSystemId);
        $courseSystemCode = array_get($oneCourseSystemInfo, 'code');
        $contentGuardInfo = $this->contentGuard->getOneInfo(self::URI, $courseSystemCode, $courseCode);
        $contentGuardServiceCode = array_get($contentGuardInfo[0], 'service_code');
        $contentGuardId = array_get($contentGuardInfo[0], 'id');
        $courseSystemName = array_get($oneCourseSystemInfo, 'name');
        $oneCourseInfo['content_guard_service_code'] =  $contentGuardServiceCode;
        $oneCourseInfo['content_guard_id'] =  $contentGuardId;
        $oneCourseInfo['course_system_name'] =  $courseSystemName;

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'one_course_info' => $oneCourseInfo,
            ], 
        ];
        return $ret;
    }

    public function getCourseList(string $courseSystemCode)
    {
        $codeList = [];
        $videoSigninIdList = [];
        $courseList = $this->course->getCourseListByCourseSystemCode($courseSystemCode);
        foreach($courseList as $course){
            $codeList[] = $course['code']; 
        } 
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'course_code_list' => $codeList,
            ],
        ];
        return $ret;
    }

    public function removeCourseByCode(string $courseSystemCode)
    {
        $deleteData = $this->course->removeCourseByCode($courseSystemCode);
        $ret['code'] = empty($deleteData) ? SYS_STATUS_SERVICE_ERROR : SYS_STATUS_OK;
        return $ret;
    }

    public function checkCourseCodeUnique(string $courseCode)
    {
        $checkRes = $this->course->checkCourseCodeUnique($courseCode);
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'course_code_check_res' => $checkRes,
            ],
        ];
        return $ret;
    }

    public function updateRecordByCode(string $newCode, string $oldCode)
    {
        $updateCourse = $this->course->updateRecordByCode($newCode, $oldCode);
        return $updateCourse;
    }

    public function apiGetCourseWithCourseVideoStruct(array $courseList, array $courseVideoList)
    {
        $codeList = array_keys($courseVideoList);//课程中存在课程视频的课程code值列表
        if( empty($courseList) ){
            return [];
        }

        $i=0;//pc端返回列表的课程数组的index值必须是依此的,ep.0,1,2,3 不能出现0,1,2,4,这种有跳跃的
        foreach($courseList as $index => $course){
            if(in_array($course['id'], $codeList)){//去掉课程中不包含视频的课程
                $list[$i]['is_grouping'] = 1;
                //$list[$index]['category_key'] = $course['code'];
                $list[$i]['category_key'] = $course['course_system_code'];
                $list[$i]['group_id'] = $course['id'];
                $list[$i]['group_name'] = $course['name'];
                $list[$i]['summary'] = $course['description'];
                //$list[$index]['poster'] = config('cdn.cdn_url').'/'.$course['background_picture'];
                $list[$i]['poster'] = config('cdn.cdn_url').$course['background_picture'];
                //$list[$index]['group_articles'] = in_array($course['course_system_code'], $codeList) ? $courseVideoList[$course['course_system_code']] : [];
                //$list[$index]['group_articles'] = in_array($course['id'], $codeList) ? $courseVideoList[$course['id']] : [];//去掉课程中不包含视频的课程
                $list[$i]['group_articles'] = $courseVideoList[$course['id']];
                $i++;
            }
        }
        return $list;
    }

    public function apiGetCourseList($courseCode)
    {
        $courseList = $this->course->show($courseCode);
        if(empty($courseList)){
            return [];
        }
        $collection = collect($courseList);
        $courseList = $collection->sortBy('sort_no')->values()->all();
        return $courseList;
    }

    public function updateOrder(int $sequence, int $courseId)
    {
        $updateResp = $this->course->updateOrder($sequence, $courseId);
        $ret = [
            'code' => $updateResp,
        ];
        return $ret;
    }

    public function checkCourse($courseSystemCode, $courseCode)
    {
        $checkCourse = $this->course->checkCourse($courseSystemCode, $courseCode);
        $ret = [
            'data' => $checkCourse,
        ];
        return $ret;
    }

    public function getCourseCodeList($courseSystemCode, $courseCodeList)
    {
        $partCourseList = $this->course->getCourseCodeListByCourseSystemCode($courseSystemCode);
        $partCourseCodeList = array_column($partCourseList, 'code');
        $codeList = self::filterCourseCodeWithoutRight($partCourseCodeList, $courseCodeList);
        return $codeList;
    }

    public function filterCourseCodeWithoutRight($partCourseCodeList, $allCourseCodeList)
    {
        $list = [];
        if(empty($partCourseCodeList) || empty($allCourseCodeList)){
            return [];
        }

        foreach($partCourseCodeList as &$courseCode){
            if(in_array($courseCode, $allCourseCodeList)){
                $list[] = $courseCode;
            }
        }
        return $list;
    }

    public function getCourseInfoByCode(string $code)
    {
        $courseInfo =  $this->course->findRecordByCode($code);
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => empty($courseInfo) ? [] : $courseInfo[0],
        ];
        return $ret;
    }

    public function getCourseListByCodeList(array $codeList)
    {
        $courseList = $this->course->getCourseListByCodeList($codeList);

        return $courseList;
    }

    public function getCourseListByCodeListOrderSort(array $codeList)
    {
        $courseList = $this->course->getCourseListByCodeListOrderSort($codeList);

        return $courseList;
    }

    public function getCourseListOfPaging(int $pageNo, int $pageSize)
    {
        $courseList = Course::where('active', 1)
            ->orderBy('updated_at', 'desc')
            ->skip($pageSize * ($pageNo - 1))
            ->take($pageSize)
            ->get()
            ->toArray();
        
        $userIdList = array_column($courseList, 'creator_user_id');
        $userList = $this->user->getUserListByUserIdList($userIdList);
        $userList = array_column($userList, NULL, 'id');
        $userIdList = array_column($userList, 'id');

        $courseSystemList = $this->courseSystem->show();
        $courseSystemList = array_column($courseSystemList, NULL, 'code');
        $courseSystemCodeList = array_column($courseSystemList, 'code');

        foreach ($courseList as &$course) {
            if (in_array(array_get($course, 'creator_user_id'), $userIdList)) {
                $course['user_name'] = $userList[$course['creator_user_id']]['name'];
            }

            if (in_array(array_get($course, 'course_system_code'), $courseSystemCodeList)) {
                $course['course_system_name'] = $courseSystemList[$course['course_system_code']]['name'];
                $course['course_system_id'] = $courseSystemList[$course['course_system_code']]['id'];
            }
        }
        
        return $courseList;
    }


    public function getCourseCnt()
    {
        $courseCnt = Course::where('active', 1)->count();
        return $courseCnt;
    }

    public function searchCourseList(int $pageNo, int $pageSize, array $credentials)
    {
        $cond = [];

        $courseName = array_get($credentials, 'course_name');
        if (!empty($courseName)) {
            $cond[]  = ['name', 'like', "%$courseName%"];
        }

        $courseSystemCode = array_get($credentials, 'course_system_code');
        if (!empty($courseSystemCode) && -1 != $courseSystemCode) {
            $cond[] = ['course_system_code', '=', $courseSystemCode];
        }

        $cond[] = ['active', '=', 1];

        $courseList = Course::where($cond)
            ->orderBy('updated_at', 'desc')
            ->skip($pageSize * ($pageNo - 1))
            ->take($pageSize)
            ->get()
            ->toArray();
        
        $userIdList = array_column($courseList, 'creator_user_id');
        $userList = $this->user->getUserListByUserIdList($userIdList);
        $userList = array_column($userList, NULL, 'id');
        $userIdList = array_column($userList, 'id');

        $courseSystemList = $this->courseSystem->show();
        $courseSystemList = array_column($courseSystemList, NULL, 'code');
        $courseSystemCodeList = array_column($courseSystemList, 'code');

        foreach ($courseList as &$course) {
            if (in_array(array_get($course, 'creator_user_id'), $userIdList)) {
                $course['user_name'] = $userList[$course['creator_user_id']]['name'];
            }

            if (in_array(array_get($course, 'course_system_code'), $courseSystemCodeList)) {
                $course['course_system_name'] = $courseSystemList[$course['course_system_code']]['name'];
                $course['course_system_id'] = $courseSystemList[$course['course_system_code']]['id'];
            }
        }

        return $courseList;
    }


    public function searchCourseCnt(array $credentials)
    {
        $cond = [];

        $courseName = array_get($credentials, 'course_name');
        if (!empty($courseName)) {
            $cond[] = ['name', 'like', "%$courseName%"];
        }

        $courseSystemCode = array_get($credentials, 'course_system_code');
        if (!empty($courseSystemCode)) {
            $cond[] = ['course_system_code', '=', $courseSystemCode];
        }

        $cond[] = ['active', '=', 1];

        $courseCnt = Course::where($cond)->count();

        return $courseCnt;
    }
}
