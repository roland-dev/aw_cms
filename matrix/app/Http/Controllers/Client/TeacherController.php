<?php

namespace Matrix\Http\Controllers\Client;

use Illuminate\Http\Request;
use Matrix\Contracts\UcManager;
use Matrix\Contracts\UserManager;
use Matrix\Contracts\TeacherManager;
use Matrix\Contracts\CustomerManager;
use Exception;
use Cache;
use Jenssegers\Agent\Agent;
use Log;
use Matrix\Contracts\CategoryManager;
use Matrix\Models\Kit;

class TeacherController extends Controller
{
    //
    private $request;
    private $agent;
    private $categoryManager;

    const USER_GROUP_STOCK_A = 'teacher_stock_a';
    const CATEGORY_GROUP_TALK_CODE = 'kgs_group_talk';

    public function __construct (Request $request, Agent $agent, CategoryManager $categoryManager)
    {
        $this->request = $request;
        $this->agent = $agent;
        $this->categoryManager = $categoryManager;
    }

    public function putFollow(UcManager $ucenter, UserManager $userManager, TeacherManager $teacherManager, CustomerManager $customerManager)
    {
        $operates = ['follow', 'unfollow'];
        $teacherUserid = $this->request->input('teacher_userid');
        $operate = $this->request->input('operate');
        if (empty($teacherUserid) || !in_array($operate, $operates)) abort(400);

        try {
            $teacherUserInfo = $userManager->getUserByEnterpriseUserId($teacherUserid);
            $code = array_get($teacherUserInfo, 'code');
            if ($code !== SYS_STATUS_OK) {
                $ret = ['code' => CLIENT_TEACHER_NOT];
                return $ret;
            }
            $teacherUserId = array_get($teacherUserInfo, 'data.id');

            $sessionId = $this->request->header('X-SessionId');
            $userInfo = $ucenter->getUserInfoBySessionId($sessionId);

            $openId = array_get($userInfo, 'data.user.openId');

            switch ($operate) {
                case 'follow':
                    $teacherManager->followTeacher($teacherUserId, $openId);
                    $ucUserInfo = $ucenter->getUserInfoBySessionId($sessionId);
                    $customerData = [
                        'open_id' => (string)array_get($ucUserInfo, 'data.user.openId'),
                        'code' => (string)array_get($ucUserInfo, 'data.user.customerCode'),
                        'name' => (string)array_get($ucUserInfo, 'data.user.name'),
                        'mobile' => (string)array_get($ucUserInfo, 'data.user.mobile'),
                        'icon_url' => (string)array_get($ucUserInfo, 'data.user.iconUrl'),
                        'qy_userid' => (string)array_get($ucUserInfo, 'data.user.qyUserId'),
                    ];
                    $customerInfo = $customerManager->updateCustomer($customerData);

                    break;
                case 'unfollow':
                    $teacherManager->unFollowTeacher($teacherUserId, $openId);
                    break;
            }
            $ret = ['code' => SYS_STATUS_OK];
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = ['code' => SYS_STATUS_ERROR_UNKNOW];
        }

        return $ret;

    }

    public function getTeacherInfo (UcManager $ucenter, UserManager $userManager, TeacherManager $teacherManager)
    {
        $ret = ['code' => SYS_STATUS_ERROR_UNKNOW];
        $userid = $this->request->input('teacher_userid');
        $sessionId = $this->request->header('X-SessionId');
        Log::debug("PureDebug: X-SessionId $sessionId");
        $userInfo = $ucenter->getUserInfoBySessionId($sessionId);
        Log::debug("PureDebug: ucUserInfo ".json_encode($userInfo));
        if (empty($userid)) {
            $userid = array_get($userInfo, 'data.user.qyUserId');
        }
        $openId = (string)array_get($userInfo, 'data.user.openId');
        Log::debug("PureDebug: OpenId $openId");

        $userInfoData = $userManager->getUserByEnterpriseUserId((string)$userid);
        Log::debug("PureDebug: userInfo ".json_encode($userInfoData));
        if ($userInfoData['code'] == USER_NOT_FOUND) {
            $ret['code'] = CLIENT_TEACHER_NOT;
        } elseif ($userInfoData['code'] == SYS_STATUS_OK) {
            if (array_get($userInfoData, 'data.type') != 'teacher') {
                $ret['code'] = CLIENT_TEACHER_NOT;
            } else {
                $ret['code'] = SYS_STATUS_OK;
                $tabList = $teacherManager->getTeacherTabList(array_get($userInfoData, 'data.id'));

                // 针对 APP 2.6.* 以及之前版本 去除 锦囊 tab
                $ua = strtolower($this->request->userAgent());
                if (!empty($ua) && strpos($ua, 'zytg') !== false) {
                    $appVersion = $this->getAPPVersion($ua);
                    if (version_compare($appVersion, '2.7.*') < 0) {
                        foreach ($tabList as $index => $tab) {
                            if ($tab['code'] === Kit::KIT_TAB_CODE) {
                                unset($tabList[$index]);
                            }
                        }
                    }
                }

                // 获取用户 accessCodeList
                $accessCodeList = $ucenter->getAccessCodeBySessionId($sessionId, 'default', true);

                $privateTalkServiceCode = $this->getPrivateTalkServiceCodeOfUserId((int)array_get($userInfoData, 'data.id'));

                if (count(array_intersect($accessCodeList, $privateTalkServiceCode)) > 0) {
                    $isPrivateTalk = 1;
                } else {
                    $isPrivateTalk = 0;
                }

                $ret['data'] = [];
                $ret['data']['teacher'] = [
                    'id' => array_get($userInfoData, 'data.id'),
                    'name' => array_get($userInfoData, 'data.name'),
                    'icon_url' => array_get($userInfoData, 'data.icon_url'),
                    'cert_no' => array_get($userInfoData, 'data.cert_no'),
                    'follow' => $teacherManager->getTeacherFollow(array_get($userInfoData, 'data.id'), $openId),
                    'follow_count' => $teacherManager->getTeacherFollowCount(array_get($userInfoData, 'data.id')),
                    'tab_list' => $tabList,
                    'is_pm' => (int)in_array('twitter', array_column($tabList, 'code')),
                    'is_private_talk' => $isPrivateTalk,
                    'private_talk_service_code' => $privateTalkServiceCode,
                    'description' => array_get($userInfoData, 'data.description'),
                ];
            }

        }

        return $ret;
    }

    private function getPrivateTalkServiceCodeOfUserId(int $userId)
    {
        $result = [];

        // 获取当前所有群聊栏目
        $categoryListData = $this->categoryManager->getCategoryListByGroupCode(self::CATEGORY_GROUP_TALK_CODE);
        $categoryOfTalk = (array)array_get($categoryListData, 'data.category_list');

        foreach ($categoryOfTalk as $category) {
            $categoryCode = array_get($category, 'code');
            $teacherListData = $this->categoryManager->getTeacherListByCategoryCode($categoryCode);
            $teacherList = array_get($teacherListData, 'data.teacher_list');
            $userIdListOfTeacherList = array_column($teacherList, 'user_id');
            if (in_array($userId, $userIdListOfTeacherList)) {
                $serviceCode = array_get($category, 'service_key');
                array_push($result, $serviceCode);
            }
        }

        return $result;
    }

    public function getTeacherList(UserManager $userManager, TeacherManager $teacherManager, string $userGroupCode, UcManager $ucenter)
    {
        $follow = $this->request->input('follow');
        if ($follow === NULL) {
            abort(400);
        }

        $ret = [];
        try {
            $userList = $userManager->getUserListByGroupCode($userGroupCode);
            $userIdList = array_column($userList, 'id');

            $sessionId = $this->request->header('X-SessionId');
            $userInfo = $ucenter->getUserInfoBySessionId($sessionId);
            $openId = (string)array_get($userInfo, 'data.user.openId');

            $followCountList = $teacherManager->getFollowCountList();
            $followUserIdList = empty($openId) ? [] : $teacherManager->getFollowListByOpenId($openId);

            $ucList = $userManager->getUcListByUserIdList($userIdList);
            $ucList = array_column($ucList, NULL, 'user_id');


            $retUserList = [];
            foreach ($userList as $user) {
                $retUserList[] = [
                    'id' => array_get($user, 'id'),
                    'name' => array_get($user, 'name'),
                    'qy_userid' => array_key_exists(array_get($user, 'id'), $ucList) ? $ucList[array_get($user, 'id')]['enterprise_userid'] : '',
                    'icon_url' => array_get($user, 'icon_url'),
                    'description' => (string)array_get($user, 'description'),
                    'cert_no' => (string)array_get($user, 'cert_no'),
                    'follow' => (int)in_array($user['id'], $followUserIdList),
                    'follow_count' => (int)array_get($followCountList, $user['id']),
                    'type' => 'teacher',
                ];
            }
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = ['code' => SYS_STATUS_ERROR_UNKNOW];
            return $ret;
        }

        $ret['code'] = SYS_STATUS_OK;
        $ret['data']['user_list'] = [];

        if ($follow === '-1') {
            $ret['data']['user_list'] = $retUserList;
        } else {
            foreach ($retUserList as $retUser) {
                if ($follow == $retUser['follow']) {
                    $ret['data']['user_list'][] = $retUser;
                }
            }
        }

        return $ret;
    }

    public function getUserFollowCount(UcManager $ucenter, TeacherManager $teacherManager)
    {
        $sessionId = $this->request->header('X-SessionId');
        $userInfo = $ucenter->getUserInfoBySessionId($sessionId);
        $openId = array_get($userInfo, 'data.user.openId');

        try {
            if (!empty($openId)) {
                $followList = $teacherManager->getFollowListByOpenId($openId);
                $followCnt = count($followList);
            } else {
                $followCnt = 0;
            }
            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'follow_count' => $followCnt,
                ],
            ];
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = ['code' => SYS_STATUS_ERROR_UNKNOW];
        }

        return $ret;
    }
}
