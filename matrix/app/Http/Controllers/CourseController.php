<?php

namespace Matrix\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Matrix\Contracts\CourseManager;
use Matrix\Contracts\CourseSystemManager;
use Matrix\Contracts\LogManager;
use Matrix\Contracts\UserManager;
use Matrix\Contracts\CourseVideoManager;
use Matrix\Contracts\ContentGuardContract;
use Matrix\Contracts\VideoManager;
use Matrix\Contracts\BossManager;
use Matrix\Services\UcService;
use Matrix\Exceptions\MatrixException;
use Exception;
use Log;

class CourseController extends Controller
{
    const SOURCETYPE = 'course';
    const ADD = 'add';
    const UPDATE = 'update';
    const DELETE = 'delete';
    const VIDEO_SERVICE = 'course';
    const URI = '/api/v2/coursesystem/{courseSystemCode}/course/{courseCode}';
    const MODEL_NAME = 'course';
    const WX_USER_AGENT_TYPE = 'wechat';
    const NORMAL_USER_AGENT_TYPE = 'normal';
    const COURSE_BG = '/files/public/course_bg_new.png';
    const CF_COURSE = '/files/public/cf_course.png';
    const XUEZHANFA_COURSE_CODE = 'xuezhanfa_course';

    private $request;
    private $courseManager;
    private $logManager;
    private $userManager;
    private $courseSystemManager;
    private $courseVideoManager;
    private $contentGuardContract;
    private $videoManager;
    private $bossManager;
    protected $blankRow;

    public function __construct(Request $request, CourseManager $courseManager, LogManager $logManager, UserManager $userManager, CourseSystemManager $courseSystemManager, CourseVideoManager $courseVideoManager, ContentGuardContract $contentGuardContract, VideoManager $videoManager, BossManager $bossManager)
    {
        $this->request = $request;
        $this->courseManager = $courseManager;
        $this->logManager = $logManager;
        $this->userManager = $userManager;
        $this->courseSystemManager = $courseSystemManager;
        $this->courseVideoManager = $courseVideoManager;
        $this->contentGuardContract = $contentGuardContract;
        $this->videoManager = $videoManager;
        $this->bossManager = $bossManager;
        $this->blankRow = [
            'service_code' => '',
            'uri' => '',
            'param1' => null,
            'param2' => null,
            'param3' => null,
        ];
    }

    public function create()
    {
        $originalData = '';
        $reqData = $this->request->validate([
            'name' => 'required|string',
            'code' => 'required|string',
            'description' => 'nullable',
            'full_text_description' => 'nullable',
            'course_system_code' => 'required|string',
            'service_code' => 'required|string',
            'sort_no' => 'required|integer',
        ]);
        $name = array_get($reqData, 'name');
        $code = array_get($reqData, 'code');
        $courseSystemCode = array_get($reqData, 'course_system_code');
        $description = array_get($reqData, 'description');
        $fullTextDescription = str_replace(config('app.tencent_img_domain_name'), config('cdn.cdn_url'), array_get($reqData, 'full_text_description'));
        $fullTextDescription = str_replace('tp=webp', '', $fullTextDescription);

        if($courseSystemCode === 'cf_course'){
            $backgroundPic = self::CF_COURSE;
        }else{
            $backgroundPic = self::COURSE_BG;
        }
        $serviceCode = array_get($reqData, 'service_code');
        $sortNo = array_get($reqData, 'sort_no');
        $userId = Auth::id();

        $this->blankRow['service_code'] = $serviceCode;
        $this->blankRow['uri'] = self::URI; 
        $this->blankRow['param1'] = $courseSystemCode;
        $this->blankRow['param2'] = $code;

        $this->logManager->createOperationLog(self::SOURCETYPE, $userId, $originalData, self::ADD);
        $createDataRes = $this->courseManager->create($name, $code, $description, $courseSystemCode, $serviceCode, $userId, $backgroundPic, $sortNo, $fullTextDescription);
        $codeStatus = array_get($createDataRes, 'code');
        if(!empty($codeStatus)){
            return $ret = ['code' => $codeStatus];
        }
        $createContentGuardRes = $this->contentGuardContract->grant($this->blankRow);
        $codeStatus = array_get($createContentGuardRes, 'code');
        if(!empty($codeStatus)){
            return $ret = ['code' => $codeStatus];
        }
        $createData = array_get($createDataRes, 'data.create_data');
        $ret =  [
            'code' => SYS_STATUS_OK,
            'data' => [
                'create_data' => $createData,
            ]
        ];
        return $ret;
    }


    public function update()
    {
        $reqData = $this->request->validate([
            'course_id' => 'required|integer',
            'name' => 'required|string',
            'code' => 'required|string',
            'description' => 'nullable',
            'full_text_description' => 'nullable',
            'course_system_code' => 'required|string',
            'service_code' => 'required|string', 
        ]);

        $courseId = array_get($reqData, 'course_id');
        $name = array_get($reqData, 'name');
        $code = array_get($reqData, 'code');
        $courseSystemCode = array_get($reqData, 'course_system_code');
        $description = array_get($reqData, 'description');
        $fullTextDescription = str_replace(config('app.tencent_img_domain_name'), config('cdn.cdn_url'), array_get($reqData, 'full_text_description'));
        $fullTextDescription = str_replace('tp=webp', '', $fullTextDescription);

        if($courseSystemCode === 'cf_course'){
            $backgroundPic = self::CF_COURSE;
        }else{
            $backgroundPic = self::COURSE_BG;
        }
        $serviceCode = array_get($reqData, 'service_code');
        $userId = Auth::id();
         
        $condition = [$courseId];
        $repData = $this->courseManager->getRecordsBeforeModify($condition);
        $originalData = json_encode(array_get($repData, 'data'));
        $this->logManager->createOperationLog(self::SOURCETYPE, $userId, $originalData, self::UPDATE);

        $updateDataRes = $this->courseManager->update($courseId, $name, $code, $description, $courseSystemCode, $userId, $serviceCode, $backgroundPic, $fullTextDescription);
        $codeStatus = array_get($updateDataRes, 'code');
        if(!empty($codeStatus)) return $ret = ['code' => $codeStatus];
        
        $updateData = array_get($updateDataRes, 'data.update_data');
        $courseCode = array_get($updateData[0], 'code');
        $courseVideoRes = $this->courseVideoManager->updateRecordByCode($code, $courseCode);

        $this->blankRow['service_code'] = $serviceCode;
        $this->blankRow['uri'] = self::URI; 
        $this->blankRow['param1'] = $courseSystemCode;
        $this->blankRow['param2'] = $code;
        $param1 = array_get($updateData[0], 'course_system_code');
        $condition = [
            'uri' => self::URI,
            'param1' => $param1,
            'param2' => $courseCode,
        ];
        $contentGuardRes = $this->contentGuardContract->update($condition, $this->blankRow);
        $contentGuardResCode = array_get($contentGuardRes, 'code');
        if(!empty($contentGuardResCode)) return $ret = ['code' => $contentGuardResCode];

        $updateData = array_get($updateDataRes, 'data.update_data');
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'update_data' => $updateData,
            ],
        ];
        return $ret;
    }


    public function remove($courseId, $courseCode)
    {
        $condition = [$courseId];

        $courseCodeList = [$courseCode];
        $videoSigninList = $this->courseVideoManager->getVideoSigninIdList($courseCodeList);
        $videoSigninIdList = array_get($videoSigninList, 'data'); 
        $condition = [
            'uri' => self::URI,
            'param2' => $courseCode,
        ];

        $deleteCourseRes = $this->courseManager->remove($courseId);

        $deleteContentGuard = $this->contentGuardContract->revoke($condition);

        $deleteCourseVideoRes = $this->courseVideoManager->removeCourseVideoByCode($courseCodeList);
   
        $deleteVideoSigninRes = $this->videoManager->removeVideoSigninById($videoSigninIdList);


        $ret = [
            'code' => SYS_STATUS_OK,
        ];

        return $ret;
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

            $courseList = $this->courseManager->getCourseListOfPaging($pageNo, $pageSize);
            $courseCnt = $this->courseManager->getCourseCnt();

            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'course_list' => $courseList,
                    'course_cnt' => $courseCnt,
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

    public function search()
    {
        $reqData = $this->request->validate([
            'page_no' => 'nullable|integer',
            'page_size' => 'nullable|integer',
            'course_name' => 'nullable|string',
            'course_system_code' => 'nullable|string',
        ]);


        try {
            $pageNo = array_get($reqData, 'page_no', 1);
            $pageSize = array_get($reqData, 'page_size', 10);

            $courseList = $this->courseManager->searchCourseList($pageNo, $pageSize, $reqData);
            $courseCnt = $this->courseManager->searchCourseCnt($reqData);

            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'course_list' => $courseList,
                    'course_cnt' => $courseCnt,
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

        $courseName = array_get($reqData, 'course_name', '');
        $courseSystemCode = array_get($reqData, 'course_system_code', '');
        $searchCourseList = $this->courseManager->search($courseName, $courseSystemCode);
        $this->checkServiceResult($searchCourseList, 'Course');
        $courseList = array_get($searchCourseList, 'data.course_list');
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'course_list' => $courseList,
            ],
        ];
        return $ret;
    }

    public function getOneInfo($courseId, $courseSystemId, $courseCode)
    {
        $repData = $this->courseManager->getOneInfo($courseId, $courseSystemId, $courseCode);    
        return $repData;
        $this->checkServiceResult($repData, 'Course');
        $oneCourseInfo = array_get($repData, 'data.one_course_info');
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'one_course_info' => $oneCourseInfo,
            ],
        ];
        return $ret;
    }

    public function checkCourseCodeUnique($courseCode)
    {
        $repData = $this->courseManager->checkCourseCodeUnique($courseCode);
        $this->checkServiceResult($repData, 'Course');
        $oneCourseInfo = array_get($repData, 'data.course_code_check_res');
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'check_res' => $oneCourseInfo,
            ],
        ];
        return $ret;
    }

    public function getServiceList()
    {
        return $this->bossManager->getServices(self::MODEL_NAME);
    }


    public function getCategoryList()
    {
        $categoryCodeList = [];
        $categoriesList = $this->videoManager->getCategoriesList();
        $categoriesList = array_get($categoriesList, 'categories');
        foreach($categoriesList as $category){
            if($category['code'] === self::XUEZHANFA_COURSE_CODE){
                $categoryCodeList[] = $category['code'];
            }
        }
        return $categoryCodeList;
    }


    public function apiGetXueZhanFaList(UcService $ucService, BossManager $bossManager, $courseSystemCode = '', $courseCode = '')
    {
        $h5Callback = $ucService->getH5EnterpriseLoginUrl();
        $callback = array_get($h5Callback, 'data.callback');

        $sessionId = $this->request->cookie('X-SessionId');

        if(empty($sessionId)){
            $ret['code'] = CMS_API_X_SESSIONID_NOT_FOUND;
            $ret['callback_url'] = $callback;
            $ret['data'] = 'Expired X-SessionId';
            return $this->respAdapter($ret);
            //return view('course.play_video', $ret);
        }

        $currentUserInfo = $ucService->getUserInfoBySessionId($sessionId);
        $currentOpenId = (string)array_get($currentUserInfo, 'data.user.openId');
        //$accessCodeList = $ucService->getAccessCodeByOpenId($currentOpenId);
        $accessCodeList = array_get($currentUserInfo, 'data.user.accessCodes', []); 
        if (empty($accessCodeList)) {
            $accessCodeList = ['basic'];
        }

        $xuezhanfaCourseCode = [self::XUEZHANFA_COURSE_CODE];
        $contentGuardList = $this->contentGuardContract->getOneCourseAccessCodeTree($accessCodeList);

        $this->checkServiceResult($contentGuardList, 'contentGuardList');
        $contentAccessCodeTree = array_get($contentGuardList, 'data.course_access_code_tree');
        $courseSystemStruct = $this->courseSystemManager->apiGetCourseSystemStruct($contentAccessCodeTree);
        $categoryCodeList = $this->getCategoryList();
        $videoSigninList = $this->videoManager->show($categoryCodeList);
        $videoSigninList = array_get($videoSigninList, 'videoSigninList');
        $courseVideoList = $this->courseVideoManager->apiGetCourseVideoList($videoSigninList);

        $courseCodeTree = array_values($contentAccessCodeTree);
        $courseCodeList = call_user_func_array('array_merge', $courseCodeTree);
        if($courseCode != 'null' && $courseSystemCode != 'null'){//判断是否传递指定课程code
            //判断是否该课程体系下存在该课程
            $checkRes = $this->courseManager->checkCourse($courseSystemCode, $courseCode);
            $checkRes = array_get($checkRes, 'data');
            if(empty($checkRes)){
                $ret = [
                    'code' => 401,
                    'msg' => '您没有权限查看该课程',
                    'data' => [],
                ];
                return $ret;
            }else{
                if(!in_array($courseCode, $courseCodeList )){
                    $ret = [
                        'code' => 401,
                        'msg' => '您没有权限查看该课程',
                        'data' => [],
                    ];
                    return $ret;
                }
            }
            $courseCode = [$courseCode];
        }else{
            if($courseSystemCode != 'null'){
                //courseCodeList为有权限看到的所有的课程
                $courseCode = $this->courseManager->getCourseCodeList($courseSystemCode, $courseCodeList);
                //如果为空则表示想要查看课程并不在有权限查看的课程之内
                if(empty($courseCode)){
                    $ret = [
                        'code' => 401,
                        'msg' => '您没有权限查看该课程',
                        'data' => [],
                    ];
                    return $ret;
                }
            }else{
                //将不同课程体系下的课程code合并
                $ret = [
                    'code' => 401,
                    'msg' => '您没有权限查看该课程',
                    'data' => [],
                ];
                return $ret;
            }
        }
        $courseList = $this->courseManager->apiGetCourseList($courseCode);
        if(empty($courseList)){
            $ret['code'] = 404;
            return $ret;
        }
        $courseWithCourseVideoStruct = $this->courseManager->apiGetCourseWithCourseVideoStruct($courseList, $courseVideoList);
        $categoryInfo = $this->appSetting($courseSystemCode);
        $ret = [
            'code' => SYS_STATUS_OK,
            'msg'  => 'success',
            'callback' => $callback,
            'data' => [
                'title' => empty($courseSystemCode) ? '' : $categoryInfo['pageTitle'],
                'summary' => empty($courseSystemCode) ? '' : $categoryInfo['pageDesc'],
                'columns' => $courseSystemStruct,
                'articles' => $courseWithCourseVideoStruct,
            ],
            'errors' => null,
        ];

        return $ret;
    }

    public function courseOrder()
    {
        $reqData = $this->request->validate([
            'sequence' => 'required|integer',
            'course_id' => 'required|integer',
        ]);
        $sequence = array_get($reqData, 'sequence');
        $courseId = array_get($reqData, 'course_id');
        $updateResp = $this->courseManager->updateOrder($sequence, $courseId);
        $code = array_get($updateResp, 'code');
        if(empty($code)) $ret = ['code' => SYS_STATUS_ERROR_UNKNOW];
        return $ret = ['code' => SYS_STATUS_OK];
    }


    public function appSetting($systemCode)
    {
        $appSetting = array(
            "shipinjiepan" => array(
                            "pageIcon" => null ,
                            "pageTitle" => "早间读报",
                            "pageDesc" => "一日之计在于晨，每天为你及时解读最新投资热点",
                          ),
            "wanjianliaogu" => array(
                            "pageIcon" => null,
                            "pageTitle" => "晚间聊股",
                            "pageDesc" => "总结一天行情，点评当天行情中盈利模式的表现，分享后期投资机会与风险",
                          ),
            "fupandianjin" => array(
                            "pageIcon" => null,
                            "pageTitle" => "复盘点金",
                            "pageDesc" => "结合当日行情，点评模式战法的应用要点",
                          ),
            "laomokanzhuli" => array(
                            "pageIcon" => null,
                            "pageTitle" => "老莫看主力",
                            "pageDesc" => "股票只要有主力在，就不要怕，跟随主力，咬紧主力！",
                          ),
            "redianweiwang" => array(
                            "pageIcon" => null,
                            "pageTitle" => "热点为王",
                            "pageDesc" => "股海茫茫，热点为王。热点选龙头，量化定买卖，专做主升浪！",
                          ),
            "moshijiaoxue" => array(
                            "pageIcon" => null,
                            "pageTitle" => "量化模式教学",
                            "pageDesc" => "教学视频，深度解析量化盈利模式应用原理及相关操作要点",
                          ),
            "jiaoyineican" => array(
                            "pageIcon" => null,
                            "pageTitle" => "交易内参",
                            "pageDesc" => "每个交易日早上提供，内容包括行情预判及建议的应对措施，同时公布众赢投顾独家市场情绪指数，及提供符合盈利模式的>股票池作为参考",
                          ),
            "chicanggenzong" => array(
                            "pageIcon" => null,
                            "pageTitle" => "持仓跟踪",
                            "pageDesc" => "就我们推荐的操作的个股，定期进行持仓跟踪",
                          ),
            "jiaoyitishi" => array(
                            "pageIcon" => null,
                            "pageTitle" => "股票池交易提示",
                            "pageDesc" => "不定期推荐股票池中出现的最合适交易机会",
                          ),
            "panzhongkuaixun" => array(
                            "pageIcon" => null,
                            "pageTitle" => "盘中快讯",
                            "pageDesc" => "第一时间推送市场相关资讯，机会与风险等",
                          ),
            "gegugenzong" => array(
                            "pageIcon" => null,
                            "pageTitle" => "案例复盘",
                            "pageDesc" => "股票池个股跟踪、复盘",
                          ),
            "zhouzhanbao" => array(
                            "pageIcon" => null,
                            "pageTitle" => "周战报",
                            "pageDesc" => "首席寄语、大势研判、个股淘金、情报侦察及精彩回顾",
                          ),
            "shipinzhanfa" => array(
                            "pageIcon" => null,
                            "pageTitle" => "视频战法",
                            "pageDesc" => "教学视频，深度解析盈利模式应用原理及相关操作要点",
                          ),
            "tuwenjiaoxue" => array(
                            "pageIcon" => null,
                            "pageTitle" => "图文教学",
                            "pageDesc" => "干货分享，交易技术",
                          ),
            "jiaoyicelue" => array(
                            "pageIcon" => null,
                            "pageTitle" => "交易策略",
                            "pageDesc" => "专属交易策略服务",
                          ),
            "panzhongzixun" => array(
                            "pageIcon" => null,
                            "pageTitle" => "盘中资讯",
                            "pageDesc" => "及时有料的盘中资讯",
                          ),
            "cyzb" => array(
                            "pageIcon" => "/files/public/portfolio.png",
                            "pageTitle" => "产业资本系列课程（新版）",
                            "pageDesc" => "深度解析产业资本金股的选股策略和操作要点",
                          ),
            "cyzb_course" => array(
                            "pageIcon" => "/files/public/portfolio.png",
                            "pageTitle" => "产业资本系列课程（新版）",
                            "pageDesc" => "深度解析产业资本金股的选股策略和操作要点",
                          ),
            "chanyeshipin" => array(
                            "pageIcon" => "/files/public/portfolio.png",
                            "pageTitle" => "价值先锋",
                            "pageDesc" => "专注产业资本行为跟踪，发现价值，挖掘金股！",
                          ),
            "chanyefengyun" => array(
                            "pageIcon" => "/files/public/portfolio.png",
                            "pageTitle" => "产业风云",
                            "pageDesc" => "产业风云带您追踪产业资本，分享市值成长！",
                          ),
            "basic" => array(
                            "pageIcon" => null,
                            "pageTitle" => "基础教学",
                            "pageDesc" => "投资者入门教程，轻松掌握专业基础",
                          ),
            "openning" => array(
                            "pageIcon" => "/files/public/portfolio.png",
                            "pageTitle" => "专家看盘",
                            "pageDesc" => "鲜活、及时的盘面剖析：光哥论市、价值掘金、顺势狙击...",
                          ),
            "it_course" => array(
                            "pageIcon" => "/files/public/portfolio.png",
                            "pageTitle" => "IT通讯",
                            "pageDesc" => "学无止境，磨刀不误砍柴工！",
                          ),
            "default" => array(
                            "pageIcon" => null,
                            "pageTitle" => "众赢投顾",
                            "pageDesc" => "和众汇富旗下专业资讯服务",
                          ),
        );

        return $appSetting[$systemCode];
    }

}
