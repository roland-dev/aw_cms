<?php

namespace Matrix\Http\Controllers;

use Illuminate\Http\Request;
use Matrix\Contracts\BossManager;

class BossController extends Controller
{
    private $bossManager;
    private $request;

    public function __construct(Request $request, BossManager $bossManager)
    {
        $this->request = $request;
        $this->bossManager = $bossManager;
    }

    public function getServiceList()
    {
        $serviceRes = $this->bossManager->getServiceList();
        $this->checkServiceResult($serviceRes, 'BossService');
        $serviceList = array_get($serviceRes, 'data');
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => $serviceList, 
        ];
        return $ret;
    }

    public function apiCourseSystemList()
    {
        $repData = $this->bossManager->apiCourseSystemList();    
        $this->checkServiceResult($repData, 'BossService');
        $courseSystemList = array_get($repData, 'data.course_system_list');
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'course_system_list' => $courseSystemList,
            ],
        ];
        return $ret;
    }

    public function apiCourseList($courseSystemCode)
    {
        $repData = $this->bossManager->apiCourseList($courseSystemCode); 
        $this->checkServiceResult($repData, 'BossService');
        $courseList = array_get($repData, 'data.course_list');
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'course_list' => $courseList,
            ],
        ];
        return $ret;
    }

    public function apiCourseVideoList($courseSystemCode, $courseCode)
    {
        $repData = $this->bossManager->apiCourseVideoList($courseCode);
        $this->checkServiceResult($repData, 'BossService');
        $courseVideoList = array_get($repData, 'data.course_video_list'); 
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'course_video_list' => $courseVideoList,
            ],
        ];
        return $ret;
    }

    public function apiGetXueZhanFa()
    {
        $repData = $this->bossManager->apiGetXueZhanFa();  
        return $repData;
    }
}
