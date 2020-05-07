<?php

namespace Matrix\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Matrix\Contracts\VideoManager;
use Matrix\Contracts\LogManager;
use Matrix\Contracts\ImageManager;
use Matrix\Contracts\UserManager;
use Matrix\Contracts\CategoryManager;
use Matrix\Exceptions\MatrixException;
use Exception;
use Log;


class VideoController extends Controller
{
    const   SOURCETYPE = 'video';
    const   ADD  = 'add';
    const   UPDATE = 'update';
    const   DELETE = 'delete';
    //const   VIDEO_SERVICE = 'manual';
    const   VIDEO_SIGNIN_GROUP_CODE = 'shipindengji_group';

    private $request;
    private $videoManager;
    private $logManager;
    private $imageManager;
    private $userManager;
    private $categoryManager;

    public function __construct(Request $request, VideoManager $videoManager, LogManager $logManager, ImageManager $imageManager, UserManager $userManager, CategoryManager $categoryManager){
        $this->request = $request;
        $this->videoManager = $videoManager;        
        $this->logManager = $logManager;
        $this->imageManager = $imageManager;
        $this->userManager = $userManager;
        $this->categoryManager = $categoryManager;
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

            $videoSigninGroupCode = self::VIDEO_SIGNIN_GROUP_CODE;
            $categoryList = $this->videoManager->getCategoryListByCategoryGroupCode($videoSigninGroupCode);
            $categoryCodeList = array_column(array_get($categoryList, 'categories'), 'code');
            $videoSigninList = $this->videoManager->getVideoSigninList($pageNo, $pageSize, $categoryCodeList);
            $videoSigninCnt = $this->videoManager->getVideoSigninCnt($categoryCodeList);
            return [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'video_signin_list' => $videoSigninList,
                    'video_signin_cnt' => $videoSigninCnt,
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

    public function create()
    {
        $originalData = '';
        $repData = $this->request->validate([
            'url' => 'required|url',
            'author' => 'required|integer',
            'category' => 'required|string',
            'published_at' => 'required|date', 
            'title' => 'required|string',
            'description' => 'nullable'
        ]);

        $description = array_get($repData, 'description');

        if(!empty($description)){
            $count = substr_count($description, "\n");

            if( $count > 4 ){
                return [
                    'code' => VIDEO_DESCRIPTION_EXTRA_LONG,
                    'msg' => '主要观点不得超过5行',
                ];
            }
        }

        $url = array_get($repData, 'url');

        $serviceRes = $this->videoManager->findVideoByUrl($url);
        $video = array_get($serviceRes, 'data.video', []);
        if (!empty($video)) {
            return [
                'code' => VIDEO_URL_EXISTS,
                'data' => [
                    'video' => $video
                ],
            ];
        }

        $author = array_get($repData, 'author');
        $categoryCode = array_get($repData, 'category');
        $publishedAt = array_get($repData, 'published_at');
        $title = array_get($repData, 'title');
        $isPublicPlayer = 1;

        $this->logManager->createOperationLog(self::SOURCETYPE, Auth::id(), $originalData, self::ADD);
        $createData = $this->videoManager->create($url, $author, $categoryCode, $title, $publishedAt, $description, $isPublicPlayer);
        $this->checkServiceResult($createData, 'Video');

        return [
            'code' => SYS_STATUS_OK,
            'data' => [
                'videoSignin' => $createData,
            ],
        ];
        
    }

    public function update()
    {
        $repData = $this->request->validate([
            'video_id' => 'required|integer',
            'url' => 'required|url',
            'author_id' => 'required|integer',
            'category_code' => 'required|string',
            'published_at' => 'required|date', 
            'title' => 'required|string',
            'description' => 'nullable',
        ]);

        $description = array_get($repData, 'description');

        if(!empty($description)){
            $count = substr_count($description, "\n");

            if( $count > 4 ){
                return [
                    'code' => VIDEO_DESCRIPTION_EXTRA_LONG,
                    'msg' => '主要观点不得超过5行',
                ];
            }
        }

        $url = array_get($repData, 'url');
        $videoId = array_get($repData, 'video_id');

        $serviceRes = $this->videoManager->findVideoByUrl($url);
        $video = array_get($serviceRes, 'data.video', []);
        if (!empty($video) && $videoId != $video['id']) {
            return [
                'code' => VIDEO_URL_EXISTS,
                'data' => [
                    'video' => $video
                ],
            ];
        }

        $userId = Auth::id();
        $authorId = array_get($repData, 'author_id');
        $categoryCode = array_get($repData, 'category_code');
        $publishedAt = array_get($repData, 'published_at');
        $title = array_get($repData, 'title');
       
        $condition = [$videoId];

        $repData = $this->videoManager->getRecordsBeforeModify($condition);
        $originalData = json_encode(array_get($repData, 'repData'));
        $this->logManager->createOperationLog(self::SOURCETYPE, Auth::id(), $originalData, self::UPDATE);
        
        $updataData = $this->videoManager->update($userId, $videoId, $url, $authorId, $categoryCode, $title, $publishedAt, $description);
        $this->checkServiceResult($updataData, 'Video');
   
        return [
            'code' => SYS_STATUS_OK
        ];
    }

    public function destory($videoId)
    {
        $condition = [$videoId];
        $repData = $this->videoManager->getRecordsBeforeModify($condition);
        $originalData = json_encode(array_get($repData, 'repData'));
        $this->logManager->createOperationLog(self::SOURCETYPE, Auth::id(), $originalData, self::DELETE);
        $destoryData = $this->videoManager->destory($videoId);
        $this->checkServiceResult($destoryData, 'Video');

        return [
            'code' => SYS_STATUS_OK
        ];
    }

    public function detail($videoId = 0)
    { 
        if(empty($videoId)){
            return [
                'code' => 'VIDEO_ID_NOT_FOUND'
            ];
        }
        $videoSigninData = $this->videoManager->getOneSigninDetail($videoId);
        $this->checkServiceResult($videoSigninData, 'Video');
        $videoSigninDetail = array_get($videoSigninData, 'oneRecordDetail');
        return [
             'code' => SYS_STATUS_OK,
             'oneRecordDetail' => $videoSigninDetail,
        ];
    }

    public function catsToTchs()
    {
        $reqData = $this->request->validate([
            'category_code' => 'required|string'
        ]);        
        $categoryCode = array_get($reqData, 'category_code');
        $catToTchsList = $this->videoManager->getTeachersList($categoryCode);
        $teacherIdList = array_column($catToTchsList, 'user_id');
        $teachersList = $this->userManager->getUserListByUserIdList($teacherIdList);
        $teachersList = array_get($teachersList, 'data.user_list');
        $teachersList = array_column($teachersList, NULL, 'id');

        foreach($catToTchsList as $item => $catToTch){
            $catToTchsList[$item]['name'] = $teachersList[$catToTch['user_id']]['name'];
        }

        return [
            'code' => SYS_STATUS_OK,
            'catToTchsList' => $catToTchsList, 
        ];
    }

    public function getCategoriesList()
    {
        $videoSigninGroupCode = self::VIDEO_SIGNIN_GROUP_CODE;
        $categoriesList = $this->videoManager->getCategoryListByCategoryGroupCode($videoSigninGroupCode);
        $this->checkServiceResult($categoriesList, 'Video');
        $categories = array_get($categoriesList, 'categories');

        return [
            'code' => SYS_STATUS_OK,
            'categories' => $categories 
        ];
    }

    public function getFileContents(string $path)
    {
        $isUrl = filter_var($path, FILTER_VALIDATE_URL);
        $path = $isUrl ? $path : resource_path($path);

        $fileContents = file_get_contents($path);
        return $fileContents;
    }

    public function generateQrCode()
    {
         $savePath = ''; 
         $reqData = $this->request->validate([
            'video_id' => 'required|integer'
         ]);

         $videoId = array_get($reqData, 'video_id');
         $videoInfo = $this->detail($videoId);
         $videoSigninsInfo = array_get($videoInfo, 'oneRecordDetail'); 
         $authorId = array_get($videoSigninsInfo, 'author');
         $videoId  = array_get($videoSigninsInfo, 'video_key');
         $title  = array_get($videoSigninsInfo, 'title');
         $time = array_get($videoSigninsInfo, 'published_at');
         $categoryCode = array_get($videoSigninsInfo, 'category_code');
         $description  = array_get($videoSigninsInfo, 'description');
         $url = config('video.video.url').$videoId; 

         $userData = $this->imageManager->getUserInfo($authorId);
         $userInfo = array_get($userData, 'userInfo');
         $name = array_get($userInfo, 'name');
         $iconUrl = array_get($userInfo, 'icon_url');
         $logo = $this->getFileContents($iconUrl);

         $categoryData = $this->categoryManager->getCategoryInfoByCode($categoryCode);
         $categoryName = array_get($categoryData, 'name');

         $qrCodeData = $this->imageManager->getQrCode($url, $logo, $title, $savePath);
         $qrcode = array_get($qrCodeData, 'data.qrcode');

         $qrCode = $this->imageManager->qrCodeMerge($qrcode, $categoryName, $time, $name, $title, $description);
         $ret = [
             'code' => SYS_STATUS_OK,
             'qrcode' => $qrCode
         ];
      
         return $ret; 
    }

    public function search()
    {
        $reqData = $this->request->validate([
            'page_no' => 'nullable|integer',
            'page_size' => 'nullable|integer',
            'category_code' => 'nullable|string',
            'author' => 'nullable|integer',
            'title' => 'nullable|string',
            's_time' => 'nullable|date',
            'e_time' => 'nullable|date',
        ]);
        
        try {
            $pageNo = array_get($reqData, 'page_no', 1);
            $pageSize = array_get($reqData, 'page_size', 10);

            $videoSigninGroupCode = self::VIDEO_SIGNIN_GROUP_CODE;
            $categoryList = $this->videoManager->getCategoryListByCategoryGroupCode($videoSigninGroupCode);
            $categoryCodeList = array_column(array_get($categoryList, 'categories'), 'code');
            $videoSigninList = $this->videoManager->searchVideoSigninList($pageNo, $pageSize, $categoryCodeList, $reqData);
            $videoSigninCnt = $this->videoManager->searchVideoSigninCnt($categoryCodeList, $reqData);
            $ret =  [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'video_signin_list' => $videoSigninList,
                    'video_signin_cnt' => $videoSigninCnt,
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
