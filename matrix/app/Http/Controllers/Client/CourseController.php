<?php

namespace Matrix\Http\Controllers\Client;

use Illuminate\Http\Request;

use Matrix\Contracts\UcManager;
use Matrix\Contracts\UserManager;
use Matrix\Contracts\VideoManager;
use Matrix\Contracts\CourseManager;
use Matrix\Contracts\CourseVideoManager;
use Matrix\Contracts\CourseSystemManager;
use Matrix\Contracts\ContentGuardContract;
use Matrix\Models\UserGroup;
use Matrix\Contracts\UserGroupManager;
use Exception;
use Log;

use Matrix\Exceptions\MatrixException;
use Matrix\Exceptions\PermissionException;
use Matrix\Exceptions\TalkshowException;
use Matrix\Contracts\InteractionContract;
use Matrix\Models\ArticleReply;
use Matrix\Contracts\CustomerManager;

class CourseController extends Controller
{
    //
    const XUEZHANFA_COURSE_CODE = 'xuezhanfa_course';
    const INIT = 0;
    const URI = '/api/v2/coursesystem/{courseSystemCode}/course/{courseCode}';
    const TENCENT_URL = 'v.qq.com';
    const IFRAME = 'iframe/player';
    const GENSEE = 'fhcj.gensee.com';
    const VHALL = 'live.vhall.com';
    const COURSE_TYPE = 'course';
    const USER_GROUP_CODE_SUPERMAN_TAG = 'teacher_superman_tag';
    const COURSE_DESCRIPTION_URI = '/api/v2/client/course/';

    private $request;
    private $videoManager;
    private $courseManager;
    private $courseVideoManager;
    private $userGroup;
    private $interactionContract;
    protected $customer;

    public function __construct (
                                     Request $request,
                                     VideoManager $videoManager,
                                     CourseManager $courseManager,
                                     CourseVideoManager $courseVideoManager,
                                     UserGroupManager $userGroup,
                                     InteractionContract $interactionContract,
                                     CustomerManager $customer
                                 )
    {
        $this->request = $request;
        $this->videoManager = $videoManager;
        $this->courseManager = $courseManager;
        $this->courseVideoManager = $courseVideoManager;
        $this->userGroup = $userGroup;
        $this->interactionContract = $interactionContract;
        $this->customer = $customer;
    }

    public function urlFilter(string $url)
    {
        if(strpos($url, 'http') !== false){
            return $url;
        }

        if(strpos($url, 'https') !== false){
            return $url;
        }

        if(strpos($url, '/files') !== false){
            $url = str_replace('/files', '', $url);
            $url = config('cdn.cdn_url').$url;
            return $url;
        }

        return $url;
    }

    /**
     * @SWG\Get(
     *  path="/api/v2/client/course/{courseCode}",
     *  tags={"Course"},
     *  description="获取课程详情，请求该接口需要X-SessionId",
     *  operationId="Client\CourseController\getCourseInfo",
     *  produces={"application/json"},
     *  consumes={"application/json"},
     *  summary="根据courseCode获取课程详情",
     *  @SWG\Parameter(
     *      in="path",
     *      name="courseCode",
     *      type="string",
     *      description="课程代码",
     *      required=true,
     *  ),
     *  @SWG\Response(
     *      response=200,
     *      description="OK",
     *  )
     * )
     */

    public function getCourseInfo(string $courseCode)
    {
        $courseInfoData = $this->courseManager->getCourseInfoByCode($courseCode);
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'course' => array_get($courseInfoData, 'data'),
            ],
        ];

        unset($ret['data']['course']['id']);
        unset($ret['data']['course']['creator_user_id']);
        unset($ret['data']['course']['active']);
        if (!empty(array_get($ret, 'data.course'))) {
            $ret['data']['course']['background_picture'] = $this->urlFilter($ret['data']['course']['background_picture']);
            $ret['data']['course']['full_text_description'] = sprintf('%s%s%s%s', config('app.h5_api_url'), self::COURSE_DESCRIPTION_URI, $courseCode, '/description');
        }

        return $ret;
    }


    /**
    *获取课程简介（富文本）
    *@return blade
    */
    public function getCourseDescription(string $courseCode)
    {
        $courseInfoData = $this->courseManager->getCourseInfoByCode($courseCode);
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'course' => array_get($courseInfoData, 'data'),
            ],
        ];

        unset($ret['data']['course']['id']);
        unset($ret['data']['course']['creator_user_id']);
        unset($ret['data']['course']['active']);
        unset($ret['data']['course']['name']);
        unset($ret['data']['course']['code']);
        unset($ret['data']['course']['description']);
        unset($ret['data']['course']['course_system_code']);
        unset($ret['data']['course']['background_picture']);
        unset($ret['data']['course']['sort_no']);

        return view('course.description', $ret);
    }


    public function getCourseList (UserManager $userManager, CourseVideoManager $courseVideoManager,  CourseManager $courseManager, UcManager $ucenter, ContentGuardContract $contentGuardService, CourseSystemManager $courseSystemManager)
    {
        $credentials = $this->request->validate([
            'teacher_userid' => 'required|string',
        ]);

        /**
        *
        * Get the X-SessionId from Http Headers and get yr service code list from uc by X-SessionId.
        *
        * If no X-SessionId in Http Headers, then yr service code list always ['basic'].
        *
        */
        $sessionId = $this->request->header('X-SessionId');
        if (empty($sessionId)) {
            $serviceCodeList = ['basic'];
        } else {
            $currentUserInfo = $ucenter->getUserInfoBySessionId($sessionId);
            $currentOpenId = (string)array_get($currentUserInfo, 'data.user.openId');
            //$serviceCodeList = $ucenter->getAccessCodeByOpenId($currentOpenId);
            $serviceCodeList = array_get($currentUserInfo, 'data.user.accessCodes', []); 
        }

        $contentGuardTreeData = $contentGuardService->getOneCourseAccessCodeTree($serviceCodeList);
        $contentGuardTree = array_get($contentGuardTreeData, 'data.course_access_code_tree');

        $contentGuardList = [];
        foreach ($contentGuardTree as $contentGuardBlock) {
            $contentGuardList = array_merge($contentGuardList, $contentGuardBlock);
        }

        $teacherUserInfoData = $userManager->getUserByEnterpriseUserId($credentials['teacher_userid']);

        $teacherUserInfo = array_get($teacherUserInfoData, 'data');
        $teacherUserId = array_get($teacherUserInfo, 'id');

        $courseVideoList = $courseVideoManager->getVideoList();

        $videoIdList = array_column($courseVideoList, 'video_signin_id');
        $videoList = $this->videoManager->getVideoListByIdListAndUserId($videoIdList, $teacherUserId);
        $videoList = array_column($videoList, NULL, 'id');
        $videoIdList = array_column($videoList, 'id');

        $courseVideoIdList = array_column($videoList, 'id');

        $courseCodeList = [];
        foreach ($courseVideoList as $courseVideo) {
            if (in_array($courseVideo['video_signin_id'], $courseVideoIdList)) {
                $courseCodeList[] = $courseVideo['course_code'];
            }
        }

        $courseList = $courseManager->getCourseListByCodeList($courseCodeList);
        $courseCodeList = array_column($courseList, 'code');

        $courseAccessCodeList = $contentGuardService->getCourseAccessCodeList();
        $courseSystemList = $courseSystemManager->getCourseSystemList();
        $courseSystemList = array_column($courseSystemList, NULL, 'code');

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'course_list' => [],
            ],
        ];
        foreach ($courseList as $course) {
            $accessDeny = 0;
            if (!in_array($course['code'], $contentGuardList)) {
                $accessDeny = 1;
            }
            $courseRes = [
                'is_grouping' => 1,
                'category_key' => $course['course_system_code'],
                'group_id' => $course['code'],
                'group_name' => $course['name'],
                'summary' => $course['description'],
                'poster' => $course['background_picture'],
                'group_articles' => [],
            ];
            foreach ($courseVideoList as $courseVideo) {
                if (!in_array($courseVideo['video_signin_id'], $videoIdList)) {
                    continue;
                }
                if ($courseVideo['course_code'] != $course['code']) {
                    continue;
                }
                $video = [
                    'detail_id' => $videoList[$courseVideo['video_signin_id']]['video_key'],
                    'column_name' => '学战法',
                    'category_key' => $course['course_system_code'],
                    'category_name' => array_get(array_get($courseSystemList, $course['course_system_code']), 'name'),
                    'title' => $videoList[$courseVideo['video_signin_id']]['title'],
                    'summary' => $videoList[$courseVideo['video_signin_id']]['description'],
                    'media_type' => 'news',
                    'thumb_cdn_url' => $this->urlFilter($courseVideo['picture_path']),
                    'thumb_local_url' => '',
                    'access_level' => array_get($courseAccessCodeList, array_get($course, 'code')) ?? 'basic',
                    'access_deny' => $accessDeny,
                    'add_time' => $videoList[$courseVideo['video_signin_id']]['created_at'],
                    'tag' => $courseVideo['tag'],
                    'jump_type' => 'common_web',
                    'guide_media' => 'page',
                ];

                $getParam = [
                    'app' => 'basic',
                    'vid' => $videoList[$courseVideo['video_signin_id']]['video_key'],
                ];

                // $video['source_url'] = sprintf("%s%s", config('video.video.h5_url'), http_build_query($getParam));

                $video['source_url'] = sprintf('%s/api/v2/client/course/detail/%s', config('app.url'), $videoList[$courseVideo['video_signin_id']]['video_key']);
                $courseRes['group_articles'][] = $video;
            }
            $ret['data']['course_list'][] = $courseRes;
        }

        return $ret;
    }

    /**
    *获取课程视频列表
    *@param $courseCode string 课程代码
    *@param x-sessionid string 客户信息x-sessionid
    *@return array
    */
    public function getOneCourseVideoList(CourseVideoManager $courseVideoManager, CourseManager $courseManager, ContentGuardContract $contentGuardService,CourseSystemManager $courseSystemManager, UcManager $ucenter, $courseCode)
    {

        $sessionId = $this->request->header('X-SessionId');

        if (empty($sessionId)) {
            abort(401);
        }

        try {
            $ucenter->getUserInfoBySessionId($sessionId);
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
                'msg' => '未知错误'
            ];
        }

        try{
            /**
            *
            * Get the X-SessionId from Http Headers and get yr service code list from uc by X-SessionId.
            *
            * If no X-SessionId in Http Headers, then yr service code list always ['basic'].
            *
            */
            // $sessionId = $this->request->header('X-SessionId');

            // if (empty($sessionId)) {
            //     $serviceCodeList = ['basic'];
            // } else {
            //     $currentUserInfo = $ucenter->getUserInfoBySessionId($sessionId);
            //     $currentOpenId = (string)array_get($currentUserInfo, 'data.user.openId');
            //     $serviceCodeList = $ucenter->getAccessCodeByOpenId($currentOpenId);
            // }

            //权限树，每个权限下面对应那些课程
            //客户所拥有的权限下对应那几个课程，当前要查看的课程是否在客户可查看范围内
            // $contentGuardTreeData = $contentGuardService->getOneCourseAccessCodeTree($serviceCodeList);
            // $contentGuardTree = array_get($contentGuardTreeData, 'data.course_access_code_tree');

            // $contentGuardList = [];
            // foreach ($contentGuardTree as $contentGuardBlock) {
            //     $contentGuardList = array_merge($contentGuardList, $contentGuardBlock);
            // }

            // if(!in_array($courseCode, $contentGuardList)){
            //     throw new PermissionException ('无权查看视频信息', VIDEO_URL_NO_PERMISSION);
            // }

            // $categoryCodeList = $this->getCategoryList();

            // $videoSigninList = $this->videoManager->show($categoryCodeList);

            // $videoSigninList = array_get($videoSigninList, 'videoSigninList');

            $courseVideoList = $courseVideoManager->apiGetCourseVideoList([]);

            $list = [];

            foreach($courseVideoList as $courseVideo){
                foreach($courseVideo as $video){
                    if($video['course_code'] === $courseCode){
                        $list[] = $video;
                    }
                }
            }

            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => $list,
            ];
        } catch (MatrixException $e) {
            Log::error($e->getMessage(), [$e]);

            $ret = [
                'code' => VIDEO_URL_NO_PERMISSION,
                'msg' => $e->getMessage(),
            ];
        }catch(Exception $e){
            Log::error($e->getMessage(), [$e]);

            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => $e->getMessage(),
            ];
        }

        return $ret;
    }

    /**
    *获取学战法栏目分类
    *@return array
    */
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

    public function getCourseVideoDetail(UcManager $ucService, ContentGuardContract $contentGuardService, UserManager $userManager, $videoKey)
    {
        try{
            $loginUrl = $this->h5WechatAutoLogin($this->request, $ucService);

            if(!empty($loginUrl)){
                return redirect()->away($loginUrl);
            }

            //$reqData = $this->request->validate([
            //    'video_key' => 'required|string',
            //    'last_reply_id' => 'integer',
            //    'page_size' => 'integer',
            //]);

            //$videoKey = array_get($reqData, 'video_key');

            $udid = '';

            //$lastReplyId = array_get($reqData, 'last_reply_id', 0);

            //$pageSize = array_get($reqData, 'page_size', 20);

            //$h5Callback = $ucService->getH5EnterpriseLoginUrl();

            //$callback = array_get($h5Callback, 'data.callback');

            $currentOpenId = '';

            $sessionId = '';

            $isTeacher = 0;

            $sessionId = $this->request->cookie('X-SessionId');

            if(!empty($sessionId)){
                $sessionIdExpired = time() + 60 * 60 * 10;
                setcookie('X-SessionId', $sessionId, $sessionIdExpired, config('session.path'), config('session.domain'), false, true);
                $currentUserInfo = $ucService->getUserInfoBySessionId($sessionId);
                $enterpriseUserId = array_get($currentUserInfo, 'data.user.qyUserId');
                $userMobile = array_get($currentUserInfo, 'data.user.mobile');
            }

            if (isset($enterpriseUserId) && !empty($enterpriseUserId)) {
                try{
                    $teacherUserData = $userManager->getUserByEnterpriseUserId($enterpriseUserId);

                    $teacherUserId = array_get($teacherUserData, 'data.id');

                    $teacherUserActive = array_get($teacherUserData, 'data.active');

                    if (!empty($teacherUserId) && !empty($teacherUserActive)) {
                        $teacherUserListData = $this->userGroup->getUserListByUserGroupCode(UserGroup::USER_GROUP_CODE_APPROVED_REPLY);

                        $teacherUserList = array_get($teacherUserListData, 'user_list');

                        if (!empty($teacherUserList)) {
                            $userIdList = array_column($teacherUserList, 'id');

                            if (in_array($teacherUserId, $userIdList)) {
                                $isTeacher = 1;
                            }
                        }
                    }
                }catch(MatrixException $e){
                    Log::info($e->getMessage());
                }catch(Exception $e){
                    $ret = [
                        'code' => SYS_STATUS_ERROR_UNKNOW,
                        'msg' => $e->getMessage(),
                    ];

                    return $ret;
                }
            }

            if (!isset($enterpriseUserId) || !empty($enterpriseUserId)) {
                $currentOpenId = isset($currentUserInfo) ? (string)array_get($currentUserInfo, 'data.user.openId') : '';
                //$accessCodeList = $ucService->getAccessCodeByOpenId($currentOpenId);
                $accessCodeList = $currentOpenId ?  array_get($currentUserInfo, 'data.user.accessCodes',[]) : []; 
            }


            if (!isset($accessCodeList) || empty($accessCodeList)) {
                $accessCodeList = ['basic'];
            }

            $customerName = isset($currentUserInfo) ? (string)array_get($currentUserInfo, 'data.user.name') : '';

            $videoSigninInfo = $this->videoManager->getVideoSigninInfo($videoKey);

            if(empty(array_get($videoSigninInfo, 'data'))){
                 throw new TalkshowException('没有找到该条视频', 404);
            }

            $videoSigninId = array_get($videoSigninInfo, 'data.id', '');

            $isLike = $this->interactionContract->getLikeRecord($videoSigninId, self::COURSE_TYPE, $currentOpenId, $udid);

            $likeSum = $this->interactionContract->getLikeSum($videoSigninId, self::COURSE_TYPE);

            $courseVideoInfo = $this->courseVideoManager->getCourseVideoInfo(self::INIT, $videoSigninId);

            $courseCode = array_get($courseVideoInfo, 'data.course_video_info.course_code');

            $courseSystemInfo = $this->courseManager->getCourseInfoByCode($courseCode);

            $courseSystemCode = array_get($courseSystemInfo, 'data.course_system_code');

            $contentGuardInfo = $contentGuardService->getOneAccessCode($courseSystemCode, $courseCode, self::URI);

            $serviceCode = array_get($contentGuardInfo, 'service_code');

            if(!in_array($serviceCode, $accessCodeList)){
                throw new PermissionException('您没有权限查看该视频', 401);
            }

            $courseName = array_get($courseSystemInfo, 'data.name');

            $authorId = array_get($videoSigninInfo, 'data.author', 0);

            $creatorId = array_get($videoSigninInfo, 'data.creator_user_id', 0);

            $authorInfo = $userManager->getUserInfo($authorId);

            $videoSigninInfo['data']['course_name'] =  $courseName;

            $videoSigninInfo['data']['course_code'] =  $courseCode;

            $videoSigninInfo['data']['author_name'] = array_get($authorInfo, 'userInfo.name');

            $videoSigninInfo['data']['customer_name'] =  $customerName;

            $videoSigninInfo['data']['course_description'] = array_get($courseSystemInfo, 'data.full_text_description');

            $videoSigninInfoUrl = array_get($videoSigninInfo, 'data.url');

            if(strpos($videoSigninInfoUrl, self::TENCENT_URL) !== false){//是腾讯视频
                if(strpos($videoSigninInfoUrl, self::IFRAME) !== false){
                    $position  = strpos($videoSigninInfoUrl, '=');

                    $subUrl = substr($videoSigninInfoUrl, $position + 1 );

                    $position  = strpos($subUrl, '&');

                    $videoId = substr($subUrl, 0, $position);
                }else{
                    $position  = strrpos($videoSigninInfoUrl, '/');

                    $subUrl = substr($videoSigninInfoUrl, $position + 1 );

                    $position  = strrpos($subUrl, '.');

                    $videoId = substr($subUrl, 0, $position);
                }

                $videoSigninInfo['data']['video_type'] = 0; //0:是腾讯视频

                $thumbnailPreviewPath = array_get($courseVideoInfo, 'data.course_video_info.thumbnail_preview_path');

                $videoSigninInfo['data']['poster_url'] = $thumbnailPreviewPath;

                $videoSigninInfo['data']['video_url'] = '';
            }elseif(strpos($videoSigninInfoUrl, self::GENSEE)){
                $position  = strrpos($videoSigninInfoUrl, '-');

                $videoId = substr($videoSigninInfoUrl, $position + 1);

                $videoSigninInfo['data']['video_type'] = 1;//1:展示互动视频

                $videoSigninInfo['data']['poster_url'] = '';

                $videoSigninInfo['data']['video_url'] = $videoSigninInfoUrl;
            } elseif (strpos($videoSigninInfoUrl, self::VHALL)) {
                $videoUrlArr = explode('/', $videoSigninInfoUrl);
                if (strpos($videoUrlArr[count($videoUrlArr) - 1], '?') !== false) {
                    $videoId = explode('?', $videoUrlArr[count($videoUrlArr) - 1])[0];
                } else {
                    $videoId = $videoUrlArr[count($videoUrlArr) - 1];
                }
                
                $videoSigninInfo['data']['video_type'] = 2; // 2: 微吼视频
                
                $videoSigninInfo['data']['poster_url'] = '';

                $videoSigninInfo['data']['video_url'] = $videoSigninInfoUrl;
            } else{
                $videoId = '';

                $videoSigninInfo['data']['video_type'] = 3;//其他视频
                $videoSigninInfo['data']['poster_url'] = '';
            }

            $videoSigninInfo['data']['open_id'] = $currentOpenId;//客户openID

            $videoSigninInfo['data']['nickname'] = $customerName;//客户nickname

            $videoSigninInfo['data']['url_change'] = $videoSigninInfoUrl;

            $videoSigninInfo['data']['video_id'] = $videoId;

            $videoSigninInfo['data']['session_id'] = empty($sessionId) ? '' : $sessionId;

            $videoSigninInfo['data']['is_forward_teacher'] = $isTeacher;

            $videoSigninInfo['data']['type'] = 'course';

            $videoSigninInfo['data']['forward_teacher_id'] = empty($isTeacher) ? 0 : $teacherUserId;

            $videoSigninInfo['data']['is_reply'] = isset($userMobile) && !empty($userMobile) ? 1 : 0;

            $videoSigninInfo['data']['forward_open_id'] = empty($isTeacher) ? 0 : $currentOpenId;

            $videoSigninInfo['data']['is_like'] = array_get($isLike, 'data.like');

            $videoSigninInfo['data']['like_sum'] = empty(array_get($likeSum, 'data.statisticInfo.like_sum')) ? 0 : array_get($likeSum, 'data.statisticInfo.like_sum') > 999 ? '999+' : array_get($likeSum, 'data.statisticInfo.like_sum');

            //$replyList = $this->interactionContract->getReplyList(self::COURSE_TYPE, $videoSigninId, ArticleReply::STATUS_APPROVE, $currentOpenId, $lastReplyId, $pageSize);

            //if (!empty($replyList->toArray())) {
            //    $customerOpenIdList = $replyList->pluck('open_id');
            //    $customerList = $this->customer->getCustomerList($customerOpenIdList->toArray());
            //    $customerMap = array_column($customerList, NULL, 'open_id');

            //    $supermanUserList = $this->user->getUserListByGroupCode(self::USER_GROUP_CODE_SUPERMAN_TAG);
            //    $supermanUserIdList = array_column($supermanUserList, 'id');
            //    $supermanUcList = $this->user->getUcListByUserIdList($supermanUserIdList);
            //    $supermanQyUseridList = array_column($supermanUcList, 'enterprise_userid');

            //    foreach ($replyList as &$reply) {
            //        $customer = array_get($customerMap, $reply['open_id']);
            //        if (!empty($customer)) {
            //            $reply['nickname'] = $customer['name'];
            //            if (!empty($currentOpenId) && $currentOpenId == $customer['open_id']) {
            //                $reply['nickname'] .= '(我)';
            //            }
            //            $reply['icon_url'] = $customer['icon_url'];

            //            if (!empty($supermanQyUseridList) && in_array($customer['qy_userid'], $supermanQyUseridList)) {
            //                $reply['is_teacher'] = 1;
            //            } else {
            //                $reply['is_teacher'] = 0;
            //            }
            //        }

            //        if (!empty($reply['ref_open_id'])) {
            //            $refCustomer = array_get($customerMap, $reply['ref_open_id']);
            //            $reply['ref_nickname'] = $refCustomer['name'];
            //            if (!empty($currentOpenId) && $currentOpenId == $refCustomer['open_id']) {
            //                $reply['ref_nickname'] .= '(我)';
            //            }
            //            $reply['ref_icon_url'] = $refCustomer['icon_url'];

            //            if (!empty($supermanQyUseridList) && in_array($refCustomer['qy_userid'], $supermanQyUseridList)) {
            //                $reply['ref_is_teacher'] = 1;
            //            } else {
            //                $reply['ref_is_teacher'] = 0;
            //            }
            //        }

            //        unset($reply['session_id']);
            //    }
//          //      $replyCnt = $this->interaction->getReplyCnt($contentType, $contentId);
            //} else {
//          //      $replyCnt = 0;
            //}

            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'course' => array_get($videoSigninInfo, 'data'),
                    //'reply_list' => $replyList,
                ],
            ];
        } catch (MatrixException $e) {
            Log::error($e->getMessage(), [$e]);

            $ret = [
                'code' => $e->getCode(),
                'msg' => $e->getMessage(),
            ];

            if ($ret['code'] == CMS_API_X_SESSIONID_NOT_FOUND) {
                $ret['callback_url'] = $loginUrl;
            }

            return view('errors.403', $ret);
        } catch (Exception $e){
            Log::error($e->getMessage(), [$e]);

            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'callback' => $loginUrl,
                'msg' => $e->getMessage(),
            ];
        }

        return view('course.play_video', $ret);
    }
}
