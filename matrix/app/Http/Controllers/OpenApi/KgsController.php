<?php

namespace Matrix\Http\Controllers\OpenApi;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Matrix\Http\Controllers\Controller;
use Matrix\Contracts\UserManager;
use Matrix\Contracts\UcManager;
use Matrix\Contracts\TwitterManager;
use Matrix\Contracts\CategoryManager;
use Matrix\Contracts\TeacherManager;
use Exception;
use Log;

class KgsController extends Controller
{
    private $request;
    private $ucenter;
    private $twitter; 
    private $teacher;

    public function __construct(Request $request, UcManager $ucenter, TwitterManager $twitter, TeacherManager $teacher)
    {
        $this->request = $request;
        $this->ucenter = $ucenter;
        $this->twitter = $twitter;
        $this->teacher = $teacher;
    }

    protected function fitDetailUrl(string $url)
    {
        if (empty($url)) {
            return '';
        }
        if (strpos($url, 'http') === 0) { // http or https
            return $url;
        } elseif (strpos($url, '//') === 0) { // //www.zhongyingtougu.com/
            return $this->request->server('REQUEST_SCHEME').":$url";
        } elseif (strpos($url, '/files/') === 0) { // //www.zhongyingtougu.com/
            return substr_replace($url, config('cdn.cdn_url'), 0, 6);
        } else {
            return sprintf('%s%s', config('app.h5_api_url'), $url);
        }
    }

    /*
    *创建看高手消息
    *@param room_id 看高手房间号
    *@param content 看高手内容文字部分
    *@param image_url 看高手内容图片部分
    *@param sourceId 靠高手Id
    *@return array
    */
    public function createKgs(UserManager $userManager)
    {
        try{
            //如果缺少必传字段，postman会报302
            $reqData = $this->request->validate([
                'room_id' => 'required|string',
                'content' => 'string|nullable',
                'image_url' => 'array|nullable',
                'image_url.*' => 'url',
                'sourceId' => 'required|string',
                'addTime' => 'nullable',
            ]);

            $roomId = array_get($reqData, 'room_id');

            $categoryCode = config('kgs.room_id.'.$roomId);

            $sessionId = $this->request->header('X-SessionId');

            $currentUserInfo = $this->ucenter->getUserInfoBySessionId($sessionId);

            $qyUserId = (string)array_get($currentUserInfo, 'data.user.qyUserId');
            /**
             * UcService中 通过sessionId获取UC信息匿名用户也会返回成功。合并获取用户信息与权限访问后，匿名用户会增加默认权限集合
             * 看高手发言老师现在都必须有企业微信帐号，没有企业微信帐号，默认为匿名用户
             */
            if(empty($qyUserId)){
                $ret = [
                    'code' => USER_X_SESSIONID_NOT_VALIDATE,
                    'msg' => '用户x-sessionid不合法',
                ];
                return $ret;
            }

            $userInfo = $userManager->getUserByEnterpriseUserId($qyUserId);

            if(0 != array_get($userInfo, 'code')){
                $ret = [
                    'code' => USER_NOT_FOUND,
                    'msg' => '没有查询到用户信息',
                ];

                return $ret;
            }


            $userId = array_get($userInfo, 'data.id');

            $teacherInfo = $this->teacher->getTeacherInfoByUserIdAndCategoryCode($userId, $categoryCode);

            $teacherId = array_get($teacherInfo, 'id');//teacherId 是teachr表的id 不是teacher的user_id

            $twitterInfoData = [
                'content' => (string)array_get($reqData,'content'),
                'room_id' => array_get($reqData, 'room_id'),
                'image_url' => (array)array_get($reqData, 'image_url', []),
                'source_id' => array_get($reqData, 'sourceId'),
                'operator_user_id' => array_get($userInfo, 'data.id'),
                'category_code' => $categoryCode,
                'teacher_id' => $teacherId,
                'feed' => 0,
            ];

            if (!empty(array_get($reqData, 'addTime'))) {
                $twitterInfoData['created_at'] = (int)array_get($reqData, 'addTime') / 1000;
            }

            $twitterInfoByKgs = $this->twitter->createrTwitterByKgsRequest($twitterInfoData);

            $twitterInfo = array_get($twitterInfoByKgs, 'data.twitter');

            $ret = [//接口返回状态吗是否与管理端内不一样，需要注意
                'code' => SYS_STATUS_OK,
                'data' => [
                    'twitter' => $twitterInfo,
                ],
            ];
        }catch ( ValidationException $e) {
            Log::error($e->errors(), [$e]);

            $ret = [
                'code' => OPEN_API_PARAMS_ERROR,
                'msg' => $e->errors(),
            ];
        }catch (ModelNotFoundException $ex) {
            Log::error($ex->getMessage(), [$ex]);
            $ret = [
                'code' => OPEN_API_PRIMARY_TEACHER_NOT_FOUND,
                'msg' => '该用户并未对应看高手老师',
            ];

        }catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => '系统未知错误',
            ];
        }

        return $ret;
    }

    /**
    *看高手消息单条读取接口
    *@param string $kgsId 靠高手消息记录
    */
    public function getKgsInfo(UserManager $userManager)
    {
        try{
            $reqData = $this->request->validate([
                'sourceId' => 'required|string',
            ]);

            $kgsId = array_get($reqData, 'sourceId');

            $sessionId = $this->request->header('X-SessionId');

            $currentUserInfo = $this->ucenter->getUserInfoBySessionId($sessionId);

            if(empty(array_get($currentUserInfo, 'data'))){
                $ret = [
                    'code' => USER_X_SESSIONID_NOT_VALIDATE,
                    'data' => '用户x-sessionid不合法',
                ];

                return $ret;
            }

            $currentOpenId = (string)array_get($currentUserInfo, 'data.user.openId');

            $qyUserId = (string)array_get($currentUserInfo, 'data.user.qyUserId');

            $twitterInfo = $this->twitter->getTwitterInfoBySourceId($kgsId);

            if(empty($twitterInfo)){
                $ret = [
                    'code' => OPEN_API_TWITTER_INFO_NOT_FOUND,
                    'msg' => '没有查询到信息记录',
                ];

                return $ret;
            }

            $teacherInfo = $userManager->getUserByEnterpriseUserId($qyUserId);

            if(empty($teacherInfo)){
                $ret = [
                    'code' => USER_NOT_FOUND,
                    'data' => '没有查询到用户信息',
                ];

                return $ret;
            }

            $twitterId = array_get($twitterInfo, 'id');

            $isLike = $this->twitter->getLikeOfTwitter($twitterId, $currentOpenId, '');

            $likeSum = $this->twitter->getLikeSumOfTwitter($twitterId, 'twitter', '');

            $resp[] = [
                'content' => empty($twitterInfo['image_url']) ? $twitterInfo['content'] : $twitterInfo['image_url'],
                'userId' => $qyUserId,
                'avatar' => array_get($teacherInfo, 'data.icon_url'),//to do 做一下处理
                'nickname' => array_get($teacherInfo, 'data.name'),
                'roomId' => $twitterInfo['room_id'],
                'timestamp' => $twitterInfo['created_at'],
                'type' => empty($twitterInfo['image_url']) ? 'TextMessage' : 'ImageMessage',
                'tips' => empty($twitterInfo['image_url']) ? '' : $twitterInfo['content'],
                'sourceId' => $twitterInfo['source_id'],
                'isLike' => array_get($isLike, 'data.like'),
                'likeSum' => empty(array_get($likeSum, 'data.statisticInfo.like_sum')) ? 0 : array_get($likeSum, 'data.statisticInfo.like_sum'),
            ];

            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => $resp,
            ];
        }catch ( ValidationException $e) {
            Log::error($e->errors(), [$e]);
            $ret = [
                'code' => OPEN_API_PARAMS_ERROR,
                'msg' => $e->errors(),
            ];

            return $ret;
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => '系统未知错误',
            ];
        }

        return $ret;
    }

    /**
    *看高手消息列表按房间读取
    *@param string $room_id 房间号
    *@param string $start_time 房间号
    *@param string $end_time 房间号
    *@return array
    */
    public function getKgsList(UserManager $userManager, CategoryManager $categoryManager, TwitterManager $twitterManager)
    {
        try{
            try{
                $reqData = $this->request->validate([
                    'room_id' => 'required|string',
                    'start_time' => 'string|nullable',
                    'end_time' => 'string|nullable',
                    'has_refer_content' => 'integer', // 是否返回携带引用的解盘内容，0 = 不携带，1 = 携带，默认为携带
                ]);

            }catch ( ValidationException $e) {
                Log::error($e->errors(), [$e]);
                $ret = [
                    'code' => OPEN_API_PARAMS_ERROR,
                    'msg' => $e->errors(),
                ];

                return $ret;
            }

            $startTime = empty(array_get($reqData, 'start_time')) ? '' : array_get($reqData, 'start_time');

            $endTime = empty(array_get($reqData, 'end_time')) ? '' : array_get($reqData, 'end_time');

            $sessionId = $this->request->header('X-SessionId');

            $currentUserInfo = $this->ucenter->getUserInfoBySessionId($sessionId);

            if(empty(array_get($currentUserInfo, 'data'))){
                $ret = [
                    'code' => USER_X_SESSIONID_NOT_VALIDATE,
                    'data' => '用户x-sessionid不合法',
                ];

                return $ret;
            }

            $openId = (string)array_get($currentUserInfo, 'data.user.openId');

            if ($this->request->has('has_refer_content')) {
                $hasReferContent = (bool)array_get($reqData, 'has_refer_content');
            } else {
                $hasReferContent = true;
            }

            $twitterList = $this->twitter->getPageTwitterListByRoomId($reqData['room_id'], $startTime, $endTime, $hasReferContent);

            $twitterList = array_get($twitterList, 'data.twitter_list');

            $teacherIdList = array_column($twitterList, 'teacher_id');

            $teacherList = $this->twitter->getTeacherListByIdList($teacherIdList);

            $teacherList = array_get($teacherList, 'data.teacher_list');

            $teacherList = array_column($teacherList, NULL, 'id');

            foreach( $twitterList as &$twitter){
                if(!empty($twitter['teacher_id'])){//将twitter表中teacher_id为0的客户过滤掉
                    $twitter['enterprise_userid'] =  $teacherList[$twitter['teacher_id']]['enterprise_userid'];

                    $twitter['icon_url'] =  $this->fitDetailUrl(array_get($teacherList[$twitter['teacher_id']], 'icon_url'));

                    $twitter['name'] =  $teacherList[$twitter['teacher_id']]['name'];
                }
            }

            foreach( $twitterList as &$twitter){
                if(!empty($twitter['teacher_id'])){//将twitter表中teacher_id为0的客户过滤掉
                    $likeMsg = $this->twitter->getLikeOfTwitter($twitter['id'], $openId, '');

                    $twitter['is_like'] =  array_get($likeMsg, 'data.like');
                }
            }

            foreach( $twitterList as &$twitter){
                if(!empty($twitter['teacher_id'])){//将twitter表中teacher_id为0的客户过滤掉
                    $likeSum = $this->twitter->getLikeSumOfTwitter($twitter['id'], 'twitter', '');

                    $twitter['like_sum'] =  empty(array_get($likeSum, 'data.statisticInfo')) ? 0 : array_get($likeSum, 'data.statisticInfo');
                }
            }

            foreach( $twitterList as &$twitterInfo){
                if(!empty($twitterInfo['teacher_id'])){//将twitter表中teacher_id为0的客户过滤掉
                    $resp[] = [
                        'content' => empty($twitterInfo['image_url']) ? $twitterInfo['content'] : $twitterInfo['image_url'],
                        'userId' => $twitterInfo['enterprise_userid'],
                        'avatar' => array_get($twitterInfo, 'icon_url'),//to do 做一下处理
                        'nickname' => array_get($twitterInfo, 'name'),
                        'roomId' => $twitterInfo['room_id'],
                        'timestamp' => $twitterInfo['created_at'],
                        'type' => empty($twitterInfo['image_url']) ? 'TextMessage' : 'ImageMessage',
                        'tips' => empty($twitterInfo['image_url']) ? '' : $twitterInfo['content'],
                        'sourceId' => $twitterInfo['source_id'],
                        'isLike' => array_get($twitterInfo, 'is_like'),
                        'likeSum' => empty(array_get($twitterInfo, 'like_sum')) ? 0 : array_get($twitterInfo, 'like_sum'),
                    ];
                }
            }

            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => $resp,
            ];

        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => '系统未知错误',
            ];
        }

        return $ret;
    }

    /**
    *靠高手点赞接口
    *@param string kgs_id 看高手记录id
    *@param string udid 内容点赞识别安装app字端
    *@return array
    */
    public function like(UserManager $userManager)
    {
        try {
            try{
                $reqData = $this->request->validate([
                    'sourceId' => 'required|string',
                    'udid' => 'string|nullable',
                ]);

            }catch ( ValidationException $e) {
                Log::error($e->errors(), [$e]);
                $ret = [
                    'code' => OPEN_API_PARAMS_ERROR,
                    'msg' => $e->errors(),
                ];

                return $ret;
            }

            $sessionId = $this->request->header('X-SessionId');

            $udid = array_get($reqData, 'udid');

            $currentUserInfo = $this->ucenter->getUserInfoBySessionId($sessionId);

            if(empty(array_get($currentUserInfo, 'data'))){
                $ret = [
                    'code' => USER_X_SESSIONID_NOT_VALIDATE,
                    'msg' => '用户x-sessionid不合法',
                ];

                return $ret;
            }

            $user = array_get($currentUserInfo, 'data.user');
            if (empty($user)) {
                $ret = [
                    'code' => USER_X_SESSIONID_INVALID,
                    'msg' => '用户X-SessionId失效了',
                ];

                return $ret;
            }

            $userType = array_get($currentUserInfo, 'data.user.roleCode');

            $openId = (string)array_get($currentUserInfo, 'data.user.openId');

            $qyUserId = (string)array_get($currentUserInfo, 'data.user.qyUserId');

            $twitterInfo = $this->twitter->getTwitterInfoBySourceId(array_get($reqData, 'sourceId'));

            if(empty($twitterInfo)){
                $ret = [
                    'code' => OPEN_API_TWITTER_INFO_NOT_FOUND,
                    'msg' => '没有查询到信息记录',
                ];

                return $ret;
            }

            $twitterId = array_get($twitterInfo, 'id');

            $twitterData = $this->twitter->likeTwitter($twitterId, $openId, $udid, $sessionId, $userType);

            $likeStatus = array_get($twitterData, 'data.like');

            $ret = []; 

            if($likeStatus == 2){
                $ret['code'] = SYS_STATUS_ERROR_UNKNOW;

                $ret['msg'] = '系统未知错误';
            }else{
                $likeStatistic = $this->twitter->likeStatistic($twitterId, 'twitter', $userType, $likeStatus);

                $teacherInfo = $userManager->getUserByEnterpriseUserId($qyUserId);

                if(empty($teacherInfo)){
                    $ret = [
                        'code' => USER_NOT_FOUND,
                        'msg' => '没有查询到老师信息',
                    ];

                    return $ret;
                }

                $twitterId = array_get($twitterInfo, 'id');

                $isLike = $this->twitter->getLikeOfTwitter($twitterId, $openId, '');

                $likeSum = $this->twitter->getLikeSumOfTwitter($twitterId, 'twitter', '');//need to modify

                $resp[] = [
                    'content' => empty($twitterInfo['content']) ? '' : $twitterInfo['content'],
                    'imageUrl' => empty($twitterInfo['image_url']) ? [] : $twitterInfo['image_url'],
                    'userId' => $qyUserId,
                    'avatar' => array_get($teacherInfo, 'data.icon_url'),//to do 做一下处理
                    'nickname' => array_get($teacherInfo, 'data.name'),
                    'roomId' => $twitterInfo['room_id'],
                    'timestamp' => $twitterInfo['created_at'],
                    'type' => empty($twitterInfo['image_url']) ? 'TextMessage' : 'ImageMessage',
                    'tips' => empty($twitterInfo['image_url']) ? '' : $twitterInfo['content'],
                    'sourceId' => $twitterInfo['source_id'],
                    'isLike' => array_get($isLike, 'data.like'),
                    'likeSum' => empty(array_get($likeSum, 'data.statisticInfo.like_sum')) ? 0 : array_get($likeSum, 'data.statisticInfo.like_sum'),
                ];

                $ret = [
                    'code' => SYS_STATUS_OK,
                    'data' => $resp,
                ];
            }
                                                                                                                                        
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => '系统未知错误',
            ];
        }

        return $ret;
    }
}
