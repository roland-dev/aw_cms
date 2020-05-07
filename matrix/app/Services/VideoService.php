<?php

namespace Matrix\Services;

use Illuminate\Support\Facades\Auth;

use Matrix\Contracts\VideoManager;

use Matrix\Models\VideoSignin;
use Matrix\Models\Log;
use Matrix\Models\Category;
use Matrix\Models\User;
use Matrix\Models\CategoryGroup;
use Matrix\Models\Teacher;
use Matrix\Models\TjWxSendLogDetail;
use Matrix\Exceptions\TalkshowException;
use Matrix\Exceptions\MatrixException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class VideoService extends BaseService implements VideoManager
{
    private $videoSignin;
    private $log;
    private $category;
    private $user;
    private $categoryGroup;
    private $teacher;

    public function __construct(VideoSignin $videoSignin, Log $log, Category $category, User $user, CategoryGroup $categoryGroup, Teacher $teacher)
    {
        $this->videoSignin = $videoSignin;
        $this->log = $log;
        $this->category = $category;
        $this->user = $user;
        $this->categoryGroup = $categoryGroup;
        $this->teacher = $teacher;
    }

    public function getVideoListByIdListAndUserId(array $videoIdList, int $userId)
    {
        $videoList = $this->videoSignin->getVideoListByIdListAndUserId($videoIdList, $userId);

        return $videoList;
    }

    public function show(array $categoryCodeList)
    {
       $showData = $this->videoSignin->select($categoryCodeList);
       if(empty($showData)){
           return ['code' => SYS_STATUS_OK];
       }

       $categoryList = $this->category->getCategoryByCode($categoryCodeList);//区别登记视频与课程视频
       $categoryList = array_column($categoryList, NULL, 'code');
       $categoryCodeList = array_column($categoryList, 'code');

       $userList = $this->user->getAllUserList();
       $userList = array_column($userList, NULL, 'id');
       $userIdList = array_column($userList, 'id');

       $videoList = [];
       foreach ($showData as $show) {
           if (in_array($show['category_code'], $categoryCodeList)) {
               $show['category_code'] = $show['category_code'];
               $show['category'] = $categoryList[$show['category_code']]['name'];
           }
           if (in_array($show['author'], $userIdList)) {
               $show['author_user_id'] = $show['author'];
               $show['author'] = $userList[$show['author_user_id']]['name'];
           }
          
           if (in_array($show['creator_user_id'], $userIdList)) {
              $show['creator'] = $userList[$show['creator_user_id']]['name'];
           }

           $videoList[] = $show;
       }

       $ret = [
           'code' => SYS_STATUS_OK,
           'videoSigninList' => $videoList,
       ];
       
       return $ret;
    }
    
    public function getVideoSigninList(int $pageNo, int $pageSize, array $categoryCodeList)
    {
        $videoList = VideoSignin::where('active', 1)
            ->whereIn('category_code', $categoryCodeList)
            ->orderBy('updated_at', 'desc')
            ->skip($pageSize * ($pageNo - 1))
            ->take($pageSize)
            ->get()
            ->toArray();
        
        $categoryList = $this->category->getCategoryByCode($categoryCodeList);
        $categoryList = array_column($categoryList, NULL, 'code');
        $categoryCodeList = array_column($categoryList, 'code');

        $userList = $this->user->getAllUserList();
        $userList = array_column($userList, NULL, 'id');
        $userIdList = array_column($userList, 'id');

        foreach ($videoList as &$video) {
            if (in_array(array_get($video, 'category_code'), $categoryCodeList)) {
                $video['category'] = $categoryList[$video['category_code']]['name'];
            }

            if (in_array(array_get($video, 'author'), $userIdList)) {
                $video['author_user_id'] = $video['author'];
                $video['author'] = $userList[$video['author_user_id']]['name'];
            }

            if (in_array(array_get($video, 'creator_user_id'), $userIdList)) {
                $video['creator'] = $userList[$video['creator_user_id']]['name'];
            }
        }
        
        return $videoList;
    }

    public function getVideoSigninCnt(array $categoryCodeList)
    {
        $videoSigninCnt = VideoSignin::where('active', 1)->whereIn('category_code', $categoryCodeList)->count();
        return $videoSigninCnt;
    }

    public function create(string $url, int $author, string $categoryCode, string $title, string $publishedAt, $description, int $isPublicPlayer)
    {
        $videoSignin = $this->videoSignin->insert(Auth::id(), $url, $categoryCode, $author, $title, $publishedAt,  $description, $isPublicPlayer);
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'videoSignin' => $videoSignin,
            ],
        ];
        
        return $ret;
    }

    public function update(int $userId, int $videoId, string $url, int $author, string $categoryCode, string $title, string $publishedAt,  $description)
    {
        $this->videoSignin->updateRecord($userId, $videoId, $url, $categoryCode, $author, $title, $publishedAt, $description);
        $ret = [
            'code'=>SYS_STATUS_OK
        ];
        
        return $ret;
    }

    public function destory(int $videoId)
    {
        $this->videoSignin->destory($videoId); 
        $ret = [
            'code' => SYS_STATUS_OK
        ];

        return $ret;
    }

    public function getOneSigninDetail(int $videoId)
    {
        $oneRecordDetail =  $this->videoSignin->getOneDetail($videoId); 
        $userId = array_get($oneRecordDetail, 'author');
        $categoryCode = array_get($oneRecordDetail, 'category_code');//category字段弃用,该用category_code
        $userInfo = $this->user->getUserInfo($userId);
        $categoryInfo = $this->category->getOneCategoryInfo($categoryCode);
        $authorName = array_get($userInfo, 'name');
        $categoryName = array_get($categoryInfo, 'name');
        if(!empty($oneRecordDetail)){
			$oneRecordDetail['author_name'] = $authorName;
            $oneRecordDetail['category_name'] = $categoryName;
            $oneRecordDetail['category_code'] = $categoryCode;
            $oneRecordDetail['author_id'] =  (int)array_get($oneRecordDetail, 'author');
            $videoKey = array_get($oneRecordDetail, 'video_key');
            $oneRecordDetail['video_url'] = config('video.video.url').$videoKey;
        }

        $ret = [
            'code' => SYS_STATUS_OK,
            'oneRecordDetail' => $oneRecordDetail
        ];
       
        return $ret;
    }

    public function getCategoriesList()
    {
        $categoriesList = $this->category->getCategories();
        $ret = [
            'code' => SYS_STATUS_OK,
            'categories' => $categoriesList,
        ];

        return $ret;
    }

    public function getCategoryListByCategoryGroupCode(string $videoSigninGroupCode)
    {
        $categoryCodeList = $this->categoryGroup->getCategoryGroupListByCode($videoSigninGroupCode);
        $categoryCodeList = array_column($categoryCodeList, 'category_code');
        $categoriesList = $this->category->getCategoryByCode($categoryCodeList);
        $ret = [
            'code' => SYS_STATUS_OK,
            'categories' => $categoriesList,
        ];

        return $ret;
    }

    public function getTeachersList(string $categoryCode)
    {
        $teachersList = $this->teacher->getTeacherListByCategoryCode($categoryCode);
        return $teachersList;
    }

    public function getRecordsBeforeModify(array $condition)
    {
        $repData = $this->videoSignin->show($condition);  
        $ret = [
            'code' => SYS_STATUS_OK,
            'repData' => ['video_signin' => $repData]
        ]; 
        
        return $ret;
    }

    public function searchVideoSignin($categoryCode, $author, $title, $sTime, $eTime, $categoryCodeList)
    {
        $showData = $this->videoSignin->search($categoryCode, $author, $title, $sTime, $eTime, $categoryCodeList);
       $categoryList = $this->category->getCategoryByCode($categoryCodeList);
       $categoryList = array_column($categoryList, NULL, 'code');
       $categoryCodeList = array_column($categoryList, 'code');

       $userList = $this->user->getAllUserList();
       $userList = array_column($userList, NULL, 'id');
       $userIdList = array_column($userList, 'id');

       $videoList = [];
       foreach ($showData as $show) {
           if (in_array($show['category_code'], $categoryCodeList)) {
               $show['category_code'] = $show['category_code'];
               $show['category'] = $categoryList[$show['category_code']]['name'];
           }
           if (in_array($show['author'], $userIdList)) {
               $show['author_user_id'] = $show['author'];
               $show['author'] = $userList[$show['author_user_id']]['name'];
           }

           if (in_array($show['creator_user_id'], $userIdList)) {
               $show['creator'] = $userList[$show['creator_user_id']]['name'];
           }

           $videoList[] = $show;
       }

       $ret = [
            'code' => SYS_STATUS_OK,
            'videoList' => $videoList,
        ];
   
        return $ret;
    }

    public function searchVideoSigninList(int $pageNo, int $pageSize, array $categoryCodeList, array $credentials)
    {
        $cond = [];

        foreach ($credentials as $k => $v) {
            if (in_array($k, ['category_code', 'author']) && $v !== "" && $v !== null) {
                $cond[] = [$k, $v];
            }
        }

        $title = array_get($credentials, 'title');
        if (!empty($title)) {
            $cond[] = ['title', 'like', "%$title%"];
        }

        $sTime = array_get($credentials, 's_time');
        if (!empty($sTime)) {
            $sTime = date('Ymd', strtotime($sTime));
            $cond[] = ['published_at', '>=', $sTime];
        }

        $eTime = array_get($credentials, 'e_time');
        if (!empty($eTime)) {
            $eTime = array_get($credentials, 'e_time');
            $cond[] = ['published_at', '<=', $eTime];
        }

        $cond[] = ['active', '=', 1];

        $videoSigninList = VideoSignin::where($cond)
            ->whereIn('category_code', $categoryCodeList)
            ->orderBy('updated_at', 'desc')
            ->skip($pageSize * ($pageNo - 1))
            ->take($pageSize)
            ->get()
            ->toArray();

        $categoryList = $this->category->getCategoryByCode($categoryCodeList);
        $categoryList = array_column($categoryList, NULL, 'code');
        $categoryCodeList = array_column($categoryList, 'code');

        $userList = $this->user->getAllUserList();
        $userList = array_column($userList, NULL, 'id');
        $userIdList = array_column($userList, 'id');

        foreach ($videoSigninList as &$videoSignin) {
            if (in_array(array_get($videoSignin, 'category_code'), $categoryCodeList)) {
                $videoSignin['category'] = $categoryList[$videoSignin['category_code']]['name'];
            }

            if (in_array(array_get($videoSignin, 'author'), $userIdList)) {
                $videoSignin['author_user_id'] = $videoSignin['author'];
                $videoSignin['author'] = $userList[$videoSignin['author_user_id']]['name'];
            }

            if (in_array(array_get($videoSignin, 'creator_user_id'), $userIdList)) {
                $videoSignin['creator'] = $userList[$videoSignin['creator_user_id']]['name'];
            }
        }

        return $videoSigninList;
    }

    public function searchVideoSigninCnt(array $categoryCodeList, array $credentials)
    {
        $cond = [];

        foreach ($credentials as $k => $v) {
            if (in_array($k, ['category_code', 'author']) && $v !== "" && $v !== null) {
                $cond[] = [$k, $v];
            }
        }

        $title = array_get($credentials, 'title');
        if (!empty($title)) {
            $cond[] = ['title', 'like', "%$title%"];
        }

        $sTime = array_get($credentials, 's_time');
        if (!empty($sTime)) {
            $sTime = date('Ymd', strtotime($sTime));
            $cond[] = ['published_at', '>=', $sTime];
        }

        $eTime = array_get($credentials, 'e_time');
        if (!empty($eTimer)) {
            $eTime = date('Ymd', strtotime($eTime));
            $cond[] = ['published_at', '<=', $eTime];
        }

        $cond[] = ['active', '=', 1];

        $videoSigninCnt = VideoSignin::where($cond)
            ->whereIn('category_code', $categoryCodeList)
            ->count();
        
        return $videoSigninCnt;
    }

    public function findVideoByUrl(string $url)
    {
        if(strpos($url, config('video.video.url')) > 0 ){
            $pos = strrpos($url, '/' );
            $videoKey = substr($url, $pos + 1); 
            $video = $this->videoSignin->findByVideoKey($videoKey);
        }else{
            $video = $this->videoSignin->findByUrl($url);
        } 

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'video' => $video,
            ],
        ];
        return $ret;
    }

    public function removeVideoSigninById(array $videoSigninIdList)
    {
        $removeRes = $this->videoSignin->removeVideoSigninById($videoSigninIdList);
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'remove_res' => $removeRes,
            ],
        ];
        return $ret;
    }

    public function getVideoSigninInfo(string $videoKey)
    {
        $videoInfo = $this->videoSignin->findByVideoKey($videoKey);
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => $videoInfo, 
        ];
        return $ret;
    }

    public function getHistoryData(int $detailId)
    {
        try {
            $logDetail = TjWxSendLogDetail::where('detail_id', $detailId)->where('msg_type', 'news')
                ->take(1)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new MatrixException('历史内容没有找到', VIDEO_URL_NOT_EXISTS);
        }

        return $logDetail;
    }

    //通过视频id获取视频列表
    public function getVideoListByVideoIds(array $videoIds)
    {
        $videoList = $this->videoSignin->show($videoIds);

        $userList = $this->user->getAllUserList();
        $userList = array_column($userList, NULL, 'id');
        $userIdList = array_column($userList, 'id');

        foreach ($videoList as &$video) {
            if (in_array($video['creator_user_id'], $userIdList)) {
               $video['creator'] = $userList[$video['creator_user_id']]['name'];
            }
        }

        $ret = [
            'code' => SYS_STATUS_OK,
            'videoSigninList' => $videoList,
        ];

        return $ret;
    }

    // 获取 大盘分析 下 节目视频列表
    public function getDapanfenxiVideoList(array $categoryCodeList, int $day = 0)
    {
        $model = TjWxSendLogDetail::whereIn('category', $categoryCodeList)->where('show_in_app', 1);

        if (!empty($day)) {
            $dayTime = strtotime(date("Y-m-d 00:00:00", strtotime("-$day days")));
            $model = $model->where('send_time', '>', $dayTime);
        }

        $dapanfenxiVideoList = $model->orderBy('send_time', 'desc')->get()->toArray();

        return $dapanfenxiVideoList;
    }
}
