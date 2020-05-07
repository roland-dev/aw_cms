<?php

namespace Matrix\Services;

use Illuminate\Support\Facades\Auth;
use Matrix\Contracts\CourseVideoManager;
use Matrix\Contracts\VideoManager;
use Matrix\Models\CourseVideo;
use Matrix\Models\VideoSignin;
use Matrix\Models\User;
use Matrix\Models\UserActionHistory;
use Matrix\Models\Course;
use Matrix\Models\CourseSystem;
use Matrix\Models\ContentGuard;

class CourseVideoService extends BaseService implements CourseVideoManager
{
    const BASE_URI = '/api/v2/';
    const COURSE_VIDEO_URI = 'client/course/detail/';
    const URI = "/api/v2/coursesystem/{courseSystemCode}/course/{courseCode}";
    const COURSE_CODE = NULL;

    private $courseVideo;
    private $course;
    private $courseSystem;
    private $user;
    private $videoSignin;
    private $userActionHistory;
    private $contentGuard;

    public function __construct(CourseVideo $courseVideo, User $user, VideoSignin $videoSignin, UserActionHistory $userActionHistory, Course $course, CourseSystem $courseSystem, ContentGuard $contentGuard)
    {
        $this->courseVideo = $courseVideo;
        $this->user = $user;
        $this->videoSignin = $videoSignin;
        $this->userActionHistory = $userActionHistory;
        $this->course = $course;
        $this->courseSystem = $courseSystem;
        $this->contentGuard = $contentGuard;
    }

    public function create(string $thumbnailPreviewPath, int $isDisplay, int $videoSigninId, array $pvUv, string $courseCode, int $sortNo, $tag, $demoUrl, $adGuide)
    {
        //$insertResp = $this->courseVideo->insert($imagePath, $thumbnailPath, $thumbnailPreviewPath, $isDisplay, $videoSigninId, $pvUv, $courseCode, $sortNo, $tag);
        $insertResp = $this->courseVideo->insert($thumbnailPreviewPath, $isDisplay, $videoSigninId, $pvUv, $courseCode, $sortNo, $tag, $demoUrl, $adGuide);
        $ret['code'] = empty($insertResp) ? SYS_STATUS_SERVICE_ERROR : SYS_STATUS_OK;
        $ret['data']['create_data'] = $insertResp;
        return $ret;
    }

    public function update(int $courseVideoId, string $thumbnailPreviewPath, int $isDisplay, int $videoSigninId, array $pvUv, $tag, $demoUrl, $adGuide)
    {
        //$updateResp = $this->courseVideo->updateRecord($courseVideoId, $imagePath, $thumbnailPath, $thumbnailPreviewPath, $isDisplay, $videoSigninId, $pvUv, $tag);
        $updateResp = $this->courseVideo->updateRecord($courseVideoId, $thumbnailPreviewPath, $isDisplay, $videoSigninId, $pvUv, $tag, $demoUrl, $adGuide);
        $ret['code'] = empty($updateResp) ? SYS_STATUS_SERVICE_ERROR : SYS_STATUS_OK;
        $ret['data']['update_data'] = $updateResp;
        return $ret;
    }

    public function getPvUvCount(string $videoKey)
    {
        $List = $this->userActionHistory->getPvList($videoKey);
        $getVideoList = array_get($List, 'get_video');
        $playVideoList = array_get($List, 'play_video');
        $finishVideoList = array_get($List, 'finish_video');
        $getVideoListCollection = collect($getVideoList);
        $playVideoListCollection = collect($playVideoList);
        $finishVideoListCollection = collect($finishVideoList);
        $getPvCount = $getVideoListCollection->count();
        $getUvCount = $getVideoListCollection->unique('actor')->count();
        $playPvCount = $playVideoListCollection->count();
        $playUvCount = $playVideoListCollection->unique('actor')->count();
        $finishPvCount = $finishVideoListCollection->count();
        $finishUvCount = $finishVideoListCollection->unique('actor')->count();
 
        $ret = [
            'get_pvuv' => $getPvCount.'/'.$getUvCount,
            'play_pvuv' => $playPvCount.'/'.$playUvCount,
            'finish_pvuv' => $finishPvCount.'/'.$finishUvCount,
        ];

        return $ret;
    }

    public function destory(int $courseVideoId)
    {
        $courseVideoDel = $this->courseVideo->destory($courseVideoId);
        $ret['code'] = empty($courseVideoDel) ? SYS_STATUS_SERVICE_ERROR : SYS_STATUS_OK;
        return $ret;
    }

    //public function getCourseVideoInfo(int $videoSigninId)
    public function getCourseVideoInfo(int $courseVideoId, int $videoSigninId)
    {
        if(!empty($courseVideoId)){
            $courseVideoInfo = $this->courseVideo->getCourseVideoInfo($courseVideoId);
        }else{
            $courseVideoInfo = $this->courseVideo->getCourseVideoInfoVideoSigninId($videoSigninId);
        }
        $picturePath = array_get($courseVideoInfo, 'picture_path');
        //$path = json_decode($picturePath, TRUE);
        //$courseVideoInfo['image_path'] = $path[0];
        //$courseVideoInfo['thumbnail_path'] = $path[1];
        //$courseVideoInfo['thumbnail_preview_path'] = $this->filterUrl($path[2]);
        $courseVideoInfo['thumbnail_preview_path'] = $this->filterUrl($picturePath);

        //$thumbnail_path = $path[2] ;
        $num = 1;
        for($i = 0 ; $i < $num ; $i ++){
            //$pos = strrpos($thumbnail_path, '/'); 
            //if($i !== $num -1 ){
            //    $thumbnail_path = substr($thumbnail_path, 0, $pos);
            //}       
            $pos = strrpos($picturePath, '/');
            if($i !== $num -1 ){
                $picturePath = substr($picturePath, 0, $pos);
            }
        }
        $thumbnail_name = substr($picturePath, $pos + 1);
        $courseVideoInfo['image_name'] = $thumbnail_name;
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'course_video_info' => $courseVideoInfo,
            ],
        ];
        return $ret;
    }

    public function getCourseVideoList(string $courseCode, array $videoList)
    {
        if(empty($videoList)){
            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'course_video_list' => [],
                ],
            ];
            return $ret;
        }

        $List = [];
        $courseVideoList = $this->courseVideo->getCourseVideoList($courseCode);
        $videoSigninList = array_column($videoList, NULL, 'id');
        $courseVideoList = array_column($courseVideoList, NULL, 'video_signin_id'); 
        foreach($courseVideoList as $courseVideo){
            $courseVideo['creator_name'] = $videoSigninList[$courseVideo['video_signin_id']]['creator'];
            $courseVideo['title'] = $videoSigninList[$courseVideo['video_signin_id']]['title'];
            empty($courseVideo['is_display']) ? $courseVideo['display'] = '否' : $courseVideo['display'] = '是';
            $List[] = $courseVideo;
        }
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'course_video_list' => $List,
            ], 
        ];
        return $ret;
    }

    public function getRecordsBeforeModify(array $condition)
    {
        $repData = $this->courseVideo->show($condition); 
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'course_video' => $repData,
            ]  
        ];
        return $ret;
    }

    public function removeCourseVideoByCode(array $courseCodeList)
    {
        $deleteRes = $this->courseVideo->removeCourseVideoByCode($courseCodeList);
        return $deleteRes;
    }


    public function removeCourseVideoBySingleCode(string $courseCode)
    {
        $deleteRes = $this->courseVideo->removeCourseVideoBySingleCode($courseCode);
        return $deleteRes;
    }


    public function updateRecordByCode(string $newCode, string $oldCode)
    {
        $updateCourseVideo = $this->courseVideo->updateRecordByCode($newCode, $oldCode);
        return $updateCourseVideo;
    }

    public function getVideoSigninIdList(array $courseCodeList)
    {
        $list = [];
        $videoSigninList = $this->courseVideo->getVideoSigninIdList($courseCodeList);
        foreach($videoSigninList as $videoSignin){
            $list[] = $videoSignin['video_signin_id'];
        }
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => $list,
        ];
        return $ret;
    }

    public function getServiceCodeByParam($courseSystemCode, $courseCode, $contentGuardList)
    {
        $ret = '';
        foreach($contentGuardList as $contentGuard){
            if($contentGuard['param1'] === $courseSystemCode && $contentGuard['param2'] === $courseCode){
                return $contentGuard['service_code'];
            }
        }
        return $ret;
    }

    public function apiGetCourseVideoList(array $videoSigninList)
    {
        $list = [];
        $codeTree = [];
        $courseVideoList = $this->courseVideo->getList();

        $videoSigninIdList = array_column($courseVideoList, 'video_signin_id');
        $videoSigninList = VideoSignin::whereIn('id', $videoSigninIdList)->where('active', 1)->get()->toArray();

        //$courseVideoCollection = collect($courseVideoList);
        //$courseVideoList = $courseVideoCollection->sortByDesc('sort_no')->values()->all();
        $courseList = $this->course->show(self::COURSE_CODE);
        $contentGuardList = $this->contentGuard->getContentGuardList(self::URI);
        //$courseSystemList = $this->courseSystem->show();
        $videoSigninList = array_column($videoSigninList, NULL, 'id');
        //$courseVideoList = array_column($courseVideoList, NULL, 'video_signin_id');
        $courseList = array_column($courseList, NULL, 'code');
        //$courseSystemList = array_column($courseSystemList, NULL, 'code');
        foreach($courseVideoList as $index => $courseVideo){
            //$list[$index]['id'] = $courseVideo['video_signin_id'];
            $list[$index]['detail_id'] = $videoSigninList[$courseVideo['video_signin_id']]['video_key'];
            $list[$index]['column_name'] = '学战法';
            //$list[$index]['category_key'] = $courseList[$courseVideo['course_code']]['course_system_code'];
            //$list[$index]['category_name'] = $courseSystemList[$courseList[$courseVideo['course_code']]['course_system_code']]['name'];
            //$list[$index]['category_key'] = $courseVideo['course_code'];//modifyed
            $list[$index]['category_key'] = $courseList[$courseVideo['course_code']]['course_system_code'];//modifyed
            $list[$index]['group_id'] = $courseList[$courseVideo['course_code']]['id'];//add
            $list[$index]['category_name'] = $courseList[$courseVideo['course_code']]['name'];
            $list[$index]['title'] = $videoSigninList[$courseVideo['video_signin_id']]['title'];
            $list[$index]['summary'] = $videoSigninList[$courseVideo['video_signin_id']]['description'];
            $list[$index]['media_type'] = 'news';
            //$list[$index]['source_url'] = $videoSigninList[$courseVideo['video_signin_id']]['url'];
            $accessLevel = $this->getServiceCodeByParam($courseList[$courseVideo['course_code']]['course_system_code'], $courseVideo['course_code'], $contentGuardList);

            // $getParam = [
            //     'app' => 'basic',
            //     'vid' => $videoSigninList[$courseVideo['video_signin_id']]['video_key'],
            // ];

            $video['source_url'] = sprintf("%s%s%s%s", config('video.video.h5_url'), self::BASE_URI, self::COURSE_VIDEO_URI, $videoSigninList[$courseVideo['video_signin_id']]['video_key']);

            //$path = json_decode($courseVideo['picture_path'], TRUE);
            $list[$index]['thumb_cdn_url'] = $this->filterUrl($courseVideo['picture_path']);
            //$list[$index]['thumb_local_url'] = $path[1];
            $list[$index]['thumb_local_url'] = $courseVideo['picture_path'];
            //$list[$index]['access_level'] = '';
            $list[$index]['add_time'] = $courseVideo['created_at'];
            $list[$index]['access_level'] = $accessLevel;
            //$contentGuardInfo = $this->contentGuard->getOneInfo(self::URI, $courseList[$courseVideo['course_code']]['course_system_code'], $courseVideo['course_code']);
            //$list[$index]['access_level'] = $contentGuardInfo['service_code'];
            $list[$index]['file_size'] = '';
            $list[$index]['tag'] = $courseVideo['tag'];
            $list[$index]['course_code'] = $courseVideo['course_code'];
            //$list[$index]['is_public_player'] = $videoSigninList[$courseVideo['video_signin_id']]['is_public_player'];//去掉is_public_player 判断是否是公共播放器播放
        }
        foreach ($list as $index => $courseVideo) {
            //if(!array_key_exists($courseVideo['category_key'], $codeTree)){
            //    $codeTree[$courseVideo['category_key']] = [];
            //}
            //$codeTree[$courseVideo['category_key']][] = $courseVideo;
            if(!array_key_exists($courseVideo['group_id'], $codeTree)){//将有同一group_id的视频(在同一栏目下的视频)，放在同一栏目下
                $codeTree[$courseVideo['group_id']] = [];
            }
            $codeTree[$courseVideo['group_id']][] = $courseVideo;
        }

        return $codeTree;
    }

    public function updateOrder(int $sequence, int $courseVideoId)
    {
        $updateResp = $this->courseVideo->updateOrder($sequence, $courseVideoId);
        $ret = [
            'code' => $updateResp,
        ];
        return $ret;
    }

    public function getCourseVideoInfoByVideoSigninId(int $videoSignId)
    {
        $courseVideoInfo = $this->courseVideo->getCourseVideoInfoByVideoSignId($videoSignId);
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => $courseVideoInfo,
        ];
        return $ret;
    }

    public function filterUrl($url)
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

        //if(strpos($url, '/files/cms') !== false){
        //    $url = str_replace('/files/cms/storage', '', $url);
        //    //$url = 'http://res.zhongyingtougu.com'.$url;
        //    $url = config('app.url').'/storage'.$url;
        //    return $url;
        //}

        //$url = config('app.url').'/storage/'.$url;
        return $url;
    }

    public function getVideoList()
    {
        $videoList = $this->courseVideo->getList();

        return $videoList;
    }

    //通过课程code获取课程视频列表
    public function getVideoListByCode(string $courseCode)
    {
        $courseVideoList = $this->courseVideo->getCourseVideoList($courseCode);
        return $courseVideoList;
    }

    public function getCourseVideoListOfPaging(int $pageNo, int $pageSize, string $courseCode)
    {
        $courseVideoList = CourseVideo::where('active', 1)
            ->where('course_code', $courseCode)
            ->orderBy('updated_at', 'desc')
            ->skip($pageSize * ($pageNo - 1))
            ->take($pageSize)
            ->get()
            ->toArray();

        $videoSigninIdList = array_column($courseVideoList, 'video_signin_id');
        $videoSigninList = $this->videoSignin->show($videoSigninIdList);
        $videoSigninList = array_column($videoSigninList, NULL, 'id');

        $userIdList = array_column($videoSigninList, 'creator_user_id');
        $userList = $this->user->getUserListByUserIdList($userIdList);
        $userList = array_column($userList, NULL, 'id');
        $userIdList = array_column($userList, 'id');

        foreach ($courseVideoList as &$courseVideo) {
            if (in_array(array_get($courseVideo, 'video_signin_id'), $videoSigninIdList)) {
                if (in_array($videoSigninList[$courseVideo['video_signin_id']]['creator_user_id'], $userIdList)) {
                    $courseVideo['creator_name'] = $userList[$videoSigninList[$courseVideo['video_signin_id']]['creator_user_id']]['name'];
                }
                $courseVideo['title'] = $videoSigninList[$courseVideo['video_signin_id']]['title'];
            }

            if (empty($courseVideo['is_display'])) {
                $courseVideo['display'] = '否';
            } else {
                $courseVideo['display'] = '是';
            }
        }

        return $courseVideoList;
    }

    public function getCourseVideoCnt(string $courseCode)
    {
        $courseVideoCnt = CourseVideo::where('active', 1)
            ->where('course_code', $courseCode)
            ->count();

        return $courseVideoCnt;
    }
}
