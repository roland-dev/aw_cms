<?php

namespace Matrix\Http\Controllers\Client;

use Illuminate\Http\Request;
use Matrix\Contracts\UcManager;
use Matrix\Contracts\UserManager;
use Matrix\Contracts\VideoManager;
use Matrix\Contracts\CourseManager;
use Matrix\Contracts\CategoryManager;
use Matrix\Contracts\CourseVideoManager;
use Matrix\Contracts\CourseSystemManager;
use Matrix\Contracts\TalkshowContract;
use Matrix\Contracts\InteractionContract;
use Matrix\Contracts\ContentGuardContract;
use Illuminate\Validation\ValidationException;

use Matrix\Models\Talkshow;

use Log;
use Exception;
use Matrix\Exceptions\UcException;
use Matrix\Exceptions\UserException;
use Matrix\Exceptions\MatrixException;
use Matrix\Exceptions\TalkshowException;
use Matrix\Exceptions\PermissionException;
use Matrix\Models\UserGroup;
use Matrix\Contracts\UserGroupManager;

use Matrix\Models\ArticleReply;
use Matrix\Contracts\CustomerManager;

class TalkshowController extends Controller
{
    const XUEZHANFA_COURSE_CODE = 'xuezhanfa_course';

    const DIR = 'image';
    const TENCENT_URL = 'v.qq.com';
    const GENSEE = 'fhcj.gensee.com';
    const VHALL = 'live.vhall.com';
    const CDN_URI = '/files/cms/storage/';
    const INIT = 0;
    const IFRAME = 'iframe/player';
    const TALK_SHOW_TYPE = 'talkshow';

    const USER_GROUP_CODE_SUPERMAN_TAG = 'teacher_superman_tag';

    protected $request;
    protected $ucenter;
    protected $talkshow;
    protected $userGroup;
    protected $videoManager;
    protected $categoryManager;
    protected $interactionContract;
    protected $customer;

    public function __construct(
                                   Request $request,
                                   UcManager $ucenter,
                                   VideoManager $videoManager,
                                   UserManager $userManager,
                                   CategoryManager $categoryManager,
                                   UserGroupManager $userGroup,
                                   TalkshowContract $talkshow,
                                   InteractionContract $interactionContract,
                                   CustomerManager $customer
                               )
    {
        $this->request = $request;
        $this->ucenter = $ucenter;
        $this->talkshow = $talkshow;
        $this->videoManager = $videoManager;
        $this->userManager = $userManager;
        $this->categoryManager = $categoryManager;
        $this->userGroup = $userGroup;
        $this->interactionContract = $interactionContract;
        $this->customer = $customer;
    }

    /**
    *获取大盘分析视频详情
    *@param $videokey string
    *@param $categoryCode string
    *@return array
    **/
    public function getVideoDetail(ContentGuardContract $contentGuardService, CategoryManager $category, $videoKey)
    {
        $udid = '';
        $sessionId = '';
        $currentOpenId = '';
        $isTeacher = 0;
        $serviceCodeList = ['basic', 'dp2'];
        try {
            /**
            *
            * Get the X-SessionId from Http Headers and get yr service code list from uc by X-SessionId.
            *
            * If no X-SessionId in Http Headers, then yr service code list always ['basic'].
            *
            */
            $loginUrl = $this->h5WechatAutoLogin($this->request, $this->ucenter);

            if(!empty($loginUrl)){
                return redirect()->away($loginUrl);
            }

            $sessionId = $this->request->cookie('X-SessionId');

            if (!empty($sessionId)) {
                $sessionIdExpired = time() + 60 * 60 * 10;
                setcookie('X-SessionId', $sessionId, $sessionIdExpired, config('session.path'), config('session.domain'), false, true);
                $currentUserInfo = $this->ucenter->getUserInfoBySessionId($sessionId);
                $currentOpenId = (string)array_get($currentUserInfo, 'data.user.openId');

                $enterpriseUserId = array_get($currentUserInfo, 'data.user.qyUserId');

                $userMobile = array_get($currentUserInfo, 'data.user.mobile');

                if (!empty($enterpriseUserId)) {
                    $teacherUserData = $this->userManager->getUserByEnterpriseUserId($enterpriseUserId);

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
                }
            }

            $customerName = isset($currentUserInfo) ? (string)array_get($currentUserInfo, 'data.user.name') : '';//获取客户nickname

        } catch (UcException $e) {
            Log::error($e->getMessage(), [$e]);
        } catch (UserException $e) {
            Log::error($e->getMessage(), [$e]);
        } catch (MatrixException $e) {
            Log::error($e->getMessage(), [$e]);
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);

            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => $e->getMessage(),
            ];
            abort(500, '发生了一个不可预知的错误');
        }

	    try {
	        if (!empty($currentOpenId)) {
                $serviceCodeList = $this->ucenter->getAccessCodeByOpenId($currentOpenId);
	        }
        } catch (MatrixException $e) {
            Log::error($e->getMessage(), [$e]);
            $serviceCodeList = ['basic', 'dp2'];
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);

            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => $e->getMessage(),
            ];
            abort(500, '发生了一个不可预知的错误');
	    }

        try {
            $videoSigninInfo = $this->videoManager->getVideoSigninInfo($videoKey);

            //可以预留出一个categoryCode的判断
            if(empty(array_get($videoSigninInfo, 'data'))){
                throw new TalkshowException('没有找到对应视频信息', VIDEO_URL_NOT_EXISTS);
            }

            $categoryCode = array_get($videoSigninInfo, 'data.category_code');

            $categoryInfo = $category->getCategoryInfoByCode($categoryCode);

            if(!in_array($categoryInfo['service_key'], $serviceCodeList)){
                throw new PermissionException ('无权查看视频信息', VIDEO_URL_NO_PERMISSION);
            }

            // $videoSigninId = array_get($videoSigninInfo, 'data.id', '');

            $authorId = array_get($videoSigninInfo, 'data.author', '');

            $categoryCode = array_get($videoSigninInfo, 'data.category_code', '');

            $authorInfo = $this->userManager->getUserInfo($authorId);

            $categoryInfo = $this->categoryManager->getCategoryInfoByCode($categoryCode);

            $videoSigninInfo = array_get($videoSigninInfo, 'data');;

            $isLike = $this->interactionContract->getLikeRecord($videoKey, self::TALK_SHOW_TYPE, $currentOpenId, $udid);

            $likeSum = $this->interactionContract->getLikeSum($videoKey, self::TALK_SHOW_TYPE);
        } catch (PermissionException $e) {
            Log::error($e->getMessage(), [$e]);

            $ret = [
                'code' => $e->getCode(),
                'msg' => $e->getMessage(),
            ];
            abort(403, $e->getMessage());
        } catch (MatrixException $e) {
            Log::error($e->getMessage(), [$e]);

            $ret = [
                'code' => $e->getCode(),
                'msg' => $e->getMessage(),
            ];
            abort(404, $e->getMessage());
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);

            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => $e->getMessage(),
            ];
            abort(404, '发生了一个不可预知的错误');
        }

        try {
            $video = [
                'detail_id' => $videoSigninInfo['video_key'],
                'content_id' => $videoSigninInfo['video_key'],
                'column_name' => '大盘分析',
                'category_key' => $videoSigninInfo['category_code'],
                'category_name' => array_get($categoryInfo, 'name'),
                'title' => $videoSigninInfo['title'],
                'summary' => $videoSigninInfo['description'],
                'media_type' => 'video',
                'source_url' => $videoSigninInfo['url'],
                'thumb_cdn_url' => '',
                'thumb_local_url' => '',
                'access_level' => array_get($categoryInfo, 'service_key'),
                'access_deny' => 0,
                //'add_time' => date("m月d日 H:i", strtotime($videoSigninInfo['created_at'])),
                'add_time' => date("m月d日", strtotime($videoSigninInfo['published_at'])),
                'tag' => '',
                'jump_type' => 'common_web',
                'guide_media' => 'page',
                'owner_name' => array_get($authorInfo, 'userInfo.name'),
                'owner_avatar' => array_get($authorInfo, 'userInfo.icon_url'),
                'guide_msg' => '',
                'demo_url' => '',
                'record_id' => $videoSigninInfo['id'],
                'session_id' => empty($sessionId) ? '' : $sessionId,
                'is_forward_teacher' => $isTeacher,
                'author_id' => array_get($authorInfo, 'userInfo.id'),
                'forward_teacher_id' => empty($isTeacher) ? 0 : $teacherUserId,
                'is_reply' => isset($userMobile) && !empty($userMobile) ? 1 : 0,
                'forward_open_id' => empty($isTeacher) ? 0 : $currentOpenId,
                'is_like' => array_get($isLike, 'data.like'),
                'like_sum' => empty(array_get($likeSum, 'data.statisticInfo.like_sum')) ? 0 : array_get($likeSum, 'data.statisticInfo.like_sum') > 999 ? '999+' : array_get($likeSum, 'data.statisticInfo.like_sum'),
            ];

            if(strpos($videoSigninInfo['url'], self::TENCENT_URL) !== false){//是腾讯视频
                $video['video_type'] = 0; //0:是腾讯视频
                $video['original_key'] = '';
            }elseif(strpos($videoSigninInfo['url'], self::GENSEE)){
                $video['video_type'] = 1; //1:是展示互动
                $pos = strpos($videoSigninInfo['url'], '-');
                $substr = substr($videoSigninInfo['url'], $pos + 1);
                $video['original_key'] = $substr;
            } elseif (strpos($videoSigninInfo['url'], self::VHALL)) {
                $video['video_type'] = 2; // 2：是微吼视频
                $videoUrlArr = explode('/', $videoSigninInfo['url']);
                if (strpos($videoUrlArr[count($videoUrlArr) - 1], '?') !== false) {
                    $vodid = explode('?', $videoUrlArr[count($videoUrlArr) - 1])[0];
                } else {
                    $vodid = $videoUrlArr[count($videoUrlArr) - 1];
                }
                $video['original_key'] = $vodid;
            } else{
                $video['video_type'] = 3; //3: 是其他视频
                $video['original_key'] = '';
            }

            $video['nickname'] = $customerName;//用户nickname

            $video['open_id'] = $currentOpenId;//客户openID

            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'talkshow' => $video,
                    //'reply_list' => $replyList,
                ],
            ];
            return view('talkshow.detail', $ret);
        } catch (MatrixException $e) {
            Log::error($e->getMessage(), [$e]);

            $ret = [
                'code' => $e->getCode(),
                'msg' => $e->getMessage(),
            ];
            abort(404, $e->getMessage());
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);

            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => $e->getMessage(),
            ];
            abort(404, '发生了一个不可预知的错误');
        }
    }

    public function getPredictInfo()
    {
        try {
            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [],
            ];
            $predict = $this->talkshow->getPredictInfo();
            if (!empty($predict)) {
                $predict['talkshow_code'] = $predict['code'];
                $predict['start_time'] = strtotime($predict['start_time']);
                $predict['end_time'] = strtotime($predict['end_time']);
                $predict['summary'] = $predict['boardcast_content'];
                $predict['category_code'] = 'daily_talkshow';
                unset($predict['id']);
                unset($predict['code']);
                unset($predict['video_vendor_code']);
                unset($predict['teacher_id']);
                unset($predict['boardcast_content']);
                unset($predict['last_modify_user_id']);
                unset($predict['play_url']);
                unset($predict['created_at']);
                unset($predict['updated_at']);
                $ret['data']['talkshow'] = $predict;
                $ret['data']['status'] = (int)array_get($predict, 'status');
                unset($ret['data']['talkshow']['status']);
            } else {
                $ret['data']['status'] = Talkshow::STATUS_NEW;
                $ret['data']['talkshow'] = [];
            }
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
                'msg' => '发生了一个不可预知的错误',
            ];
        }

        return $ret;
    }

    public function getTodayTalkshowList()
    {
        $credentials = [
            'date' => date('Y-m-d'),
        ];

        $talkshowList = $this->talkshow->getTalkshowList(1, 1000, $credentials);
        if (!empty($talkshowList)) {
            $videoVendorList = $this->talkshow->getVideoVendorList(1, 1000, []);
            $videoVendorDomainList = array_column($videoVendorList, 'domain', 'code');
            foreach ($talkshowList as &$talkshow) {
                $talkshow['play_status'] = $talkshow['status'];
                $talkshow['talkshow_code'] = $talkshow['code'];
                $talkshow['teacher_name'] = $talkshow['teacher_user_name'];
                $talkshow['start_time'] = date('H:i', strtotime($talkshow['start_time']));
                $talkshow['end_time'] = date('H:i', strtotime($talkshow['end_time']));
                $talkshow['sdk_video_vodid'] = $talkshow['type'] == 'live' ? $talkshow['live_room_code'] : $talkshow['talkshow_code'];
                $talkshow['sdk_video_vendor'] = $talkshow['video_vendor_code'];
                $talkshow['sdk_video_domain'] = (string)array_get($videoVendorDomainList, $talkshow['video_vendor_code']);
                $talkshow['allow_discuss'] = (($talkshow['status'] == Talkshow::STATUS_PLAY) && ($talkshow['type'] == 'live'));
                $talkshow['category_code'] = 'daily_talkshow';
                unset($talkshow['status']);
                unset($talkshow['id']);
                unset($talkshow['code']);
                unset($talkshow['teacher_id']);
                unset($talkshow['teacher_user_id']);
                unset($talkshow['teacher_user_name']);
                unset($talkshow['last_modify_user_id']);
                unset($talkshow['last_modify_user_name']);
                unset($talkshow['created_at']);
                unset($talkshow['updated_at']);
            }
        }


        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'talkshow_list' => $talkshowList,
            ],
        ];

        return $ret;
    }

    // 直播详情页
    public function getLiveTalkshowInfo($talkshowCode)
    {
        try {
            $talkshow = $this->talkshow->getTalkshow($talkshowCode);
            
            $userInfoData = $this->userManager->getUserInfo((int)array_get($talkshow, 'teacher_user_id'));
            $talkshow['teacher_qy_userid'] = (string)array_get($userInfoData, 'ucInfo.enterprise_userid');
            $talkshow['teahcer_name'] = (string)array_get($userInfoData, 'userInfo.name');
            $talkshow['teacher_icon_url'] = (string)array_get($userInfoData, 'userInfo.icon_url');

            // 判断是否具有play_url
            $playUrl = array_get($talkshow, 'play_url');
            if (empty($playUrl)) {
                if (in_array($talkshow['video_vendor_code'], ['video_gensee'])) {
                    $videoVendor = $this->talkshow->getVideoVendor($talkshow['video_vendor_code']);
                    $talkshow['video_type'] = 1; // 1: 展示互动
    
                    if (!empty($talkshow['live_room_code'])) {
                        $talkshow['sdk_video_videoid'] = $talkshow['live_room_code'];
                    }
                } elseif (in_array($talkshow['video_vendor_code'], ['video_vhall'])) {
                    $videoVendor = $this->talkshow->getVideoVendor($talkshow['video_vendor_code']);
                    $talkshow['video_type'] = 2; // 2: 微吼视频
    
                    if (!empty($talkshow['live_room_code'])) {
                        $talkshow['sdk_video_videoid'] = $talkshow['live_room_code'];
                    }
                } else {
                    throw new MatrixException("该记录的直播类型数据当前不支持", VIDEO_VENDOR_NOT_FOUND);
                }
            } else {
                if (strpos($playUrl, self::GENSEE)) {
                    $videoVendor = $this->talkshow->getVideoVendor('video_gensee');
                    $talkshow['video_type'] = 1; // 1: 展示互动
                    $talkshow['sdk_video_videoid'] = $talkshow['code'];
                } elseif (strpos($playUrl, self::VHALL)) {
                    $videoVendor = $this->talkshow->getVideoVendor('video_vhall');
                    $talkshow['video_type'] = 2; // 2: 微吼视频
                    $talkshow['sdk_video_videoid'] = $talkshow['code'];
                }
            }

            if (!empty($videoVendor)) {
                $talkshow['sdk_video_domain'] = $videoVendor['domain'];
                $talkshow['sdk_video_vendor'] = $videoVendor['code'];
            }

            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'live_talkshow' => $talkshow
                ],
            ];
        } catch (MatrixException $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = [
                'code' => $e->getCode(),
                'msg' => $e->getMessage()
            ];
            return $ret;
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => '未知错误'
            ];
            return $ret;
        }

        // return $ret;
        return view('live.detail', $ret);
    }
}
