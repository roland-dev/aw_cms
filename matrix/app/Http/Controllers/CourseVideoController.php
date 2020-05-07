<?php

namespace Matrix\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Matrix\Contracts\CourseVideoManager;
use Matrix\Contracts\VideoManager;
use Matrix\Contracts\ImageManager;
use Matrix\Contracts\LogManager;
use Matrix\Contracts\UserManager;
use Matrix\Contracts\CategoryManager;
use Matrix\Contracts\CourseManager;
use Matrix\Services\UcService;
use Matrix\Services\ContentGuardService;
use Matrix\Exceptions\MatrixException;
use Exception;
use Log;

class CourseVideoController extends Controller
{
    const DIR = 'image';
    const THUMBNAIL = 'thumbnail';
    const SOURCETYPE = 'courseVideo';
    const CATEGORY = 'xuezhanfa_course';
    const ADD = 'add';
    const UPDATE = 'update';
    const DELETE = 'delete';
    const XUEZHANFA_COURSE_CODE = 'xuezhanfa_course';
	const WX_USER_AGENT_TYPE = 'wechat';
	const NORMAL_USER_AGENT_TYPE = 'normal';
    const TENCENT_URL = 'v.qq.com';
    const CDN_URI = '/files/cms/storage/';
    const INIT = 0;
    const IFRAME = 'iframe/player';
    const URI = '/api/v2/coursesystem/{courseSystemCode}/course/{courseCode}';

    private $request;
    private $courseVideoManager;
    private $logManager;
    private $imageManager;
    private $videoManager;
    private $categoryManager;
    private $userManager;
    private $courseManager;

    public function __construct(Request $request, CourseVideoManager $courseVideoManager, LogManager $logManager, ImageManager $imageManager, VideoManager $videoManager, CategoryManager $categoryManager, UserManager $userManager, CourseManager $courseManager)
    {
        $this->request = $request;
        $this->courseVideoManager = $courseVideoManager;
        $this->logManager = $logManager;    
        $this->imageManager = $imageManager;
        $this->videoManager = $videoManager;
        $this->categoryManager = $categoryManager;
        $this->userManager = $userManager;
        $this->courseManager = $courseManager;
    }

    public function removeImage()
    {
        $reqData = $this->request->validate([
            'image_path' => 'string|nullable',
            'thumbnail_path' => 'string|nullable',
        ]);
        $imagePath = array_get($reqData, 'image_path');
        $thumbnailPath = array_get($reqData, 'thumbnail_path');
        $this->imageManager->fileDelete($imagePath);
        $this->imageManager->thumbnailFileDelete($thumbnailPath);
    }

    public function upload()
    { 
        $reqData = $this->request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
            'image_path' => 'string|nullable',
            'thumbnail_path' => 'string|nullable',
        ]);
        $imagePath = array_get($reqData, 'image_path');
        $thumbnailPath = array_get($reqData, 'thumbnail_path');
        $dir = self::DIR;
        $thumbnailDir = self::THUMBNAIL; 
        if($this->request->file('image')->isValid()){
            if(!empty($imagePath)){
                $this->imageManager->fileDelete($imagePath);
            }
            if(!empty($thumbnailPath)){
                $this->imageManager->thumbnailFileDelete($thumbnailPath);
            }
            $image = $this->request->file('image');
            $thumbnailRes = $this->imageManager->makeThumbnail($image, $thumbnailDir);
            $imageRes = $this->imageManager->upload($image, $dir);
            $thumbnailInfo = array_get($thumbnailRes, 'data');
            $imageInfo = array_get($imageRes, 'data');
            $imagesInfo = [
                'image' => $imageInfo,
                'thumbnail' => $thumbnailInfo,
            ];
            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => $imagesInfo,
            ];
        }else{
            $ret = [
                'code' => FILE_UPLOAD_NOT_VALIDATE,
                'data' => NULL,
            ];
        }
        
        return $ret;
    }

    public function create()
    {
        $originalData = '';
        $reqData = $this->request->validate([
            'name' => 'required|string',
            //'image_path' => 'required|string',
            //'thumbnail_path' => 'required|string',
            'url' => 'required|url',
            'is_display' => 'required|integer',
            'author_id' => 'required|integer',
            'course_code' => 'required|string',
            //'thumbnail_preview_path' => 'url|nullable',
            'thumbnail_preview_path' => 'required|string',
            'sort_no' => 'required|integer',
            'tag' => 'nullable',
            'demo_url' => 'url|nullable',
            'ad_guide' => 'nullable',
        ]);

        $name = array_get($reqData, 'name');
        $sortNo = array_get($reqData, 'sort_no');
        //$imagePath = array_get($reqData, 'image_path');
        //$thumbnailPath = array_get($reqData, 'thumbnail_path');
        $url = array_get($reqData, 'url');

        $signinUrlFormat = config('video.video.signin_format');
        if (strpos($url, $signinUrlFormat) !== false) {
            return [
                'code' => VIDEO_URL_EXISTS,
                'data' => [
                    'video' => '视频已经存在',
                ],
            ];
        }

        $isDisplay = array_get($reqData, 'is_display');
        $authorId = array_get($reqData, 'author_id');
        $courseCode = array_get($reqData, 'course_code');
        $thumbnailPreviewPath = array_get($reqData, 'thumbnail_preview_path');
        $tag = array_get($reqData, 'tag');
        //配置cdn目录图片路径 
        //$thumbnailPreviewPath = self::CDN_URI.$thumbnailPreviewPath;
        //试看视频与广告引导语
        $demoUrl = array_get($reqData, 'demo_url');
        $adGuide = array_get($reqData, 'ad_guide');

        $userId = Auth::id();
        $publishedAt = date('Ymd');
        $description = '';
        $isPublicPlayer = 0;
        $categoryCode = self::CATEGORY;
        $this->logManager->createOperationLog(self::SOURCETYPE, $userId, $originalData, self::ADD);
        $serviceRes = $this->videoManager->findVideoByUrl($url);
        $video = array_get($serviceRes, 'data.video', []); 
        $title = array_get($video, 'title');
        if(!empty($video)){
            //$this->imageManager->fileDelete($imagePath);
            //$this->imageManager->thumbnailFileDelete($thumbnailPath);
            return [
                'code' => VIDEO_URL_EXISTS,
                'data' => [
                    'video' => $video,
                ],
            ];
        }
        //$categoryInfo = $this->categoryManager->getCategoryInfoByCode(self::CATEGORY);
        //$categoryId = array_get($categoryInfo, 'id', 0);
        $resp = $this->videoManager->create($url, $authorId, $categoryCode, $name, $publishedAt, $description, $isPublicPlayer);
        $videoSigninId = array_get($resp, 'data.videoSignin.id');
        $videoKey = array_get($resp, 'data.videoSignin.video_key');
        //$videoKey = '52bfa3505ee5de32d7a86d35ebe0ffbd';
        $pvUv = $this->courseVideoManager->getPvUvCount($videoKey);
        //$createDataRes = $this->courseVideoManager->create($imagePath, $thumbnailPath, $thumbnailPath, $isDisplay, $videoSigninId, $pvUv, $courseCode, $sortNo, $tag);
        $createDataRes = $this->courseVideoManager->create($thumbnailPreviewPath, $isDisplay, $videoSigninId, $pvUv, $courseCode, $sortNo, $tag, $demoUrl, $adGuide);
        $this->checkServiceResult($createDataRes, 'CourseVideo'); 
        $createData = array_get($createDataRes, 'data.create_data');
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'create_data' => $createData,
            ],
        ];

        return $ret;
    }


    public function update()
    {
        $reqData = $this->request->validate([
            'name' => 'required|string',
            //'image_path' => 'required|string',
            'thumbnail_path' => 'required|string',
            //'thumbnail_preview_path' => 'required|url',
            //'thumbnail_preview_path' => 'required|string',
            'url' => 'required|url',
            'is_display' => 'required|integer',
            'author_id' => 'required|integer',
            'course_video_id' => 'required|integer',
            'video_signin_id' => 'required|integer',
            'category_code' => 'required|string',
            'tag' => 'nullable',
            'demo_url' => 'url|nullable',
            'ad_guide' => 'nullable',
        ]);

        $name = array_get($reqData, 'name');
        //$imagePath = array_get($reqData, 'image_path');
        $thumbnailPath = array_get($reqData, 'thumbnail_path');
        //if(strpos($thumbnailPath, '/files') === false){
        //    $thumbnailPath = self::CDN_URI.$thumbnailPath;
        //}
        //$thumbnailPreviewPath = array_get($reqData, 'thumbnail_preview_path');
        //
        //$thumbnailPreviewPath = self::CDN_URI.$thumbnailPreviewPath;
        $url = array_get($reqData, 'url');
        $isDisplay = array_get($reqData, 'is_display');
        $authorId = array_get($reqData, 'author_id');
        $courseVideoId = array_get($reqData, 'course_video_id');
        $videoSigninId = array_get($reqData, 'video_signin_id');
        $categoryCode = array_get($reqData, 'category_code');
        $tag = array_get($reqData, 'tag');
        //试看视频新增信息
        $demoUrl = array_get($reqData, 'demo_url');
        $adGuide = array_get($reqData, 'ad_guide');
        $userId = Auth::id();
        $publishedAt = date('Ymd');
        $description = '';

        $courseCondition = [$courseVideoId];
        $courseVideoRepData = $this->courseVideoManager->getRecordsBeforeModify($courseCondition);
        $videoSigninCondition = [$videoSigninId];
        $videoSigninRepData = $this->videoManager->getRecordsBeforeModify($videoSigninCondition);
        $jsonData[] = array_get($courseVideoRepData, 'data.course_video');
        $jsonData[] = array_get($videoSigninRepData, 'repData.video_signin');
        $originalData = json_encode($jsonData);
        $this->logManager->createOperationLog(self::SOURCETYPE, $userId, $originalData, self::ADD);
        $serviceRes = $this->videoManager->findVideoByUrl($url);
        $video = array_get($serviceRes, 'data.video', []); 
        $videoId = array_get($video, 'id');
        if(!empty($video) && $videoSigninId != $videoId ){
            //如果图片没有被上传。还是之前图片的path。那么之前的图片就会被删除。所以要去掉这些代码
            //$this->imageManager->fileDelete($imagePath);
            //$this->imageManager->thumbnailFileDelete($thumbnailPath);
            return [
                'code' => VIDEO_URL_EXISTS,
                'data' => [
                    'video' => $video,
                ],
            ];
        }
        $updateData = $this->videoManager->update($userId, $videoSigninId, $url, $authorId, $categoryCode, $name, $publishedAt, $description);
        $videoKey = array_get($video, 'video_key', 0);
        //$serviceResOfId = $this->courseVideoManager->findCourseVideoByVideoSigninId($id); 
        //$videoKey = '52bfa3505ee5de32d7a86d35ebe0ffbd';
        $pvUv = $this->courseVideoManager->getPvUvCount($videoKey);
        //$resp = $this->courseVideoManager->update($courseVideoId, $imagePath, $thumbnailPath, $thumbnailPath, $isDisplay, $videoSigninId, $pvUv, $tag);
        $resp = $this->courseVideoManager->update($courseVideoId, $thumbnailPath, $isDisplay, $videoSigninId, $pvUv, $tag, $demoUrl, $adGuide);
        $this->checkServiceResult($resp, 'CourseVideo'); 
        $courseUpdateData = array_get($resp, 'data.update_data');
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'update_data' => $courseUpdateData,
            ],
        ];
        return $ret;
    }

    public function remove($videoId, $courseVideoId)
    {
        $courseCondition = [$courseVideoId];
        $courseVideoRepData = $this->courseVideoManager->getRecordsBeforeModify($courseCondition);
        $videoSigninCondition = [$videoId];
        $videoSigninRepData = $this->videoManager->getRecordsBeforeModify($videoSigninCondition);
        $jsonData[] = array_get($courseVideoRepData, 'data.course_video');
        $jsonData[] = array_get($videoSigninRepData, 'repData.video_signin');
        $originalData = json_encode($jsonData);
        $userId = Auth::id();
        //$this->logManager->createOperationLog(self::SOURCETYPE, $userId, $originalData, self::ADD);
 
        $picturePath = array_get($courseVideoRepData, 'data.course_video');
        //$picturePath = json_decode($picturePath[0]['picture_path']);
        //$imagePath = $picturePath[0];
        //$thumbnailPath = $picturePath[1];

        $videoDel = $this->videoManager->destory($videoId);
        $courseVideoDel = $this->courseVideoManager->destory($courseVideoId); 
        //$path = $this->getCourseVideoInfo($videoId, $courseVideoId);//报404 ...
        //return $path;
        //去掉图片删除功能
        //$this->imageManager->fileDelete($imagePath);
        //$this->imageManager->thumbnailFileDelete($thumbnailPath);

        $this->checkServiceResult($courseVideoDel, 'CourseVideo'); 
        $courseVideo = array_get($courseVideoDel, 'data.delete_data');
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'delete_data' => $courseVideo,
            ]
        ];
        return $ret;
    }


    public function getCourseVideoInfo($videoId, $courseVideoId)
    {
        $videoSigninInfo = $this->videoManager->getOneSigninDetail($videoId);
        $courseVideoInfo = $this->courseVideoManager->getCourseVideoInfo($courseVideoId, self::INIT);
        $this->checkServiceResult($videoSigninInfo, 'CourseVideo');
        $this->checkServiceResult($courseVideoInfo, 'CourseVideo');
        $videoSignin = array_get($videoSigninInfo, 'oneRecordDetail');
        $courseVideo = array_get($courseVideoInfo, 'data.course_video_info');
        $info = array_merge($videoSignin, $courseVideo);
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'info' => $info,
            ],
        ]; 
        return $ret;
    }


    public function getCourseVideoList($courseCode)
    {
        $credentials = $this->request->validate([
            'page_no' => 'nullable|integer',
            'page_size' => 'nullable|integer',
        ]);

        try {
            $pageNo = array_get($credentials, 'page_no', 1);
            $pageSize = array_get($credentials, 'page_size', 10);

            $courseVideoList = $this->courseVideoManager->getCourseVideoListOfPaging($pageNo, $pageSize, $courseCode);
            $courseVideoCnt = $this->courseVideoManager->getCourseVideoCnt($courseCode);

            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'course_video_list' => $courseVideoList,
                    'course_video_cnt' => $courseVideoCnt,
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

    public function courseVideoOrder()
    {
        $reqData = $this->request->validate([
            'sequence' => 'required|integer',
            'course_video_id' => 'required|integer',
        ]);
        $sequence = array_get($reqData, 'sequence');
        $courseVideoId = array_get($reqData, 'course_video_id');
        $updateResp = $this->courseVideoManager->updateOrder($sequence, $courseVideoId);
        $code = array_get($updateResp, 'code');
        if(empty($code)) $ret = ['code' => SYS_STATUS_ERROR_UNKNOW];
        return $ret = ['code' => SYS_STATUS_OK];
    }

    public function apiGetCourseVideoDetail(UcService $ucService, ContentGuardService $contentGuardService, $videoKey)
    {
        $h5Callback = $ucService->getH5EnterpriseLoginUrl();
        $callback = array_get($h5Callback, 'data.callback');

        $sessionId = $this->request->cookie('X-SessionId');

        if(empty($sessionId)){
            $ret['code'] = CMS_API_X_SESSIONID_NOT_FOUND;
            $ret['callback_url'] = $callback;
            $ret['data'] = 'Expired X-SessionId';
            return view('course.play_video', $ret);
        }

        $currentUserInfo = $ucService->getUserInfoBySessionId($sessionId);
        $currentOpenId = (string)array_get($currentUserInfo, 'data.user.openId');
        //$accessCodeList = $ucService->getAccessCodeByOpenId($currentOpenId);
        $accessCodeList = array_get($currentUserInfo, 'data.user.accessCodes', []); 
        if (empty($accessCodeList)) {
            $accessCodeList = ['basic'];
        }

        $customerName = array_get($currentUserInfo, 'data.user.name');

        $videoSigninInfo = $this->videoManager->getVideoSigninInfo($videoKey);
        if(empty(array_get($videoSigninInfo, 'data'))){
             $ret = [
               'code' => 404,
               'data' => [],
               'msg'  => '没有找到该条视频',
            ];
            return $ret;     
        }

        $videoSigninId = array_get($videoSigninInfo, 'data.id', '');
        $courseVideoInfo = $this->courseVideoManager->getCourseVideoInfo(self::INIT, $videoSigninId);
        $courseCode = array_get($courseVideoInfo, 'data.course_video_info.course_code');
        $courseSystemInfo = $this->courseManager->getCourseInfoByCode($courseCode);
        $courseSystemCode = array_get($courseSystemInfo, 'data.course_system_code');
        $contentGuardInfo = $contentGuardService->getOneAccessCode($courseSystemCode, $courseCode, self::URI);
        $serviceCode = array_get($contentGuardInfo, 'service_code');

        if(!in_array($serviceCode, $accessCodeList)){
            $ret = [
               'code' => 401,
               'data' => [],
               'msg'  => '您没有权限查看该视频',
            ];
            return view('course.play_video', $ret);
        }

        $courseName = array_get($courseSystemInfo, 'data.name');
        $authorId = array_get($videoSigninInfo, 'data.author', 0);
        $creatorId = array_get($videoSigninInfo, 'data.creator_user_id', 0);
        $authorInfo = $this->userManager->getUserInfo($authorId);
        $videoSigninInfo['data']['course_name'] =  $courseName;
        $videoSigninInfo['data']['author_name'] = array_get($authorInfo, 'userInfo.name');
        $videoSigninInfo['data']['customer_name'] =  $customerName;
        $videoSigninInfo['data']['tag'] = array_get($courseVideoInfo, 'data.course_video_info.tag');
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
            $videoSigninInfo['data']['is_tencent'] = true; //1:是腾讯视频
            $thumbnailPreviewPath = array_get($courseVideoInfo, 'data.course_video_info.thumbnail_preview_path');
            $videoSigninInfo['data']['poster_url'] = $thumbnailPreviewPath;
            $videoSigninInfo['data']['video_url'] = '';
        }else{
            $position  = strrpos($videoSigninInfoUrl, '-');
            $videoId = substr($videoSigninInfoUrl, $position + 1);
            $videoSigninInfo['data']['is_tencent'] = false;//0:展示互动视频
            $videoSigninInfo['data']['poster_url'] = '';
            $videoSigninInfo['data']['video_url'] = $videoSigninInfoUrl;
        }
        $videoSigninInfo['data']['url_change'] = $videoSigninInfoUrl;
        $videoSigninInfo['data']['video_id'] = $videoId;

        $ret = [
            'code' => SYS_STATUS_OK,
            'callback' => $callback,
            'data' => $videoSigninInfo,
        ];
        return view('course.play_video', $ret);
    }
}
