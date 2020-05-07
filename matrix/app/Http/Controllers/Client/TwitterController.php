<?php

namespace Matrix\Http\Controllers\Client;

use Illuminate\Http\Request;
use Matrix\Contracts\UserManager;
use Matrix\Contracts\UcManager;
use Matrix\Contracts\TwitterManager;
use Matrix\Contracts\CategoryManager;
use Matrix\Exceptions\MatrixException;
use Exception;
use Log;

class TwitterController extends Controller
{
    private $request;
    // 兼容看高手多图片类型 APP版本号
    private $kgsImageArrayCompatibleVersion = '2.10.*';

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getTwitterInfo(UserManager $userManager, CategoryManager $categoryManager, TwitterManager $twitterManager, UcManager $ucenter, int $twitterId)
    {
        try {
            // get current user information from uc
            $sessionId = $this->request->header('X-SessionId');
            $currentUserInfo = $ucenter->getUserInfoBySessionId($sessionId);
            $currentOpenId = (string)array_get($currentUserInfo, 'data.user.openId');

            // get current user access code from uc
            //$serviceCodeList = $ucenter->getAccessCodeByOpenId($currentOpenId);
            $serviceCodeList = array_get($currentUserInfo, 'data.user.accessCodes', []); 

            // get twitter info from cms_twitters
            $twitterInfo = $twitterManager->getTwitterInfo($twitterId);
            if (empty($twitterInfo)) {
                // TODO softdelete?
                throw new MatrixException('我们似乎找不到这个解盘记录', TWITTER_NOT_FOUND);
            }

            $teacherInfo = $categoryManager->getTeacherById($twitterInfo['teacher_id']);
            $teacherInfo = array_get($teacherInfo, 'data.teacher');
            $userInfo = $userManager->getUserInfo($teacherInfo['user_id']);
            $userInfo = array_get($userInfo, 'userInfo');

            $categoryInfo = $categoryManager->getCategoryInfoByCode($twitterInfo['category_code']);

            $twitterInfo['type'] = 'twitter';
            $twitterInfo['icon_url'] = $userInfo['icon_url'];
            $twitterInfo['name'] = $userInfo['name'];
            $twitterInfo['category_name'] = $categoryInfo['name'];
            $twitterInfo['access_deny'] = (int)!in_array($categoryInfo['service_key'], $serviceCodeList);
            $twitterInfo['jump_type'] = 'message';

            // 针对看高手多图片兼容
            $imageUrlArr = array_get($twitterInfo, 'image_url');
            $ua = strtolower($this->request->userAgent());
            if (empty($imageUrlArr)) {
                if (!empty($ua) && strpos($ua, 'zytg') !== false) {
                    $appVersion = $this->getAPPVersion($ua);
                    if (version_compare($appVersion, $this->kgsImageArrayCompatibleVersion) < 0) {
                        $twitterInfo['image_url'] = '';
                    } else {
                        $twitterInfo['image_url'] = [];
                    }
                } else {
                    $twitterInfo['image_url'] = [];
                }
            } else {
                if (!empty($ua) && strpos($ua, 'zytg') !== false) {
                    $appVersion = $this->getAPPVersion($ua);
                    if (version_compare($appVersion, $this->kgsImageArrayCompatibleVersion) < 0) {
                        $twitterInfo['image_url'] = $this->fitClientUrl((string)$imageUrlArr[0]);
                    } else {
                        foreach ($imageUrlArr as &$imageUrl) {
                            $imageUrl = $this->fitClientUrl((string)$imageUrl);
                        }
                        $twitterInfo['image_url'] = $imageUrlArr;
                    }
                } else {
                    foreach ($imageUrlArr as &$imageUrl) {
                        $imageUrl = $this->fitClientUrl((string)$imageUrl);
                    }
                    $twitterInfo['image_url'] = $imageUrlArr;
                }
            }

            if (!empty($twitterInfo['ref_id'])) {
                $twitterInfo['refer'] = [];
                $twitterInfo['refer']['ref_id'] = $twitterInfo['ref_id'];
                $twitterInfo['refer']['ref_category_code'] = $twitterInfo['ref_category_code'];
                $twitterInfo['refer']['ref_type'] = $twitterInfo['ref_type'];
                $twitterInfo['refer']['ref_thumb'] = $twitterInfo['ref_thumb'];
                $twitterInfo['refer']['ref_title'] = $twitterInfo['ref_title'];
                $twitterInfo['refer']['ref_summary'] = $twitterInfo['ref_summary'];
            }

            unset($twitterInfo['ref_id']);
            unset($twitterInfo['ref_category_code']);
            unset($twitterInfo['ref_type']);
            unset($twitterInfo['ref_thumb']);
            unset($twitterInfo['ref_title']);
            unset($twitterInfo['ref_summary']);

            if ($twitterInfo['access_deny'] == 1) {
                $twitterInfo['content'] = preg_replace("/(\x{ff08}[\x{4e00}-\x{9fa5}a-zA-Z\s+]*\d{5,6}[\x{4e00}-\x{9fa5}a-zA-Z\s+]*\s*\x{ff09})/u","（金股***）", $twitterInfo['content']);
                $twitterInfo['content'] = sprintf('%s...', mb_substr($twitterInfo['content'], 0 , 50));
                $twitterInfo['guide_media'] = 'ad';
            }

            $ret = [
                'code' => SYS_STATUS_OK,
                'msg' => '',
                'data' => [
                    'twitter' => $twitterInfo,
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
                'msg' => '出现了一个不可预知的错误',
            ];
        }

        return $ret;
    }

    public function getTwitterList(UserManager $userManager, CategoryManager $categoryManager, TwitterManager $twitterManager, UcManager $ucenter)
    {
        $credentials = $this->request->validate([
            'teacher_userid' => 'required|string',
            'index' => 'required|integer',
            'page_size' => 'required|integer',
        ]);
        $sessionId = $this->request->header('X-SessionId');
        $currentUserInfo = $ucenter->getUserInfoBySessionId($sessionId);
        $currentOpenId = (string)array_get($currentUserInfo, 'data.user.openId');
        //$serviceCodeList = $ucenter->getAccessCodeByOpenId($currentOpenId);
        $serviceCodeList = array_get($currentUserInfo, 'data.user.accessCodes', []); 

        $userInfo = $userManager->getUserByEnterpriseUserId(array_get($credentials, 'teacher_userid'));
        $teacherUserId = array_get($userInfo, 'data.id');
        $categoryList = $categoryManager->getCategoryListByUserId($teacherUserId);
        $categoryCodeList = array_column($categoryList, 'code');

        $twitterCategoryListData = $categoryManager->getCategoryListByGroupCode('twitter_group_a');
        $twitterCategoryList = array_get($twitterCategoryListData, 'data.category_list');
        $twitterCategoryCodeList = array_column($twitterCategoryList, 'code');

        $categoryCodeListForGetTwitter = array_intersect($categoryCodeList, $twitterCategoryCodeList);

        $hasReferContent = true;
        $ua = $this->request->userAgent();
        if (!empty($ua)) {
            $ua = strtolower($ua);
            if (strpos($ua, 'zytg') !== false) {
                $appVersion = $this->getAPPVersion($ua);
                $appVersionArr = explode('.', $appVersion);
                if (count($appVersionArr) >= 2 && version_compare($appVersion, "2.5.*") < 0) {
                    $hasReferContent = false;
                }
            }
        }

        $twitterListData = $twitterManager->getPageTwitterList($categoryCodeListForGetTwitter, $credentials['index'], $credentials['page_size'], '', [$teacherUserId], $hasReferContent, 3);
        $twitterList = array_get($twitterListData, 'data.twitter_list');
        $categoryList = array_column($categoryList, NULL, 'code');

        $showTwitterList = [];
        foreach ($twitterList as $twitter) {
            $categoryCode = array_get($twitter, 'category_code');
            $category = array_get($categoryList, $categoryCode);
            $currentTwitter = [
                'id' => array_get($twitter, 'id'),
                'type' => 'twitter',
                'icon_url' => array_get($userInfo, 'data.icon_url'),
                'name' => array_get($userInfo, 'data.name'),
                'created_at' => array_get($twitter, 'created_at'),
                'category_code' => $categoryCode,
                'category_name' => array_get($category, 'name'),
                'room_id' => (string)array_get($twitter, 'room_id'),
                'access_deny' => (int)!in_array($category['service_key'], $serviceCodeList),
                'content' => $this->makeClientContent((string)array_get($twitter, 'content')),
                'jump_type' => 'message',
                'source_id' => (string)array_get($twitter, 'source_id'),
            ];

            $imageUrlArr = (array)array_get($twitter, 'image_url');
            if (empty($imageUrlArr)) {
                $currentTwitter['image_url'] = '';
            } else {
                $currentTwitter['image_url'] = $this->fitClientUrl((string)$imageUrlArr[0]);
            }

            // 用于转发生成的解盘字段
            if (!empty($twitter['ref_id'])) {
                $currentTwitter['refer'] = [];
                $currentTwitter['refer']['ref_id'] = $twitter['ref_id'];
                $currentTwitter['refer']['ref_category_code'] = $twitter['ref_category_code'];
                $currentTwitter['refer']['ref_type'] = $twitter['ref_type'];
                $currentTwitter['refer']['ref_thumb'] = $this->fitClientUrl($twitter['ref_thumb']);
                $currentTwitter['refer']['ref_title'] = $twitter['ref_title'];
                $currentTwitter['refer']['ref_summary'] = $twitter['ref_summary'];
            }

            if ($currentTwitter['access_deny'] == 1) {
                $currentTwitter['content'] = preg_replace("/(\x{ff08}[\x{4e00}-\x{9fa5}a-zA-Z\s+]*\d{5,6}[\x{4e00}-\x{9fa5}a-zA-Z\s+]*\s*\x{ff09})/u","（金股***）", $currentTwitter['content']);
                if (mb_strlen($currentTwitter['content']) > 50) {
                    $currentTwitter['content'] = sprintf('%s...', mb_substr($currentTwitter['content'], 0 , 50));
                }
                $currentTwitter['guide_media'] = 'ad';
            }
            $showTwitterList[] = $currentTwitter;
        }

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'twitter_list' => $showTwitterList,
            ],
        ];

        return $ret;
    }
}
