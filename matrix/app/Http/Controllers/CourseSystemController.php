<?php

namespace Matrix\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Matrix\Contracts\CourseSystemManager;
use Matrix\Contracts\LogManager;
use Matrix\Contracts\UserManager;
use Matrix\Contracts\CourseManager;
use Matrix\Contracts\CourseVideoManager;
use Matrix\Contracts\VideoManager;
use Matrix\Contracts\BossManager;
use Matrix\Contracts\ContentGuardContract;
use Matrix\Exceptions\MatrixException;
use Exception;
use Log;

class CourseSystemController extends Controller
{

    const SOURCETYPE = 'courseSystem';
    const ADD = 'add';
    const UPDATE = 'update';
    const DELETE = 'delete';
    const URI = '/api/v2/coursesystem/{courseSystemCode}/course/{courseCode}'; 
    const MODEL_NAME = 'course';

    private $request;
    private $courseSystemManager;
    private $logManager;
    private $userManager;
    private $contentGuardContract;
    private $courseManager;
    private $bossManager;

    public function __construct(Request $request, CourseSystemManager $courseSystemManager, LogManager $logManager, UserManager $userManager, ContentGuardContract $contentGuardContract, CourseManager $courseManager, BossManager $bossManager)
    {
        $this->request = $request;
        $this->courseSystemManager = $courseSystemManager;
        $this->logManager = $logManager;
        $this->userManager = $userManager;
        $this->contentGuardContract = $contentGuardContract;
        $this->courseManager = $courseManager;
        $this->bossManager = $bossManager;
    }

    public function create()
    {
        $originalData = '';
        $reqData = $this->request->validate([
            'name' => 'required|string',
            'code' => 'required|string',
            'sort_no' => 'required|integer',
            'category_code' => 'required|string',
        ]);
        $name = array_get($reqData, 'name');
        $code = array_get($reqData, 'code');
        $sortNo = array_get($reqData, 'sort_no');
        $categoryCode = array_get($reqData, 'category_code');
        $userId = Auth::id();

        $this->logManager->createOperationLog(self::SOURCETYPE, $userId, $originalData, self::ADD);
        $createDataRes = $this->courseSystemManager->create($name, $code, $userId, $sortNo, $categoryCode);
        $codeStatus = array_get($createDataRes, 'code'); 
        $createData = array_get($createDataRes, 'data.create_data'); 
        return [
            'code' => $codeStatus,
            'data' => [
                'create_data' => $createData,
            ],
        ];
    }

    public function update()
    {
        $reqData = $this->request->validate([
            'course_system_id' => 'required|integer',
            'name' => 'required|string',
            'code' => 'required|string',
            'category_code' => 'required|string',
        ]);
        $courseSystemId = array_get($reqData, 'course_system_id');
        $name = array_get($reqData, 'name');
        $code = array_get($reqData, 'code');
        $categoryCode = array_get($reqData, 'category_code');
        $userId = Auth::id();
        $condition = [$courseSystemId];
        $repData = $this->courseSystemManager->getRecordsBeforeModify($condition);
        $originalData = json_encode(array_get($repData, 'repData'));
        $this->logManager->createOperationLog(self::SOURCETYPE, $userId, $originalData, self::UPDATE);

        $updateDataRes = $this->courseSystemManager->update($courseSystemId, $name, $code, $userId, $categoryCode);
        $codeStatus = array_get($updateDataRes, 'code');
        if(!empty($codeStatus)) return $ret = ['code' => $codeStatus];

        $updateData = array_get($updateDataRes, 'data.update_data', []);
        $courseSystemCode = array_get($updateData[0], 'code');
        $courseRes = $this->courseManager->updateRecordByCode($code, $courseSystemCode);

        $condition = [
            'uri' => self::URI,
            'param1' => $courseSystemCode,
        ];
        $newData = [
            'param1' => $code,
        ];

        $contentGuardRes = $this->contentGuardContract->update($condition, $newData); 
        if(!empty($contentGuardResCode)) return $ret = ['code' => $contentGuardResCode];

        return [
            'code' => SYS_STATUS_OK,
            'data' => [
                'update_data' => $updateDataRes,
            ],
        ];
    } 

    public function remove(CourseVideoManager $courseVideoManager, VideoManager $videoManager, $courseSystemId, $courseSystemCode)
    {
        $condition = [$courseSystemId];

        $courseList = $this->courseManager->getCourseList($courseSystemCode);
        $courseCodeList = array_get($courseList, 'data.course_code_list');
        $videoSigninList = $courseVideoManager->getVideoSigninIdList($courseCodeList);
        $videoSigninIdList = array_get($videoSigninList, 'data');
        $condition = [
            'uri' => self::URI,
            'param1' => $courseSystemCode,
        ];

        $deleteCourseSystemRes = $this->courseSystemManager->remove($courseSystemId);

        $deleteCourseRes = $this->courseManager->removeCourseByCode($courseSystemCode);

        $deleteContentGuard = $this->contentGuardContract->revoke($condition);

        $deleteCourseVideoRes = $courseVideoManager->removeCourseVideoByCode($courseCodeList);

        $deleteVideoSigninRes = $videoManager->removeVideoSigninById($videoSigninIdList);

        return [
            'code' => SYS_STATUS_OK,
        ];
    }

    public function show()
    {
        $credentials = $this->request->validate([
            'page_no' => 'nullable|integer',
            'page_size' => 'nullable|integer',
        ]);

        try {
            $pageNo = array_get($credentials, 'page_no', 1);
            $pageSize = array_get($credentials, 'page_size', 10);

            $courseSystemList = $this->courseSystemManager->getCourseSystemListOfPaging($pageNo, $pageSize);
            $courseSystemCnt = $this->courseSystemManager->getCourseSystemCnt();

            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'course_system_list' => $courseSystemList,
                    'course_system_cnt' => $courseSystemCnt,
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

    public function getOneInfo($courseSystemId)
    {
        $repData = $this->courseSystemManager->getOneInfo($courseSystemId); 
        $this->checkServiceResult($repData, 'CourseSystem');
        $oneCourseSystem = array_get($repData, 'data.one_course_system');
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'one_course_system' => $oneCourseSystem,
            ],
        ];

        return $ret;
    }

    public function checkCourseSystemCodeUnique($courseSystemCode)
    {
        $repData = $this->courseSystemManager->checkCourseSystemCodeUnique($courseSystemCode);
        $this->checkServiceResult($repData, 'CourseSystem');
        $oneCourseSystemInfo = array_get($repData, 'data.course_system_code_check_res');
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'check_res' => $oneCourseSystemInfo,
            ],
        ];
        return $ret;
    }

    public function courseSystemOrder()
    {
        $reqData = $this->request->validate([
            'sequence' => 'required|integer',
            'course_system_id' => 'required|integer',
        ]);
        $sequence = array_get($reqData, 'sequence');
        $courseSystemId = array_get($reqData, 'course_system_id');
        $updateResp = $this->courseSystemManager->updateOrder($sequence, $courseSystemId);
        $code = array_get($updateResp, 'code');
        if(empty($code)) $ret = ['code' => SYS_STATUS_ERROR_UNKNOW ];

        return $ret = ['code' => SYS_STATUS_OK ];
    }

    public function getCategoryList()
    {
        return $this->bossManager->getCategoryList(self::MODEL_NAME);
    }

    public function getAllCourseSystemList()
    {
        try {
            $courseSystemList = $this->courseSystemManager->getCourseSystemList();
            
            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'course_system_list' => $courseSystemList,
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

