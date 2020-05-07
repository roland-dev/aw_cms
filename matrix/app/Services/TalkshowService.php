<?php
namespace Matrix\Services;

use Matrix\Contracts\TalkshowContract;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Matrix\Exceptions\MatrixException;
use Matrix\Exceptions\VideoException;

use Matrix\Models\StaticTalkshow;
use Matrix\Models\VideoVendor;
use Matrix\Models\Talkshow;
use Matrix\Models\LiveRoom;
use Matrix\Models\Category;
use Matrix\Models\Teacher;
use Matrix\Models\Discuss;
use Matrix\Models\Customer;
use Matrix\Models\Ucenter;
use Matrix\Models\User;
use Matrix\Models\UserGroup;
use Matrix\Models\VideoSignin;
use Matrix\Models\Grant;
use Auth;
use DateInterval;
use DateTime;
use DB;
use Log;
use Matrix\Contracts\DynamicAdManager;
use Matrix\Contracts\OperateLogContract;
use Matrix\Exceptions\TalkshowException;
use Matrix\Models\DynamicAd;

class TalkshowService extends BaseService implements TalkshowContract
{
    private $dynamicAdManager;
    private $operateLogContract;

    public function __construct(
        DynamicAdManager $dynamicAdManager,
        OperateLogContract $operateLogContract
    )
    {
        $this->dynamicAdManager = $dynamicAdManager;
        $this->operateLogContract = $operateLogContract;
    }

    private function getNextTime(string $dateStr, int $second)
    {
        $date = new DateTime($dateStr);
        $interval = DateInterval::createFromDateString('+' . $second . ' seconds');


        date_add($date, $interval);

        return $date->format("Y-m-d H:i:s");
    }

    public function getVideoVendorList(int $pageNo, int $pageSize, array $credentials)
    {
        $vendorCode = array_get($credentials, 'code');
        if (!empty($vendorCode)) {
            $vendorList = VideoVendor::where('code', $vendorCode)->get();
        } else {
            $vendorName = array_get($credentials, 'name');
            if (!empty($vendorName)) {
                $vendorList = VideoVendor::where('name', 'like', "%$vendorName%")
                    ->orderBy('created_at', 'desc')->skip($pageSize * ($pageNo - 1))
                    ->take($pageSize)->get();
            } else {
                $vendorList = VideoVendor::orderBy('created_at', 'desc')
                    ->skip($pageSize * ($pageNo - 1))
                    ->take($pageSize)->get();
            }
        }

        $userIdList = $vendorList->pluck('last_modify_user_id');
        $vendorList = $vendorList->toArray();
        if (!empty($userIdList)) {
            $userList = User::all()->toArray();
            $userNameList = array_column($userList, 'name', 'id');
            foreach ($vendorList as &$vendor) {
                $vendor['last_modify_user_name'] = (string)array_get($userNameList, $vendor['last_modify_user_id']);
            }
        }

        return $vendorList;
    }

    public function createVideoVendor(array $credentials)
    {
        $vendor = VideoVendor::create($credentials);

        return $vendor;
    }

    public function updateVideoVendor(string $vendorCode, array $credentials)
    {
        try {
            // TODO 判断是否还有正在播出的节目，如果有就不允许更新
            // TODO 判断是否还有尚未播出的节目，如果有就一并更新
            // TODO 判断是否还有固定节目表的节目，如果有就一并更新
            $vendor = VideoVendor::where('code', $vendorCode)->firstOrFail();
            foreach ($credentials as $k => $v) {
                $vendor->{$k} = $v;
            }

            $vendor->save();

            return $vendor;
        } catch (ModelNotFoundException $e) {
            throw new VideoException("{$vendorCode}我没找着这个供应商.", VIDEO_VENDOR_NOT_FOUND);
        }
    }

    public function removeVideoVendor(string $vendorCode)
    {
        try {
            // TODO 判断是否还有正在播出的节目，如果有就不允许删除
            // TODO 判断是否还有尚未播出的节目，如果有就不允许删除
            $vendor = VideoVendor::where('code', $vendorCode)->firstOrFail()->delete();
        } catch (ModelNotFoundException $e) {
            throw new VideoException("{$vendorCode}我没找着这个供应商.", VIDEO_VENDOR_NOT_FOUND);
        }

    }

    public function getVideoVendor(string $vendorCode)
    {
        try {
            $vendor = VideoVendor::where('code', $vendorCode)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new VideoException("{$vendorCode}我没找着这个供应商.", VIDEO_VENDOR_NOT_FOUND);
        }

        return $vendor;
    }

    public function getLiveRoomList(int $pageNo, int $pageSize)
    {
        $liveRoomList = LiveRoom::orderBy('created_at', 'desc')
            ->skip($pageSize * ($pageNo - 1))->take($pageSize)->get();

        $userIdList = $liveRoomList->pluck('last_modify_user_id');

        $userList = User::all()->toArray();
        $userNameList = array_column($userList, 'name', 'id');

        $liveRoomList = $liveRoomList->toArray();

        foreach ($liveRoomList as &$liveRoom) {
            $liveRoom['last_modify_user_name'] = (string)array_get($userNameList, $liveRoom['last_modify_user_id']);
        }

        return $liveRoomList;
    }

    public function getLiveRoomCnt()
    {
        $liveRoomCnt = LiveRoom::count();

        return $liveRoomCnt;
    }

    public function createLiveRoom(array $credentials)
    {
        try {
            $liveRoomCode = (string)array_get($credentials, 'code');
            if (!empty($liveRoomCode)) {
                $liveRoom = LiveRoom::where('code', $liveRoomCode)->take(1)->firstOrFail();
                throw new MatrixException("直播室 {$liveRoomCode} 已存在.", LIVE_ROOM_EXISTS);
            }
        } catch (ModelNotFoundException $e) {
            $liveRoom = LiveRoom::create($credentials);
        }

        return $liveRoom;
    }

    public function updateLiveRoom(string $roomCode, array $credentials)
    {
        try {
            // TODO 判断是否还有正在播出的节目，如果有就不允许更新
            // TODO 判断是否还有尚未播出的节目，如果有就一并更新
            // TODO 判断是否还有固定节目表的节目，如果有就一并更新
            $liveRoom = LiveRoom::where('code', $roomCode)->firstOrFail();
            foreach ($credentials as $k => $v) {
                $liveRoom->{$k} = $v;
            }
            $liveRoom->save();

            $user = User::findOrFail($liveRoom['last_modify_user_id']);
            $liveRoom['last_modify_user_name'] = $user->name;

            return $liveRoom;
        } catch (ModelNotFoundException $e) {
            throw new VideoException("{$roomCode}我没找着这个直播室.", LIVE_ROOM_NOT_FOUND);
        }
    }

    public function removeLiveRoom(string $roomCode)
    {
        try {
            // TODO 判断是否还有正在播出的节目，如果有就不允许删除
            $staticTalkshow = StaticTalkshow::where('live_room_code', $roomCode)->take(1)->first();
            // TODO 判断是否还有尚未播出的节目，如果有就不允许删除
            $talkshow = Talkshow::where('live_room_code', $roomCode)
                ->where('end_time', '>', date('Y-m-d H:i:s'))->take(1)->first();

            if (!empty($staticTalkshow) || !empty($talkshow)) {
                throw new MatrixException("{$roomCode}这个直播室还有人用呢.", LIVE_ROOM_NOT_FOUND);
            }
            $liveRoom = LiveRoom::where('code', $roomCode)->firstOrFail()->delete();
        } catch (ModelNotFoundException $e) {
            throw new VideoException("{$roomCode}我没找着这个直播室.", LIVE_ROOM_NOT_FOUND);
        }
    }

    public function getLiveRoom(string $roomCode)
    {
        try {
            $liveRoom = LiveRoom::where('code', $roomCode)->firstOrFail()->toArray();
            $user = User::findOrFail($liveRoom['last_modify_user_id']);
            $liveRoom['last_modify_user_name'] = $user->name;

            return $liveRoom;
        } catch (ModelNotFoundException $e) {
            throw new VideoException("{$roomCode}我没找着这个直播室.", LIVE_ROOM_NOT_FOUND);
        }
    }

    public function getStaticTalkshowList(int $pageNo, int $pageSize)
    {
        $staticTalkshowList = StaticTalkshow::skip($pageSize * ($pageNo - 1))
            ->orderBy('start_time', 'asc')->take($pageSize)->get();

        $teacherIdList = $staticTalkshowList->pluck('teacher_id');
        $staticTalkshowList = $staticTalkshowList->toArray();

        if (empty($staticTalkshowList)) {
            return [];
        }

        $teacherList = Teacher::whereIn('id', $teacherIdList)->get()->toArray();
        $teacherList = array_column($teacherList, NULL, 'id');

        $categoryCodeList = array_column($teacherList, 'category_code');
        $categoryList = Category::whereIn('code', $categoryCodeList)->get()->toArray();
        $categoryNameList = array_column($categoryList, 'name', 'code');

        $userIdList = array_column($teacherList, 'user_id');
        $userList = User::all()->toArray();
        $userNameList = array_column($userList, 'name', 'id');

        foreach ($staticTalkshowList as &$staticTalkshow) {
            $staticTalkshow['category_code'] = (string)array_get((array)array_get($teacherList, $staticTalkshow['teacher_id']), 'category_code');
            $staticTalkshow['category_name'] = (string)array_get($categoryNameList, $staticTalkshow['category_code']);
            $staticTalkshow['teacher_user_id'] = (string)array_get((array)array_get($teacherList, $staticTalkshow['teacher_id']), 'user_id');
            $staticTalkshow['teacher_user_name'] = (string)array_get($userNameList, $staticTalkshow['teacher_user_id']);
            $staticTalkshow['last_modify_user_name'] = (string)array_get($userNameList, $staticTalkshow['last_modify_user_id']);
        }

        return $staticTalkshowList;
    }

    public function getStaticTalkshowCnt()
    {
        $staticTalkshowCnt = StaticTalkshow::count();

        return $staticTalkshowCnt;
    }

    public function createStaticTalkshow(array $credentials)
    {
        $startTime = (string)array_get($credentials, 'start_time');
        if (!empty($startTime)) {
            $credentials['start_time'] = (string)date('1970-01-01 H:i:s', strtotime($startTime));
        }
        $endTime = (string)array_get($credentials, 'end_time');
        if (!empty($endTime)) {
            $credentials['end_time'] = (string)date('1970-01-01 H:i:s', strtotime($endTime));
        }
        $staticTalkshow = StaticTalkshow::create($credentials);

        return $staticTalkshow;
    }

    public function updateStaticTalkshow(string $staticTalkshowId, array $credentials)
    {
        try {
            $startTime = (string)array_get($credentials, 'start_time');
            if (!empty($startTime)) {
                $credentials['start_time'] = (string)date('1970-01-01 H:i:s', strtotime($startTime));
            }
            $endTime = (string)array_get($credentials, 'end_time');
            if (!empty($endTime)) {
                $credentials['end_time'] = (string)date('1970-01-01 H:i:s', strtotime($endTime));
            }
            $staticTalkshow = StaticTalkshow::where('id', $staticTalkshowId)->firstOrFail();
            foreach ($credentials as $k => $v) {
                $staticTalkshow->{$k} = $v;
            }

            $staticTalkshow->save();
        } catch (ModelNotFoundException $e) {
            throw new VideoException("{$staticTalkshowId}我没找着这个固定节目.", STATIC_TALKSHOW_NOT_FOUND);
        }

        return $staticTalkshow;
    }

    public function removeStaticTalkshow(string $staticTalkshowId)
    {
        try {
            $staticTalkshow = StaticTalkshow::where('id', $staticTalkshowId)->firstOrFail()->delete();
        } catch (ModelNotFoundException $e) {
            throw new VideoException("{$staticTalkshowId}我没找着这个固定节目.", STATIC_TALKSHOW_NOT_FOUND);
        }
    }

    public function getStaticTalkshow(string $staticTalkshowId)
    {
        try {
            $staticTalkshow = StaticTalkshow::where('id', $staticTalkshowId)->firstOrFail();
            $teacher = Teacher::where('id', $staticTalkshow->teacher_id)->firstOrFail();

            $staticTalkshow = $staticTalkshow->toArray();
            $staticTalkshow['category_code'] = $teacher->category_code;
            $staticTalkshow['teacher_user_id'] = $teacher->user_id;
        } catch (ModelNotFoundException $e) {
            throw new VideoException("{$staticTalkshowId}我没找着这个固定节目.", STATIC_TALKSHOW_NOT_FOUND);
        }

        return $staticTalkshow;
    }

    public function getTalkshowList(int $pageNo, int $pageSize, array $credentials)
    {
        $date = array_get($credentials, 'date');
        $talkshowList = Talkshow::whereBetween('start_time', [
            "$date 00:00:00", "$date 23:59:59"
        ])->orderBy('start_time', 'asc')->skip($pageSize * ($pageNo - 1))
          ->take($pageSize)->get();

        if (empty($talkshowList)) {
            return [];
        }

        $teacherIdList = $talkshowList->pluck('teacher_id');

        $teacherList = Teacher::whereIn('id', $teacherIdList)->get()->toArray();
        $teacherList = array_column($teacherList, NULL, 'id');

        $categoryCodeList = array_column($teacherList, 'category_code');
        $categoryList = Category::whereIn('code', $categoryCodeList)->get()->toArray();
        $categoryNameList = array_column($categoryList, 'name', 'code');

        $userIdList = array_column($teacherList, 'user_id');
        $userList = User::all()->toArray();
        $userNameList = array_column($userList, 'name', 'id');

        $talkshowList = $talkshowList->toArray();
        foreach ($talkshowList as &$talkshow) {
            $talkshow['category_code'] = (string)array_get((array)array_get($teacherList, $talkshow['teacher_id']), 'category_code');
            $talkshow['category_name'] = (string)array_get($categoryNameList, $talkshow['category_code']);
            $talkshow['teacher_user_id'] = (string)array_get((array)array_get($teacherList, $talkshow['teacher_id']), 'user_id');
            $talkshow['teacher_user_name'] = (string)array_get($userNameList, $talkshow['teacher_user_id']);
            $talkshow['last_modify_user_name'] = (string)array_get($userNameList, $talkshow['last_modify_user_id']);

            // set status
            if ($talkshow['type'] == 'play') {
                if (time() < strtotime($talkshow['start_time'])) { // 尚未开始
                    if (time() > strtotime($talkshow['start_time']) - 600) { // 10分钟之内，即将开始
                        $talkshow['status'] = Talkshow::STATUS_GOING;
                    } else {
                        $talkshow['status'] = Talkshow::STATUS_PREPARE; // 这条判断只能用于节目表
                    }
                } elseif (time() > strtotime($talkshow['end_time']) || $talkshow['status'] > Talkshow::STATUS_PLAY) { // 已经结束
                    if (empty($talkshow['play_url'])) { // 直播结束
                        $talkshow['status'] = Talkshow::STATUS_DONE;
                    } else {
                        $talkshow['status'] = Talkshow::STATUS_REPLAY;
                    }
                } else { // 正在播放
                    $talkshow['status'] = Talkshow::STATUS_PLAY;
                }
            } else {
                $forceDiffTime = (int)((strtotime($talkshow['end_time']) - strtotime($talkshow['start_time'])) * 0.2);
                $forceEndTime = strtotime($talkshow['end_time']) + $forceDiffTime;
                if ($talkshow['status'] < Talkshow::STATUS_PLAY) { // 尚未开始
                    if (time() > strtotime($talkshow['start_time']) - 600) { // 10分钟之内，即将开始
                        $talkshow['status'] = Talkshow::STATUS_GOING;
                    } else {
                        $talkshow['status'] = Talkshow::STATUS_PREPARE; // 这条判断只能用于节目表
                    }
                } elseif (time() > $forceEndTime || $talkshow['status'] > Talkshow::STATUS_PLAY) { // 超时结束或强制结束
                    if (empty($talkshow['play_url'])) { // 直播结束
                        $talkshow['status'] = Talkshow::STATUS_DONE;
                    } else {
                        $talkshow['status'] = Talkshow::STATUS_REPLAY;
                    }
                }
            }

            switch ($talkshow['status']) {
                case Talkshow::STATUS_NEW:
                    $talkshow['status_title'] = '没有预告';
                    break;
                case Talkshow::STATUS_PREPARE:
                    $talkshow['status_title'] = '蓝字预告';
                    break;
                case Talkshow::STATUS_GOING:
                    $talkshow['status_title'] = '即将开始';
                    break;
                case Talkshow::STATUS_PLAY:
                    $talkshow['status_title'] = '正在播出';
                    break;
                case Talkshow::STATUS_DONE:
                    $talkshow['status_title'] = '直播结束';
                    break;
                case Talkshow::STATUS_REPLAY:
                    $talkshow['status_title'] = '看回放';
                    break;
                default:
                    $talkshow['status_title'] = '未知状态';
                    break;
            }
        }

        return $talkshowList;
    }

    public function getTalkshowCnt(array $credentials)
    {
        $date = array_get($credentials, 'date');
        $talkshowCnt = Talkshow::whereBetween('start_time', [
            "$date 00:00:00", "$date 23:59:59"
        ])->count();

        return $talkshowCnt;
    }

    protected function generateTalkshowCode(int $length)
    {
        return str_random($length);
    }

    protected function autoSigninVideo(array $credentials, string $oldUrl = '')
    {
        $videoData = [
            'video_key' => '',
            'creator_user_id' => Auth::user()->id,
            'url' => (string)array_get($credentials, 'play_url'),
            'title' => (string)array_get($credentials, 'title'),
            'description' => (string)array_get($credentials, 'description'),
            'published_at' => date('Ymd'),
            'active' => 1,
            'is_public' => 1,
        ];

        try {
            $teacherId = (int)array_get($credentials, 'teacher_id');
            $teacher = Teacher::findOrFail($teacherId);
            $videoData['category_code'] = $teacher->category_code;
            $videoData['author'] = $teacher->user_id;
        } catch (ModelNotFoundException $e) {
            throw new VideoException('这位老师不存在', COLUMN_TEACHER_NOT_FOUND);
        }

        $newUrl = (string)array_get($credentials, 'play_url');
        try {
            $video = VideoSignin::where('url', $newUrl)->take(1)->firstOrFail();
            unset($videoData['video_key']);
            foreach ($videoData as $k => $v) {
                $video->{$k} = $v;
            }
            $video->save();
            return ;
        } catch (ModelNotFoundException $e) {
        }

        try {
            if (empty($oldUrl)) {
                $oldUrl = (string)array_get($credentials, 'play_url');
            }
            $video = VideoSignin::where('url', $oldUrl)->take(1)->firstOrFail();
            unset($videoData['video_key']);
            foreach ($videoData as $k => $v) {
                $video->{$k} = $v;
            }
            $video->save();
        } catch (ModelNotFoundException $e) {
            $video = VideoSignin::create($videoData);
            $video->video_key = $video->generateStr();
            $video->save();
        }
    }

    public function createTalkshow(array $credentials)
    {
        $credentials['code'] = $this->generateTalkshowCode(16);
        $credentials['status'] = Talkshow::STATUS_NEW;
        $playUrl = array_get($credentials, 'play_url');
        if (!empty($playUrl)) {
            $signinFormat = config('video.video.signin_format');
            if (!empty($signinFormat) && strpos($playUrl, $signinFormat) !== false) {
                throw new TalkshowException('播放链接不能是我们自己的登记地址.', TALKSHOW_PLAY_URL_EXISTS);
            }

            $talkshowCnt = Talkshow::where('play_url', array_get($credentials, 'play_url'))->count();
            if ($talkshowCnt > 0) {
                throw new TalkshowException('录播链接重复', TALKSHOW_PLAY_URL_EXISTS);
            }
            if (strpos($playUrl, 'gensee') !== false) {
                $urlArr = explode('-', $playUrl);
                if (count($urlArr) > 1) {
                    $credentials['code'] = $urlArr[1];
                }
            }

            // 兼容微吼视频
            if (strpos($playUrl, 'vhall') !== false) {
                $urlArr = explode('/', $playUrl);
                if (strpos($urlArr[count($urlArr) - 1], '?') !== false) {
                    $credentials['code']= explode('?', $urlArr[count($urlArr) - 1])[0];
                } else {
                    $credentials['code'] = $urlArr[count($urlArr) - 1];
                }
            }
        }

        $talkshow = Talkshow::create($credentials);

        // 同步数据到 跑马灯
        self::syncDynamicAdOfCreate($talkshow->toArray());

        if (!empty($playUrl)) {
            // TODO 自动登记视频
            $this->autoSigninVideo($credentials);
        }

        return $talkshow;
    }

    private function syncDynamicAdOfCreate(array $talkshowData)
    {
        $startTimeStr = date('H:i', strtotime(array_get($talkshowData, 'start_time')));
        $endTimeStr = date('H:i', strtotime(array_get($talkshowData, 'end_time')));

        try {
            $teacherId = (int)array_get($talkshowData, 'teacher_id');
            $teacher = Teacher::where('id', $teacherId)->firstOrFail()->toArray();
            $teacherUserId = (int)array_get($teacher, 'user_id');
            $user = User::where('id', $teacherUserId)->firstOrFail()->toArray();
            $userName = (string)array_get($user, 'name');
    
            $categoryCode = array_get($teacher, 'category_code');
            $category = Category::where('code', $categoryCode)->firstOrFail()->toArray();
            $categoryName = array_get($category, 'name');
        } catch (ModelNotFoundException $e) {
            throw new MatrixException('关联数据错误', SYS_STATUS_ERROR_UNKNOW);
        }

        $title = $startTimeStr . '-' . $endTimeStr . ' 主讲人：' . $userName . ' 【' . $categoryName . '】 ' . array_get($talkshowData, 'title');

        $videoVodid = (string)array_get($talkshowData, 'type') == 'live' ? (string)array_get($talkshowData, 'live_room_code') : (string)array_get($talkshowData, 'code');

        // 获取上一个节目信息
        $prevTalkshow = Talkshow::where('end_time', '<=', array_get($talkshowData, 'start_time'))->orderBy('end_time', 'desc')->first()->toArray();

        if (empty($prevTalkshow)) {
            if (new DateTime(array_get($talkshowData, 'start_time')) < new DateTime()) {
                $startTimeStr = (string)array_get($talkshowData, 'start_time');
            } else {
                $startTimeStr = date('Y-m-d H:i:s');
            }
        } else {
            $startTimeStr = self::getNextTime((string)array_get($prevTalkshow, 'end_time'), 1);
        }

        $dynamicAdDatas = [
            [
                'title' => $title,
                'content_url' => sprintf('/api/v2/client/live/talkshow/%s', (string)array_get($talkshowData, 'code')),
                'start_at' => $startTimeStr,
                'end_at' => (string)array_get($talkshowData, 'start_time'),
                'terminal_codes' => Talkshow::DYNAMIC_AD_TERMINAL_TYPES,
                'permission_codes' => [config('packagetype.basic_package')],
                'active' => DynamicAd::ACTIVE_DEFAULT,
                'sign' => DynamicAd::SIGN_DEFAULT,
                'source_type' => Talkshow::NOTICE_SYNC_TO_DYNAMIC_AD_TYPE,
                'source_id' => (int)array_get($talkshowData, 'id')
            ],
            [
                'title' => $title,
                'content_url' => sprintf('/api/v2/client/live/talkshow/%s', (string)array_get($talkshowData, 'code')),
                'start_at' => (string)array_get($talkshowData, 'start_time'),
                'end_at' => (string)array_get($talkshowData, 'end_time'),
                'terminal_codes' => Talkshow::DYNAMIC_AD_TERMINAL_TYPES,
                'permission_codes' => [config('packagetype.basic_package')],
                'active' => DynamicAd::ACTIVE_DEFAULT,
                'sign' => DynamicAd::SIGN_DEFAULT,
                'source_type' => Talkshow::SYNC_TO_DYNAMIC_AD_TYPE,
                'source_id' => (int)array_get($talkshowData, 'id')
            ]
        ];

        foreach ($dynamicAdDatas as $dynamicAdData) {
            $dynamicAd = $this->dynamicAdManager->createDynamicAd($dynamicAdData);
            $this->operateLogContract->record('create', 'dynamic_ad', $dynamicAd->id, "用户 ".Auth::user()->name." 同步创建了一个跑马灯 {$dynamicAd}", '', Auth::user()->id);
        }

        // 影响下一个节目信息
        // 可能影响多个跑马灯信息
        try {
            $nextTalkshowOfCreated = Talkshow::where('start_time', '>=', array_get($talkshowData, 'end_time'))->orderBy('start_time', 'asc')->firstOrFail()->toArray();
            $prevTalkshowOfCreated = Talkshow::where('end_time', '<=', array_get($nextTalkshowOfCreated, 'start_time'))->where('id', '<>', (int)array_get($talkshowData, 'id'))->orderBy('end_time', 'desc')->firstOrFail()->toArray();
            $noticeDynamicAdsOfCreated = DynamicAd::where('start_at', self::getNextTime(array_get($prevTalkshowOfCreated, 'end_time'), 1))->whereIn('source_type', [Talkshow::NOTICE_SYNC_TO_DYNAMIC_AD_TYPE])->get();
            
            foreach  ($noticeDynamicAdsOfCreated as $noticeDynamicAdOfCreated) {
                if (new DateTime((string)array_get($prevTalkshowOfCreated, 'end_time')) <= new DateTime((string)array_get($talkshowData, 'end_time')) && new DateTime((string)array_get($talkshowData, 'end_time')) <= new DateTime((string)$noticeDynamicAdOfCreated->end_at)) {
                    Log::info("123");
                    $noticeDynamicAdOfCreated->start_at = self::getNextTime((string)array_get($talkshowData, 'end_time'), 1);
                    $noticeDynamicAdOfCreated->save();
                    $this->operateLogContract->record('update', 'dynamic_ad', $noticeDynamicAdOfCreated->id, "用户 ".Auth::user()->name." 同步修改了一个跑马灯 {$noticeDynamicAdOfCreated}", '', Auth::User()->id);
                }
            }
        } catch (ModelNotFoundException $e) {
        }
    }

    public function updateTalkshow(string $talkshowCode, array $credentials)
    {
        try {
            $playUrl = (string)array_get($credentials, 'play_url');
            if (!empty($playUrl)) {
                $signinFormat = config('video.video.signin_format');
                if (!empty($signinFormat) && strpos($playUrl, $signinFormat) !== false) {
                    throw new TalkshowException('播放链接不能是我们自己的登记地址.', TALKSHOW_PLAY_URL_EXISTS);
                }
                $talkshowCnt = Talkshow::where('play_url', $playUrl)
                    ->where('code', '<>', $talkshowCode)->count();
                if ($talkshowCnt > 0) {
                    throw new TalkshowException('录播链接重复', TALKSHOW_PLAY_URL_EXISTS);
                }
            }

            $talkshow = Talkshow::where('code', $talkshowCode)->firstOrFail();
            if (!empty($talkshow->play_url) && empty($playUrl)) {
                throw new TalkshowException('禁止清空已有的节目URL', TALKSHOW_PLAY_URL_EXISTS);
            }

            $oldUrl = $talkshow->play_url;

            if (!empty($playUrl)) {
                if (strpos($playUrl, 'gensee') !== false) {
                    $urlArr = explode('-', $playUrl);
                    if (count($urlArr) > 1) {
                        $credentials['code'] = $urlArr[1];
                    }
                }
                // 兼容微吼视频
                if (strpos($playUrl, 'vhall') !== false) {
                    $urlArr = explode('/', $playUrl);
                    if (strpos($urlArr[count($urlArr) - 1], '?') !== false) {
                        $credentials['code'] = explode('?', $urlArr[count($urlArr) - 1])[0];
                    } else {
                        $credentials['code'] = $urlArr[count($urlArr) - 1];
                    }
                }
            }

            $code = array_get($credentials, 'code');
            if (!empty($code) && $talkshow->code != $code) {
                $discussList = Discuss::where('talkshow_code', $talkshow->code)->get();
                $discussList->each(function ($item, $key) use ($code) {
                    $item->talkshow_code = $code;
                    $item->save();
                });
            }

            if (!empty($playUrl)) {
                // TODO 如果更新play_url，则判断是否已登记并重新登记视频
                $this->autoSigninVideo($credentials, $oldUrl);
            }

            // 更新跑马灯数据
            self::syncDynamicAdOfUpdate($talkshow->toArray(), $credentials);

            foreach ($credentials as $k => $v) {
                $talkshow->{$k} = $v;
            }

            $talkshow->save();
        } catch (ModelNotFoundException $e) {
            throw new VideoException("{$talkshowCode}我没找着这个节目.", TALKSHOW_NOT_FOUND);
        }

        return $talkshow;
    }

    private function syncDynamicAdOfUpdate(array $talkshowData, array $updateTalkshowData)
    {
        // 获取前后可能影响的节目数据
        $talkshowId = array_get($talkshowData, 'id');
        try {
            $dynamicAdOfNotice = DynamicAd::where('source_type', Talkshow::NOTICE_SYNC_TO_DYNAMIC_AD_TYPE)->where('source_id', $talkshowId)->firstOrFail();
            $dynamicAd = DynamicAd::where('source_type', Talkshow::SYNC_TO_DYNAMIC_AD_TYPE)->where('source_id', $talkshowId)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            Log::error("找不到对应的跑马灯记录: id:" . $talkshowId, [$updateTalkshowData]);
            return ;
        }
        

        $startTimeStr = date('H:i', strtotime(array_get($updateTalkshowData, 'start_time')));
        $endTimeStr = date('H:i', strtotime(array_get($updateTalkshowData, 'end_time')));

        try {
            $teacherId = (int)array_get($updateTalkshowData, 'teacher_id');
            $teacher = Teacher::where('id', $teacherId)->firstOrFail()->toArray();
            $teacherUserId = (int)array_get($teacher, 'user_id');
            $user = User::where('id', $teacherUserId)->firstOrFail()->toArray();
            $userName = (string)array_get($user, 'name');
    
            $categoryCode = array_get($teacher, 'category_code');
            $category = Category::where('code', $categoryCode)->firstOrFail()->toArray();
            $categoryName = array_get($category, 'name');
        } catch (ModelNotFoundException $e) {
            throw new MatrixException('关联数据错误', SYS_STATUS_ERROR_UNKNOW);
        }

        $title = $startTimeStr . '-' . $endTimeStr . ' 主讲人：' . $userName . ' 【' . $categoryName . '】 ' . array_get($talkshowData, 'title');

        $dynamicAdOfNotice->title = $title;
        $dynamicAd->title = $title;

        $videoVodid = (string)array_get($updateTalkshowData, 'type') == 'live' ? (string)array_get($updateTalkshowData, 'live_room_code') : (string)array_get($updateTalkshowData, 'code');

        $dynamicAdOfNotice->content_url = sprintf('/api/v2/client/live/talkshow/%s', (string)array_get($updateTalkshowData, 'code'));
        $dynamicAd->content_url = sprintf('/api/v2/client/live/talkshow/%s', (string)array_get($updateTalkshowData, 'code'));

        // 判断展示时间是否变化
        if (array_get($talkshowData, 'start_time') !== array_get($updateTalkshowData, 'start_time') || array_get($talkshowData, 'end_time') !== array_get($updateTalkshowData, 'end_time')) {
            // start_time
            if (array_get($talkshowData, 'start_time') !== array_get($updateTalkshowData, 'start_time')) {
                $prevTalkshow = Talkshow::where('end_time', '<=', array_get($updateTalkshowData, 'start_time'))->orderBy('end_time', 'desc')->first()->toArray();

                if (empty($prevTalkshow)) {
                    if (new DateTime(array_get($updateTalkshowData, 'start_time')) < new DateTime()) {
                        $dynamicAdOfNotice->start_at = (string)array_get($updateTalkshowData, 'start_time');
                    } else {
                        $dynamicAdOfNotice->start_at = date('Y-m-d H:i:s');
                    }
                } else {
                    $dynamicAdOfNotice->start_at = self::getNextTime((string)array_get($prevTalkshow, 'end_time'), 1);
                }
                $dynamicAdOfNotice->end_at = (string)array_get($updateTalkshowData, 'start_time');

                $dynamicAd->start_at = (string)array_get($updateTalkshowData, 'start_time');
            }

            // end_time
            if (array_get($talkshowData, 'end_time') !== array_get($updateTalkshowData, 'end_time')) {
                // 老数据影响问题
                $noticeDynamicAds = DynamicAd::where('start_at', self::getNextTime(array_get($talkshowData, 'end_time'), 1))->where('source_type', Talkshow::NOTICE_SYNC_TO_DYNAMIC_AD_TYPE)->get();
                $noticeDynamicAdIds = array_column($noticeDynamicAds->toArray(), 'id');

                foreach ($noticeDynamicAds as $noticeDynamicAd) {
                    $prevTalkshow = Talkshow::where('end_time', '<=', $noticeDynamicAd->end_at)->where('id', '<>', array_get($talkshowData, 'id'))->orderBy('end_time', 'desc')->first()->toArray();

                    if (empty($prevTalkshow)) {
                        // 判断 更新数据的结束时间是否在 节目预告的节目开始时间之前
                        if (new DateTime(array_get($updateTalkshowData, 'end_time')) <= new DateTime($noticeDynamicAd->end_at)) {
                            $noticeDynamicAd->start_at = self::getNextTime((string)array_get($updateTalkshowData, 'end_time'), 1);
                        } else {
                            if (new DateTime($noticeDynamicAd->end_at) < new DateTime()) {
                                $noticeDynamicAd->start_at = (string)$noticeDynamicAd->end_time;
                            } else {
                                $noticeDynamicAd->start_at = date('Y-m-d H:i:s');
                            }
                        }
                    } else {
                        // 判断 更新数据的结束时间是否在 上一个节目时间结束时间之后
                        if (new DateTime(array_get($prevTalkshow, 'end_time')) <= new DateTime(array_get($updateTalkshowData, 'end_time')) && new DateTime(array_get($updateTalkshowData, 'end_time')) <= new DateTime($noticeDynamicAd->end_at)) {
                            $noticeDynamicAd->start_at = self::getNextTime((string)array_get($updateTalkshowData, 'end_time'), 1);
                        } else {
                            $noticeDynamicAd->start_at = self::getNextTime((string)array_get($prevTalkshow, 'end_time'), 1);
                        }
                    }
                    $noticeDynamicAd->save();
                    $this->operateLogContract->record('update', 'dynamic_ad', $noticeDynamicAd->id, "用户 ".Auth::user()->name." 同步修改了一个跑马灯 {$noticeDynamicAd}", '', Auth::user()->id);
                }

                $dynamicAd->end_at = (string)array_get($updateTalkshowData, 'end_time');

                // 新数据 影响
                try {
                    $nextTalkshowOfUpdated = Talkshow::where('start_time', '>=', array_get($updateTalkshowData, 'end_time'))->orderby('start_time', 'asc')->firstOrFail()->toArray();
                    $prevTalkshowOfUpdated = Talkshow::where('end_time', '<=', (string)array_get($nextTalkshowOfUpdated, 'start_time'))->orderBy('end_time', 'desc')->firstOrFail()->toArray();
                    $noticeDynamicAdsOfUpdated = DynamicAd::where('start_at', self::getNextTime(array_get($prevTalkshowOfUpdated, 'end_time'), 1))->where('source_type', Talkshow::NOTICE_SYNC_TO_DYNAMIC_AD_TYPE)->get();

                    foreach ($noticeDynamicAdsOfUpdated as $noticeDynamicAdOfUpdated) {
                        if (!in_array($noticeDynamicAdOfUpdated->id, $noticeDynamicAdIds)) {
                            if (new DateTime((string)array_get($prevTalkshowOfUpdated, 'end_time')) <= new DateTime((string)array_get($updateTalkshowData, 'end_time')) && new DateTime((string)array_get($updateTalkshowData, 'end_time')) <= new DateTime((string)$noticeDynamicAdOfUpdated->end_at)) {
                                $noticeDynamicAdOfUpdated->start_at = self::getNextTime((string)array_get($updateTalkshowData, 'end_time'), 1);
                                $noticeDynamicAdOfUpdated->save();
                                $this->operateLogContract->record('update', 'dynamic_ad', $noticeDynamicAdOfUpdated->id, "用户 ".Auth::user()->name." 同步修改了一个跑马灯 {$noticeDynamicAdOfUpdated}", '', Auth::user()->id);
                            }
                        }
                    }
                } catch (ModelNotFoundException $e) {
                }
            }
        }

        $dynamicAdOfNotice->save();
        $this->operateLogContract->record('update', 'dynamic_ad', $dynamicAdOfNotice->id, "用户 ".Auth::user()->name." 同步修改了一个跑马灯 {$dynamicAdOfNotice}", '', Auth::user()->id);

        $dynamicAd->save();
        $this->operateLogContract->record('update', 'dynamic_ad', $dynamicAd->id, "用户 ".Auth::user()->name." 同步修改了一个跑马灯 {$dynamicAd}", '', Auth::user()->id);
    }

    public function removeTalkshow(string $talkshowCode)
    {
        try {
            $talkshow = Talkshow::where('code', $talkshowCode)->firstOrFail();
            $talkshow->delete();

            // 同步更新 跑马灯数据
            self::syncDynamicAdOfDelete($talkshow->toArray());
        } catch (ModelNotFoundException $e) {
            throw new VideoException("{$talkshowCode}我没找着这个节目.", TALKSHOW_NOT_FOUND);
        }

        return $talkshow;
    }

    private function syncDynamicAdOfDelete(array $talkshowData)
    {
        // 删除 对应 跑马灯 记录
        $dynamicAdsOfTalkshow = DynamicAd::whereIn('source_type', [Talkshow::SYNC_TO_DYNAMIC_AD_TYPE, Talkshow::NOTICE_SYNC_TO_DYNAMIC_AD_TYPE])->where('source_id', (int)array_get($talkshowData, 'id'))->get();
        
        foreach ($dynamicAdsOfTalkshow as $dynamicAdOfTalkshow) {
            $dynamicAdId = array_get($dynamicAdOfTalkshow, 'id');
            $dynamicAd = $this->dynamicAdManager->deleteDynamicAd($dynamicAdId);
            $this->operateLogContract->record('delete', 'dynamic_ad', $dynamicAd->id, "用户 ".Auth::user()->name." 同步删除了一个跑马灯 {$dynamicAd}", '', Auth::user()->id);
        }

        // 修改影响数据
        $noticeDynamicAds = DynamicAd::where('start_at', self::getNextTime(array_get($talkshowData, 'end_time'), 1))->where('source_type', Talkshow::NOTICE_SYNC_TO_DYNAMIC_AD_TYPE)->get();

        foreach ($noticeDynamicAds as $noticeDynamicAd) {
            $prevTalkshow = Talkshow::where('end_time', '<=', $noticeDynamicAd->end_at)->orderBy('end_time', 'desc')->first()->toArray();

            if (empty($prevTalkshow)) {
                if (new DateTime($noticeDynamicAd->end_at) < new DateTime()) {
                    $noticeDynamicAd->start_at = (string)$noticeDynamicAd->end_at;
                } else {
                    $noticeDynamicAd->start_at = date('Y-m-d H:i:s');
                }
            } else {
                $noticeDynamicAd->start_at = self::getNextTime((string)array_get($prevTalkshow, 'end_time'), 1);
            }
            $noticeDynamicAd->save();
            $this->operateLogContract->record('update', 'dynamic_ad', $noticeDynamicAd->id, "用户 ".Auth::user()->name." 同步修改了一个跑马灯 {$noticeDynamicAd}", '', Auth::user()->id);
        }

    }

    public function getTalkshow(string $talkshowCode)
    {
        try {
            $talkshow = Talkshow::where('code', $talkshowCode)->firstOrFail();
            $teacher = Teacher::where('id', $talkshow->teacher_id)->firstOrFail();

            $talkshow = $talkshow->toArray();
            $talkshow['category_code'] = $teacher->category_code;
            $talkshow['teacher_user_id'] = $teacher->user_id;
        } catch (ModelNotFoundException $e) {
            throw new VideoException("{$talkshowCode}我没找着这个节目.", TALKSHOW_NOT_FOUND);
        }

        return $talkshow;
    }

    public function getTalkshowByUrl(string $url)
    {
        try {
            $talkshow = Talkshow::where('play_url', $url)->firstOrFail();
            $teacher = Teacher::where('id', $talkshow->teacher_id)->firstOrFail();

            $talkshow = $talkshow->toArray();
            $talkshow['category_code'] = $teacher->category_code;
            $talkshow['teacher_user_id'] = $teacher->user_id;
        } catch (ModelNotFoundException $e) {
            throw new VideoException("{$url}我没找着这个节目.", TALKSHOW_NOT_FOUND);
        }

        return $talkshow;
    }

    public function importTalkshowList(array $credentials)
    {
        $date = array_get($credentials, 'date');
        $staticTalkshowList = StaticTalkshow::orderBy('start_time', 'asc')->get();

        $staticTalkshowList->each(function ($item, $key) use ($date) {
            $talkshowData = [
                'video_vendor_code' => $item->video_vendor_code,
                'title' => $item->title,
                'teacher_id' => $item->teacher_id,
                'start_time' => date("$date H:i:s", strtotime($item->start_time)),
                'end_time' => date("$date H:i:s", strtotime($item->end_time)),
                'banner_url' => $item->banner_url,
                'type' => $item->type,
                'live_room_code' => $item->live_room_code,
                'boardcast_content' => $item->boardcast_content,
                'play_url' => '',
                'code' => $this->generateTalkshowCode(16),
                'status' => Talkshow::STATUS_NEW,
                'last_modify_user_id' => Auth::user()->id,
            ];
            $talkshow = Talkshow::create($talkshowData);
            self::syncDynamicAdOfCreate($talkshow->toArray());
        });
    }

    public function getDiscussList(int $pageNo, int $pageSize, array $credentials)
    {
        $status = array_get($credentials, 'status');
        $discussList = Discuss::where('status', $status);

        foreach ($credentials as $k => $v) {
            if (!in_array($k, ['page_no', 'page_size', 'start_time', 'end_time', 'status', 'category_code', 'title'])) {
                $discussList = $discussList->where($k, $v);
            }
        }

        $categoryCode = array_get($credentials, 'category_code');
        if (!empty($categoryCode)) {
            $teacherIdList = Teacher::where('category_code', $categoryCode)->get()->pluck('id')->toArray();
            $talkshowCodeList = Talkshow::whereIn('teacher_id', $teacherIdList)->get()->pluck('code')->toArray();
            $discussList = $discussList->whereIn('talkshow_code', $talkshowCodeList);
        }

        $title = array_get($credentials, 'title');
        if (!empty($title)) {
            $talkshowCodeList = Talkshow::where('title', 'like', "%$title%")->get()->pluck('code')->toArray();
            $discussList = $discussList->whereIn('talkshow_code', $talkshowCodeList);
        }

        $startTime = array_get($credentials, 'start_time');
        if (empty($startTime)) {
            $startTime = '1970-01-01 00:00:00';
        }

        $endTime = array_get($credentials, 'end_time');
        if (empty($endTime)) {
            $endTime = date('Y-m-d H:i:s');
        }

        $discussList = $discussList->whereBetween('created_at', [$startTime, $endTime]);

        if ($status == Discuss::STATUS_APPROVED || $status == Discuss::STATUS_REJECTED) {
            $discussList = $discussList->orderBy('examine_at', 'desc');
        }

        $discussList = $discussList->skip($pageSize * ($pageNo - 1))->take($pageSize)->get();
        
        $talkshowCodeList = $discussList->pluck('talkshow_code');
        $talkshowList = Talkshow::whereIn('code', $talkshowCodeList)->get()->toArray();
        $talkshowList = array_column($talkshowList, NULL, 'code');


        $teacherIdList = array_column($talkshowList, 'teacher_id');
        $teacherList = Teacher::whereIn('id', $teacherIdList)->get()->toArray();
        $teacherList = array_column($teacherList, NULL, 'id');

        $categoryCodeList = array_column($teacherList, 'category_code');
        $categoryList = Category::whereIn('code', $categoryCodeList)->get()->toArray();
        $categoryNameList = array_column($categoryList, 'name', 'code');

        $userList = User::all()->toArray();
        $userNameList = array_column($userList, 'name', 'id');

        $discussList = $discussList->toArray();
        foreach ($discussList as &$discuss) {
            $talkshow = (array)array_get($talkshowList, $discuss['talkshow_code']);
            $teacher = (array)array_get($teacherList, $talkshow['teacher_id']);
            $discuss['category_code'] = (string)array_get($teacher, 'category_code');
            $discuss['category_name'] = (string)array_get($categoryNameList, $discuss['category_code']);
            $discuss['title'] = (string)array_get($talkshow, 'title');
            $discuss['examine_user_name'] = empty($discuss['examine_user_id']) ? "" : (string)array_get($userNameList, $discuss['examine_user_id']);
        }

        return $discussList;
    }

    public function getDiscussCnt(array $credentials)
    {
        $status = array_get($credentials, 'status');
        $discussCnt = Discuss::where('status', $status);

        foreach ($credentials as $k => $v) {
            if (!in_array($k, ['page_no', 'page_size', 'start_time', 'end_time', 'status', 'category_code', 'title'])) {
                $discussCnt = $discussCnt->where($k, $v);
            }
        }

        $categoryCode = array_get($credentials, 'category_code');
        if (!empty($categoryCode)) {
            $teacherIdList = Teacher::where('category_code', $categoryCode)->get()->pluck('id')->toArray();
            $talkshowCodeList = Talkshow::whereIn('teacher_id', $teacherIdList)->get()->pluck('code')->toArray();
            $discussCnt = $discussCnt->whereIn('talkshow_code', $talkshowCodeList);
        }

        $title = array_get($credentials, 'title');
        if (!empty($title)) {
            $talkshowCodeList = Talkshow::where('title', 'like', "%$title%")->get()->pluck('code')->toArray();
            $discussCnt = $discussCnt->whereIn('talkshow_code', $talkshowCodeList);
        }

        $startTime = array_get($credentials, 'start_time');
        if (empty($startTime)) {
            $startTime = '1970-01-01 00:00:00';
        }

        $endTime = array_get($credentials, 'end_time');
        if (empty($endTime)) {
            $endTime = date('Y-m-d H:i:s');
        }

        $discussCnt = $discussCnt->whereBetween('created_at', [$startTime, $endTime])->count();

        return $discussCnt;
    }

    public function examineDiscuss(int $discussId, int $operate)
    {
        try {
            $discuss = Discuss::findOrFail($discussId);
            // 以下注释代码为重复审批防御，由于业务需要变更审批策略，需要开放重复审批.
            // -- modify by purehow
//            if ($discuss->status != Discuss::STATUS_NEW) {
//                throw new VideoException("你这是逗我，{$discussId}已经审批过啦！", DISCUSS_ALREADY_EXAMINE);
//            }

            $discuss->status = $operate;
            $discuss->examine_user_id = Auth::id();
            $discuss->examine_at = now();
            $discuss->save();
        } catch (ModelNotFoundException $e) {
            throw new VideoException("{$discussId}我没找着这个讨论记录.", DISCUSS_NOT_FOUND);
        };
    }

    public function createDiscuss(array $credentials)
    {
        $credentials['status'] = array_key_exists('status', $credentials) ? $credentials['status'] : Discuss::STATUS_NEW;
        $discuss = Discuss::create($credentials);

        return $discuss;
    }

    public function getPredictInfo()
    {
        $todayStartTime = date('Y-m-d 00:00:00');
        $todayEndTime = date('Y-m-d 23:59:59');

        $todayTalkshowList = Talkshow::whereBetween('start_time', [$todayStartTime, $todayEndTime])
            ->orderBy('start_time', 'asc')->get();

        $talkshow = $todayTalkshowList->filter(function ($item, $key) {
            if (strtotime($item->end_time) > time() && $item->status < Talkshow::STATUS_DONE) { // 还有尚未播放完的节目
                return true;
            }
        })->first();

        if (empty($talkshow)) {
            return [];
        }

        try {
            $teacher = Teacher::findOrFail($talkshow->teacher_id);
            $user = User::findOrFail($teacher->user_id);
        } catch (ModelNotFoundException $e) {
            throw new MatrixException("找不着这老师: {$talkshow->teacher_id}", COLUMN_TEACHER_NOT_FOUND);
        }

        try {
            $videoVendor = VideoVendor::where('code', $talkshow->video_vendor_code)->take(1)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new MatrixException("您要的这个节目预告视频供应商不存在: {$talkshow->video_vendor_code}", VIDEO_VENDOR_NOT_FOUND);
        }

        try {
            $category = Category::where('code', $teacher->category_code)->take(1)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new MatrixException("您要的这个节目预告分类不存在: {$teacher->category_code}", COLUMN_CATEGORY_NOT_FOUND);
        }

        $talkshow = $talkshow->toArray();

        $talkshow['teacher_user_name'] = $user->name;

        if (!empty($talkshow['play_url'])) {
            try {
                $video = VideoSignin::where('url', $talkshow['play_url'])->take(1)->firstOrFail();
                $talkshow['play_url'] = sprintf('%s%s', config('video.video.url'), $video->video_key);
            } catch (ModelNotFoundException $e) {
                unset($talkshow['play_url']);
            }
            $vodid = $talkshow['code'];
        } else {
            $vodid = $talkshow['live_room_code'];
        }

        $talkshow['sdk_video_vodid'] = $vodid;
        $talkshow['sdk_video_domain'] = $videoVendor->domain;
        $talkshow['sdk_video_vendor'] = $videoVendor->code;

        $talkshow['category_code'] = $category->code;
        $talkshow['category_name'] = $category->name;

        $talkshow['allow_discuss'] = 1;

        if ($talkshow['type'] == 'play') {
            if (strtotime($talkshow['start_time']) < time()) { // 正在播放
                $talkshow['status'] = Talkshow::STATUS_PLAY;
            } elseif (strtotime($talkshow['start_time']) - 600 < time()) { // 即将开始
                $talkshow['status'] = Talkshow::STATUS_GOING;
            } else { // 跑马灯预告
                $talkshow['status'] = Talkshow::STATUS_PREPARE;
            }
        } else {
            if (strtotime($talkshow['start_time']) - 600 < time() && strtotime($talkshow['start_time']) > time() && $talkshow['status'] < Talkshow::STATUS_PLAY) { // 即将开始
                $talkshow['status'] = Talkshow::STATUS_GOING;
            } elseif (strtotime($talkshow['start_time']) < time() && $talkshow['status'] < Talkshow::STATUS_PLAY) {
                $talkshow['status'] = Talkshow::STATUS_GOING;
            } elseif (strtotime($talkshow['start_time']) - 600 > time()) {
                $talkshow['status'] = Talkshow::STATUS_PREPARE;
            }
        }

        return $talkshow;
    }

    public function getLastTalkshow()
    {
        $talkshow = Talkshow::orderBy('end_time', 'desc')
            ->get()
            ->filter(function ($item, $key) {
                if (strtotime($item->end_time) < time() && $item->status > Talkshow::STATUS_PLAY && !empty($item->play_url)) {
                    return true;
                }
            })->first();

        if (empty($talkshow)) {
            return [];
        }

        try {
            $teacher = Teacher::findOrFail($talkshow->teacher_id);
            $user = User::findOrFail($teacher->user_id);
        } catch (ModelNotFoundException $e) {
            throw new MatrixException("找不到这老师：{$talkshow->teacher_id}", COLUMN_TEACHER_NOT_FOUND);
        }

        try {
            $videoVendor = VideoVendor::where('code', $talkshow->video_vendor_code)->take(1)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new MatrixException("您要的这个节目预告视频供应商不存在: {$talkshow->video_vendor_code}", VIDEO_VENDOR_NOT_FOUND);
        }

        try {
            $category = Category::where('code', $teacher->category_code)->take(1)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new MatrixException("您要的这个节目预告分类不存在: {$teacher->category_code}", COLUMN_CATEGORY_NOT_FOUND);
        }

        $talkshow = $talkshow->toArray();

        $talkshow['teacher_user_name'] = $user->name;

        $talkshow['source_url'] = $talkshow['play_url'];

        try {
            $video = VideoSignin::where('url', $talkshow['play_url'])->take(1)->firstOrFail();
            $talkshow['play_url'] = sprintf('%s%s', config('video.video.url'), $video->video_key);
            $talkshow['source_url'] = $talkshow['play_url'];
        } catch (ModelNotFoundException $e) {
            unset($talkshow['play_url']);
        }
        $vodid = $talkshow['code'];

        $talkshow['sdk_video_vodid'] = $vodid;
        $talkshow['sdk_video_domain'] = $videoVendor->domain;
        $talkshow['sdk_video_vendor'] = $videoVendor->code;

        $talkshow['category_code'] = $category->code;
        $talkshow['category_name'] = $category->name;

        $talkshow['allow_discuss'] = 1;

        return $talkshow;
    }

    public function getLiveDiscussListApp(int $lastDiscussId, int $pageSize, array $credentials, string $myOpenId = '')
    {
        $cond = [];
        $orCond = [];

        foreach ($credentials as $k => $v) {
            $cond[] = [$k, '=', $v];
            if ($k != 'status') {
                $orCond[] = [$k, '=', $v];
            }
        }

        if (!empty($lastDiscussId)) {
            $cond[] = ['id', '>', $lastDiscussId];
            $orCond[] = ['id', '>', $lastDiscussId];
        }
        $discussList = Discuss::where($cond);
        if (!empty($myOpenId)) {
            if (array_key_exists('status', $orCond)) {
                unset($orCond['status']);
            }
            $orCond[] = ['open_id', '=', $myOpenId];
            $discussList = $discussList->orWhere($orCond);
        }
        $discussList = $discussList->orderBy('id', 'asc')->take($pageSize)->get();

        $customerOpenIdList = $discussList->pluck('open_id')->toArray();
        if (!empty($customerOpenIdList)) {
            $discussList->toArray();
            $customerList = Customer::whereIn('open_id', $customerOpenIdList)->get()->toArray();
            $qyUseridList = array_column($customerList, 'qy_userid');
            $ucList = Ucenter::whereIn('enterprise_userid', $qyUseridList)->get();
            $ucList = $ucList->toArray();
            $ucList = array_column($ucList, 'user_id', 'enterprise_userid');
            $teacherUserIdList = UserGroup::where('code', 'teacher_stock_a')->get()->pluck('user_id')->toArray();
            $customerList = array_column($customerList, NULL, 'open_id');

            $assistantListData = Grant::where('permission_code', 'discuss')->where('active', 1)->get()->toArray();
            $assistantUserIdList = array_column($assistantListData, 'user_id');

            foreach ($discussList as &$discuss) {
                $discuss['qy_userid'] = (string)array_get(array_get($customerList, $discuss['open_id']), 'qy_userid');
                $discuss['send_time'] = date('H:i', strtotime($discuss['created_at']));
                $currentUserId = (int)array_get($ucList, $discuss['qy_userid']);
                $discuss['is_teacher'] = (int)in_array($currentUserId, $teacherUserIdList);
                if (empty($discuss['is_teacher']) && in_array($currentUserId, $assistantUserIdList)) {
                    $discuss['customer_name'] = '老师助理';
                }
            }
        }

        return $discussList;
    }

    public function getTalkshowByTime(string $startTime, string $endTime)
    {
        $cond = [
            ['start_time', '<', $startTime],
            ['end_time', '>', $startTime],
        ];
        $orCond = [
            ['start_time', '<', $endTime],
            ['end_time', '>', $endTime],
        ];
        try {
            $talkshow = Talkshow::where($cond)->orWhere($orCond)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new VideoException("我没找着这个节目.", TALKSHOW_NOT_FOUND);
        }

        return $talkshow;
    }

    public function operateTalkshow(string $talkshowCode, int $operate)
    {
        try {
            $talkshow = Talkshow::where('code', $talkshowCode)->firstOrFail();

            if ($operate == Talkshow::STATUS_DONE) {
                if (($talkshow['type'] != 'play' && $talkshow->status != Talkshow::STATUS_PLAY) || ($talkshow['type'] == 'play' && time() < strtotime($talkshow['start_time']))) {
                    throw new VideoException("当前状态不能够结束.", TALKSHOW_NOT_FOUND);
                }
            }

            $talkshow->status = $operate;
            $talkshow->save();
        } catch (ModelNotFoundException $e) {
            throw new VideoException("我没找着这个节目.", TALKSHOW_NOT_FOUND);
        }

        return $talkshow;
    }

    /**
    *获取直播互动评论信息
    *
    *@param discussId integer 直播互动评论信息id
    *
    *@return array
    */
    public function getDiscussInfo(int $discussId)
    {
        try {
            $discussInfo = Discuss::where('id', $discussId)->firstOrFail();

            $discussInfo = $discussInfo->toArray();
        } catch (ModelNotFoundException $e) {
            throw new MatrixException("直播互动评论{$discussId}没有找到.", DISCUSS_NOT_FOUND);
        }

        return $discussInfo;
    }
}
