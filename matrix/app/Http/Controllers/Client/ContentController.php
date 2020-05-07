<?php

namespace Matrix\Http\Controllers\Client;

use Illuminate\Http\Request;
use Matrix\Contracts\FeedManager;
use Matrix\Contracts\CategoryManager;
use Matrix\Contracts\CategoryGroupManager;
use Matrix\Contracts\UserManager;
use Matrix\Contracts\UcManager;
use Matrix\Contracts\VideoManager;
use Matrix\Contracts\CourseVideoManager;
use Matrix\Contracts\CourseManager;
use Matrix\Contracts\CustomerManager;
use Matrix\Contracts\CourseSystemManager;
use Matrix\Contracts\ArticleManager;
use Matrix\Contracts\TalkshowContract;
use Matrix\Contracts\TeacherManager;
use Matrix\Contracts\ContentGuardContract;
use Matrix\Contracts\TwitterManager;
use Matrix\Contracts\InteractionContract;
use Matrix\Models\ArticleReply;
use Jenssegers\Agent\Agent;

use Matrix\Services\ContentGuardService;

use Matrix\Exceptions\MatrixException;
use Matrix\Exceptions\PermissionException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Log;
use Exception;
use Matrix\Contracts\KitManager;
use Matrix\Contracts\StockReportManager;
use Matrix\Models\Kit;
use Matrix\Contracts\KitReportManager;

class ContentController extends Controller
{
    //
    const USER_GROUP_CODE_SUPERMAN_TAG = 'teacher_superman_tag';
    const REPLY_TYPE = 'article_reply';//评论类型

    protected $request;
    protected $feed;
    protected $category;
    protected $categoryGroup;
    protected $user;
    protected $ucenter;
    protected $video;
    protected $courseVideo;
    protected $courseSystem;
    protected $course;
    protected $talkshow;
    protected $teacher;
    protected $contentGuard;
    protected $twitter;
    protected $stockReport;
    protected $kitReport;
    protected $kit;
    protected $customer;
    protected $interaction;
    protected $agent;
    // 兼容看高手多图片类型 APP版本号
    protected $kgsImageArrayCompatibleVersion = '2.10.*';

    protected $vhallVideoCompatibleVersion = '2.10.*';

    public function __construct(Request $request, FeedManager $feed, CategoryManager $category, UserManager $user, UcManager $ucenter, VideoManager $video, TalkshowContract $talkshow, ArticleManager $article, TeacherManager $teacher, CourseVideoManager $courseVideo, CourseManager $course, ContentGuardContract $contentGuard, TwitterManager $twitter, StockReportManager $stockReport, KitReportManager $kitReport, KitManager $kit, InteractionContract $interaction, CustomerManager $customer, CourseSystemManager $courseSystem, CategoryGroupManager $categoryGroup, Agent $agent)
    {
        $this->request = $request;
        $this->feed = $feed;
        $this->user = $user;
        $this->video = $video;
        $this->customer = $customer;
        $this->course = $course;
        $this->courseVideo = $courseVideo;
        $this->ucenter = $ucenter;
        $this->category = $category;
        $this->talkshow = $talkshow;
        $this->article = $article;
        $this->teacher = $teacher;
        $this->twitter = $twitter;
        $this->stockReport = $stockReport;
        $this->kitReport = $kitReport;
        $this->kit = $kit;
        $this->contentGuard = $contentGuard;
        $this->interaction = $interaction;
        $this->courseSystem = $courseSystem;
        $this->categoryGroup = $categoryGroup;
        $this->agent = $agent;
    }

    protected function isArticleCategory(string $categoryCode)
    {
        try {
            // category_group_code = article_group_a
            $categoryGroupCode = 'article_group_a';
            $categoryListData = $this->category->getCategoryListByGroupCode($categoryGroupCode);
            $categoryCodeList = array_column((array)array_get($categoryListData, 'data.category_list'), 'code');

            return in_array($categoryCode, $categoryCodeList);
        } catch (MatrixException $e) {
            return false;
        }
    }

    protected function isTwitterCategory(string $categoryCode)
    {
        try {
            // category_group_code = twitter_group_a
            $categoryGroupCode = 'twitter_group_a';
            $categoryListData = $this->category->getCategoryListByGroupCode($categoryGroupCode);
            $categoryCodeList = array_column((array)array_get($categoryListData, 'data.category_list'), 'code');

            return in_array($categoryCode, $categoryCodeList);
        } catch (MatrixException $e) {
            return false;
        }
    }

    protected function isCourseCategory(string $categoryCode)
    {
        try {
            $courseSystemList = $this->courseSystem->getCourseSystemList();
            if (empty($courseSystemList)) {
                return false;
            }
            $courseSystemCodeList = array_column($courseSystemList, 'code');
            return in_array($categoryCode, $courseSystemCodeList);
        } catch (MatrixException $e) {
            return false;
        }
    }

    protected function isNewsCategory(string $categoryCode)
    {
        return $categoryCode === 'news_stock_a';
    }

    protected function isStockReport(string $categoryCode)
    {
        return $categoryCode === 'cyzb';
    }

    protected function isKitReport (string $categoryCode)
    {
        return substr($categoryCode, 0, strlen(Kit::GENERATE_CODE_PREFIX)) === Kit::GENERATE_CODE_PREFIX;
    }

    protected function isTalkshowCategory(string $categoryCode)
    {
        try {
            // category_group_code = shipindengji_group
            $categoryGroupCode = 'allwin_visible';
            $categoryCodeList = $this->categoryGroup->getCategoryCodeList($categoryGroupCode);
            if (in_array('zhouzhanbao', $categoryCodeList)) {
                $categoryCodeList = array_diff($categoryCodeList, ['zhouzhanbao']);
            }
            if (in_array('cyzzb', $categoryCodeList)) {
                $categoryCodeList = array_diff($categoryCodeList, ['cyzzb']);
            }

            return in_array($categoryCode, $categoryCodeList);
        } catch (MatrixException $e) {
            return false;
        }
    }

    protected function isDailyTalkshowCategory(string $categoryCode)
    {
        return $categoryCode == 'daily_talkshow';
    }

    protected function isForwardTalkshowCategory(string $categoryCode)
    {
        return $categoryCode == 'forward_talkshow';
    }

    protected function isH5(string $categoryCode)
    {
        return $this->isArticleCategory($categoryCode) || $this->isCourseCategory($categoryCode) || $this->isTalkshowCategory($categoryCode) || $this->isNewsCategory($categoryCode);
    }

    protected function jumpSettingMask(array &$content)
    {
        if (!array_key_exists('feed_type', $content)) {
            $content['feed_type'] = 99999;
        }
        if ($this->isH5($content['category_key']) || ($content['feed_type'] == 1)) {
            $content['jump_type'] = $content['jump_type'] ?? "common_web";
            if (strpos($content['source_url'], '.youku.com') > 0) {
                $content['jump_params'] = ['agent' => "MQQBrowser/6.2 TBS/036558 MicroMessenger/6.3.25.861"];
                $content['source_agent'] = "MQQBrowser/6.2 TBS/036558 MicroMessenger/6.3.25.861";
            }
            if (array_get($content, "access_deny")) {
                $content['guide_media'] = 'page';
        
                //特殊处理产业金股的个股研报模块，有权限时候跳转报告h5，无权限跳转产业金股模块海报
                if ($content['feed_type'] == 1) {
                    $content['jump_type'] = "cyzb";
                    $content['guide_media'] = 'ad';
                }
            }
        } else {
            if ((array_key_exists('media_type', $content)) && ($content["media_type"] == 'pdf') && (empty($content["access_deny"]))) {
                $content['jump_type'] = "pdf";
            } else {
                $content['jump_type'] = empty($content['jump_type']) ? $content['category_key'] : $content['jump_type'];
            }
            if ((array_key_exists('media_type', $content)) && $content["media_type"] == 'message') {
                $content['jump_type'] = empty($content["access_deny"]) ? "message" : $content["category_key"];
            }
            if (!empty($content["access_deny"])) {
                $content['guide_media'] = 'ad';
                //特殊处理：产业周战报无权访问时候，引导跳转到默认无权页，而非周战报开通页
                if ($content["category_key"] == "zhouzhanbao" && $content['access_level'] == 'cyzzb') {
                    $content['guide_media'] =  'blank';
                    $content['jump_type'] = "pdf";
                }
                // 特殊处理：锦囊报告无权访问时候，引导跳转到锦囊购买引导页
                if ($this->isKitReport($content['category_key'])) {
                    $content['guide_media'] = 'h5';
                }
            }
        }

        if (empty(array_get($content, 'access_deny')) && array_key_exists('media_type', $content) && $content['media_type'] == 'pdf') {
            $content['jump_type'] = 'pdf';
        }

        $videoSigninFormat = config('video.video.signin_format');
        if (array_key_exists('source_url', $content) && (strpos($content['source_url'], $videoSigninFormat) !== false)) {
            $urlArr = explode('/', $content['source_url']);
            $videoKey = $urlArr[count($urlArr) - 1];
            $videoInfoData = $this->video->getVideoSigninInfo($videoKey);
            $videoInfo = array_get($videoInfoData, 'data');
            if (!empty($videoInfo) && strpos($videoInfo['url'], 'gensee') !== false) {
                $videoVendor = $this->talkshow->getVideoVendor('video_gensee');
                $content['jump_type'] = 'video_gensee';

                // APP版本大于2.6 课程jump_type 变成 course_gensee  http://task.daohehui.com/browse/ZYAPP-505
                if ($this->isCourseCategory($content['category_key'])) {
                    if ($this->versionCompare("2.7.*")){
                        $content['jump_type'] = 'course_gensee';
                    }
                }

                $videoUrlArr = explode('-', $videoInfo['url']);
                $content['jump_params'] = [
                    "sdk_video_vodid" => array_get($videoUrlArr, count($videoUrlArr) - 1), // 当视频类型时，给视频SDK使用的视频id
                    "sdk_video_domain" => $videoVendor['domain'], // 当视频类型时，给视频SDK使用的播放域名
                    "sdk_video_vendor" => $videoVendor['code'], // 当视频类型时，视频SDK的供应商
                ];
            }

            if (!empty($videoInfo) && strpos($videoInfo['url'], 'vhall') !== false && $this->versionCompare($this->vhallVideoCompatibleVersion)) {
                $videoVendor = $this->talkshow->getVideoVendor('video_vhall');
                $content['jump_type'] = 'video_vhall';
                
                if ($this->isCourseCategory($content['category_key'])) {
                    $content['jump_type'] = 'course_gensee';
                }
                $videoUrlArr = explode('/', $videoInfo['url']);
                if (strpos($videoUrlArr[count($videoUrlArr) - 1], '?') !== false) {
                    $sdkVideoVodid = explode('?', $videoUrlArr[count($videoUrlArr) - 1])[0];
                } else {
                    $sdkVideoVodid = $videoUrlArr[count($videoUrlArr) - 1];
                }
                $content['jump_params'] = [
                    'sdk_video_vodid' => $sdkVideoVodid,
                    'sdk_video_vendor' => $videoVendor['code'],
                ];
            }
        }

        if (array_key_exists('source_url', $content) && !empty($content['source_url'])) {
            $content['source_url'] = $this->fitDetailUrl($content['source_url']);
        }

        unset($content['feed_type']);
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

    protected function versionCompare(string $version)
    {
        $result = false;

        $strUa = $this->request->userAgent() ? strtolower($this->request->userAgent()) : '';
        if ($strUa && preg_match('#zytg#', $strUa)){
            $appVersion = $this->getAPPVersion($strUa);
            $appVersionArr = explode('.', $appVersion);
            if (count($appVersionArr) >= 2 && version_compare($appVersion, $version) > 0) {
                $result = true;
            }
        }

        return $result;
    }

    protected function getArticleInfo(array &$content, $detailId)
    {
        try {
            $articleData = $this->article->getArticleInfo($detailId);
            $article = array_get($articleData, 'data.article');
            $content['content_id'] = $article['id'];
            $content['title'] = $article['title'];
            $content['summary'] = $article['summary'];
            $content['media_type'] = 'news';
            $content['source_url'] = sprintf('%s/api/v2/client/article/%s', config('app.url'), $detailId);
            $content['add_time'] = array_get($article, 'created_at');

            $teacherData = $this->teacher->getTeacherInfo($article['teacher_id']);
            $teacher = array_get($teacherData, 'data.teacher_info');
            if (!empty($teacher)) {
                $authorUserData = $this->user->getUserInfo($teacher['user_id']);
                $authorUser = array_get($authorUserData, 'userInfo');
                $content['owner_user_id'] = (int)array_get($authorUser, 'id');
                $content['owner_name'] = (string)array_get($authorUser, 'name');
                $content['owner_avatar'] = (string)array_get($authorUser, 'icon_url');
            }
            if (!empty($content['access_deny'])) {
                $content['guide_media'] = 'page';
                $content['guide_msg'] = $article['ad_guide'];
            }
        } catch (ModelNotFoundException $e) {
            throw new MatrixException('内容没有找到', CONTENT_NOT_FOUND);
        }
    }

    protected function getTwitterInfo(array &$content, $detailId)
    {
            // get twitter info from cms_twitters
            $twitterInfo = $this->twitter->getTwitterInfo($detailId);
            if (empty($twitterInfo)) {
                // TODO softdelete?
                throw new MatrixException('我们似乎找不到这个解盘记录', TWITTER_NOT_FOUND);
            }

            $content['title'] = '';
            $content['content_id'] = $twitterInfo['id'];
            $content['summary'] = $twitterInfo['content'];
            $content['source_id'] = $twitterInfo['source_id'];
            $content['media_type'] = 'message';
            $content['source_url'] = '';
            $content['add_time'] = array_get($twitterInfo, 'created_at');

            $teacherData = $this->teacher->getTeacherInfo($twitterInfo['teacher_id']);
            $teacher = array_get($teacherData, 'data.teacher_info');
            if (!empty($teacher)) {
                $authorUserData = $this->user->getUserInfo($teacher['user_id']);
                $authorUser = array_get($authorUserData, 'userInfo');
                $authorUc = array_get($authorUserData, 'ucInfo');
                $content['owner_id'] = (string)array_get($authorUc, 'enterprise_userid');
                $content['owner_user_id'] = (int)array_get($authorUser, 'id');
                $content['owner_name'] = (string)array_get($authorUser, 'name');
                $content['owner_avatar'] = (string)array_get($authorUser, 'icon_url');
            }

            // 针对看高手多图片兼容
            $imageUrlArr = array_get($twitterInfo, 'image_url');
            $ua = strtolower($this->request->userAgent());
            if (empty($imageUrlArr)) {
                if (!empty($ua) && strpos($ua, 'zytg') !== false) {
                    $appVersion = $this->getAPPVersion($ua);
                    if (version_compare($appVersion, $this->kgsImageArrayCompatibleVersion) < 0) {
                        $content['image_url'] = '';
                    } else {
                        $content['image_url'] = [];
                    }
                } else {
                    $content['image_url'] = [];
                }
            } else {
                if (!empty($ua) && strpos($ua, 'zytg') !== false) {
                    $appVersion = $this->getAPPVersion($ua);
                    if (version_compare($appVersion, $this->kgsImageArrayCompatibleVersion) < 0) {
                        $content['image_url'] = $this->fitClientUrl((string)$imageUrlArr[0]);
                    } else {
                        foreach ($imageUrlArr as &$imageUrl) {
                            $imageUrl = $this->fitClientUrl((string)$imageUrl);
                        }
                        $content['image_url'] = $imageUrlArr;
                    }
                } else {
                    foreach ($imageUrlArr as &$imageUrl) {
                        $imageUrl = $this->fitClientUrl((string)$imageUrl);
                    }
                    $content['image_url'] = $imageUrlArr;
                }
            }

            $content['jump_type'] = 'message';

            if (!empty($twitterInfo['ref_id'])) {
                $content['refer'] = [];
                $content['refer']['ref_id'] = (string)$twitterInfo['ref_id'];
                $content['refer']['ref_category_code'] = $twitterInfo['ref_category_code'];
                $content['refer']['ref_type'] = $twitterInfo['ref_type'];
                $content['refer']['ref_thumb'] = $twitterInfo['ref_thumb'];
                $content['refer']['ref_title'] = $twitterInfo['ref_title'];
                $content['refer']['ref_summary'] = $twitterInfo['ref_summary'];
            }

            unset($content['ref_id']);
            unset($content['ref_category_code']);
            unset($content['ref_type']);
            unset($content['ref_thumb']);
            unset($content['ref_title']);
            unset($content['ref_summary']);

            if ($content['access_deny'] == 1) {
                $content['summary'] = preg_replace("/(\x{ff08}[\x{4e00}-\x{9fa5}a-zA-Z\s+]*\d{5,6}[\x{4e00}-\x{9fa5}a-zA-Z\s+]*\s*\x{ff09})/u","（金股***）", $twitterInfo['content']);
                $content['summary'] = sprintf('%s...', mb_substr($twitterInfo['content'], 0 , 50));
                $content['guide_media'] = 'ad';
            } else {
                $content['summary'] = array_get($twitterInfo, 'content');
            }

    }

    protected function getTwitterInfoBySourceId(array &$content, $sourceId)
    {
        $twitterInfo = $this->twitter->getTwitterInfoBySourceId($sourceId);
        if (empty($twitterInfo)) {
            throw new MatrixException('我们似乎找不到这个解盘记录', TWITTER_NOT_FOUND);
        }

        $content['title'] = '';
        $content['category_key'] = $twitterInfo['category_code'];
        $content['content_id'] = $twitterInfo['id'];
        $content['summary'] = $twitterInfo['content'];
        $content['source_id'] = $twitterInfo['source_id'];
        $content['media_type'] = 'message';
        $content['source_url'] = '';
        $content['add_time'] = array_get($twitterInfo, 'created_at');

        $teacherData = $this->teacher->getTeacherInfo($twitterInfo['teacher_id']);
        $teacher = array_get($teacherData, 'data.teacher_info');
        if (!empty($teacher)) {
            $authorUserData = $this->user->getUserInfo($teacher['user_id']);
            $authorUser = array_get($authorUserData, 'userInfo');
            $authorUc = array_get($authorUserData, 'ucInfo');
            $content['owner_id'] = (string)array_get($authorUc, 'enterprise_userid');
            $content['owner_user_id'] = (int)array_get($authorUser, 'id');
            $content['owner_name'] = (string)array_get($authorUser, 'name');
            $content['owner_avatar'] = (string)array_get($authorUser, 'icon_url');
        }

        // 针对看高手多图片兼容
        $imageUrlArr = array_get($twitterInfo, 'image_url');
        $ua = strtolower($this->request->userAgent());
        if (empty($imageUrlArr)) {
            if (!empty($ua) && strpos($ua, 'zytg') !== false) {
                $appVersion = $this->getAPPVersion($ua);
                if (version_compare($appVersion, $this->kgsImageArrayCompatibleVersion) < 0) {
                    $content['image_url'] = '';
                } else {
                    $content['image_url'] = [];
                }
            } else {
                $content['image_url'] = [];
            }
        } else {
            if (!empty($ua) && strpos($ua, 'zytg') !== false) {
                $appVersion = $this->getAPPVersion($ua);
                if (version_compare($appVersion, $this->kgsImageArrayCompatibleVersion) < 0) {
                    $content['image_url'] = $this->fitClientUrl((string)$imageUrlArr[0]);
                } else {
                    foreach ($imageUrlArr as &$imageUrl) {
                        $imageUrl = $this->fitClientUrl((string)$imageUrl);
                    }
                    $content['image_url'] = $imageUrlArr;
                }
            } else {
                foreach ($imageUrlArr as &$imageUrl) {
                    $imageUrl = $this->fitClientUrl((string)$imageUrl);
                }
                $content['image_url'] = $imageUrlArr;
            }
        }
        
        $content['jump_type'] = 'message';

        if (!empty($twitterInfo['ref_id'])) {
            $content['refer'] = [];
            $content['refer']['ref_id'] = (string)$twitterInfo['ref_id'];
            $content['refer']['ref_category_code'] = $twitterInfo['ref_category_code'];
            $content['refer']['ref_type'] = $twitterInfo['ref_type'];
            $content['refer']['ref_thumb'] = $twitterInfo['ref_thumb'];
            $content['refer']['ref_title'] = $twitterInfo['ref_title'];
            $content['refer']['ref_summary'] = $twitterInfo['ref_summary'];
        }

        unset($content['ref_id']);
        unset($content['ref_category_code']);
        unset($content['ref_type']);
        unset($content['ref_thumb']);
        unset($content['ref_title']);
        unset($content['ref_summary']);

        $content['summary'] = array_get($twitterInfo, 'content');
    }

    protected function getTalkshowInfo(array &$content, $detailId, $serviceCodeList, $feedAccessLevel)
    {
        $talkshow = $this->feed->getTjWxSendLogDetail((int)$detailId);

        // if ($content['category_key'] != $talkshow->category) {
        //     throw new MatrixException('分类不一致，去feed查找');
        // }

        $content['title'] = $talkshow->title;
        $content['summary'] = $talkshow->digest;
        $content['media_type'] = $talkshow->msg_type;

        if (strpos($talkshow->title, '[保密]') === 0 && strpos($talkshow->content_url, 'getsafemsg') !== false && $talkshow->msg_type == 'news') {
            $content['source_url'] = sprintf('/getHistoryData.php?did=%s', $detailId);
        } elseif (in_array($talkshow->msg_type, ['file', 'pdf'])) {
            $content['source_url'] = $talkshow->content_local_url;
        } elseif (strpos($talkshow->content_url, 'open.work.weixin.qq.com') !== false && $talkshow->content_local_url == '#local_data#') {
            $content['source_url'] = sprintf('/getHistoryData.php?did=%s', $detailId);
        } else {
            $content['source_url'] = $talkshow->content_url;
        }

        $content['category_key'] = (string)$talkshow->category;

        $videoSigninFormat = config('video.video.signin_format');
        if (strpos($content['source_url'], $videoSigninFormat) !== false) {
            $urlArr = explode('/', $content['source_url']);
            $videoKey = $urlArr[count($urlArr) - 1];
            $videoInfoData = $this->video->getVideoSigninInfo($videoKey);
            $videoInfo = array_get($videoInfoData, 'data');
        }

        $content['content_id'] = empty($videoInfo) ? $detailId : (string)array_get($videoInfo, 'video_key');

        $content['thumb_cdn_url'] = $talkshow->thumb_cdn_url;
        $content['thumb_local_url'] = $talkshow->thumb_local_url;
        $content['add_time'] = date('Y-m-d H:i:s', $talkshow->send_time);
        $content['file_size'] = $talkshow->file_size;

        if (!empty($talkshow->owner_id)) {
            $authorUserData = $this->user->getUserByEnterpriseUserId($talkshow->owner_id);
            $authorUser = array_get($authorUserData, 'data');

            $content['owner_id'] = (string)$talkshow->owner_id;
            $content['owner_user_id'] = (int)array_get($authorUser, 'id');
            $content['owner_name'] = (string)array_get($authorUser, 'name');
            $content['owner_avatar'] = (string)array_get($authorUser, 'icon_url');
        }
        
        // 判断 1. feed_id 按照 feed 表中 access_level 2. detail_id 按照 tj_wx_send_log_detail 表中 category
        if (empty($feedAccessLevel)) {
            $category = $this->category->getCategoryInfoByCode($content['category_key']);
            $content['access_level'] = (string)array_get($category, 'service_key');
        } else {
            $content['access_level'] = $feedAccessLevel;
        }

        $content['access_deny'] = (int)!in_array($content['access_level'], $serviceCodeList);

        if (!empty($content['access_deny'])) {
            $content['guide_media'] = '';
            $content['guide_msg'] = $talkshow->ad_guide;
            $content['demo_url'] = '';
        }

        if (!empty($talkshow->content_url)) {
            $videoData = $this->video->findVideoByUrl($talkshow->content_url);
            $video = array_get($videoData, 'data.video');
            if (!empty($video)) {
                $content['content_id'] = $video['video_key'];
            }
        }
    }

    public function getDailyTalkshowInfo(&$content, $detailId)
    {
        $talkshow = $this->talkshow->getTalkshow($detailId);
        $content['title'] = $talkshow['title'];
        $content['talkshow_type'] = $talkshow['type'];
        $content['summary'] = $talkshow['boardcast_content'];
        $content['thumb_cdn_url'] = $talkshow['banner_url'];
        $content['thumb_local_url'] = $talkshow['banner_url'];
        $content['add_time'] = $talkshow['created_at'];
        $content['owner_user_id'] = (int)array_get($talkshow, 'teacher_user_id');

        $userInfoData = $this->user->getUserInfo($content['owner_user_id']);
        $content['owner_id'] = (string)array_get($userInfoData, 'ucInfo.enterprise_userid');
        $content['owner_name'] = (string)array_get($userInfoData, 'userInfo.name');
        $content['owner_avatar'] = (string)array_get($userInfoData, 'userInfo.icon_url');

        if (in_array($talkshow['video_vendor_code'], ['video_gensee'])) {
            $videoVendor = $this->talkshow->getVideoVendor($talkshow['video_vendor_code']);
            $content['jump_type'] = $talkshow['video_vendor_code'];
            if ((!empty($talkshow['live_room_code'])) && empty($talkshow['play_url'])) {
                $vodid = $talkshow['live_room_code'];
            } elseif (!empty($talkshow['play_url'])) {
                $vodid = $talkshow['code'];
                $videoData = $this->video->findVideoByUrl($talkshow['play_url']);
                $videoInfo = array_get($videoData, 'data.video');
                $content['content_id'] = (string)array_get($videoInfo, 'video_key');
                $content['source_url'] = sprintf('%s%s', config('video.video.url'), (string)array_get($videoInfo, 'video_key'));
            }

            $content['jump_params'] = [
                "sdk_video_vodid" => $vodid, // 当视频类型时，给视频SDK使用的视频id
                "sdk_video_domain" => $videoVendor['domain'], // 当视频类型时，给视频SDK使用的播放域名
                "sdk_video_vendor" => $videoVendor['code'], // 当视频类型时，视频SDK的供应商
            ];
        } elseif (in_array($talkshow['video_vendor_code'], ['video_vhall']) && $this->versionCompare($this->vhallVideoCompatibleVersion)) {
            $videoVendor = $this->talkshow->getVideoVendor($talkshow['video_vendor_code']);
            $content['jump_type'] = $talkshow['video_vendor_code'];
            if ((!empty($talkshow['live_room_code'])) && empty($talkshow['play_url'])) {
                $vodid = $talkshow['live_room_code'];
            } elseif (!empty($talkshow['play_url'])) {
                $vodid = $talkshow['code'];
                $videoData = $this->video->findVideoByUrl($talkshow['play_url']);
                $videoInfo = array_get($videoData, 'data.video');
                $content['content_id'] = (string)array_get($videoInfo, 'video_key');
                $content['source_url'] = sprintf('%s%s', config('video.video.url'), (string)array_get($videoInfo, 'video_key'));
            }

            $content['jump_params'] = [
                'sdk_video_vodid' => $vodid,
                'sdk_video_vendor' => $videoVendor['code'],
            ];
        } else {
            $videoData = $this->video->findVideoByUrl($talkshow['play_url']);
            $content['jump_type'] = 'common_web';
            $content['source_url'] = sprintf('%s%s', config('video.video.url'), (string)array_get($videoData, 'data.video.video_key'));
        }
    }

    public function getForwardTalkshowInfo(&$content, $detailId)
    {
        $videoData = $this->video->getVideoSigninInfo($detailId);
        $video = array_get($videoData, 'data');
        $content['title'] = $video['title'];
        $content['content_id'] = $video['video_key'];
        $content['summary'] = $video['description'];
        $content['thumb_cdn_url'] = '';
        $content['thumb_local_url'] = '';
        $content['add_time'] = $video['created_at'];
        $content['owner_user_id'] = $video['author'];

        $userInfoData = $this->user->getUserInfo($content['owner_user_id']);
        $content['owner_id'] = (string)array_get($userInfoData, 'ucInfo.enterprise_userid');
        $content['owner_name'] = (string)array_get($userInfoData, 'userInfo.name');
        $content['owner_avatar'] = (string)array_get($userInfoData, 'userInfo.icon_url');
        $content['access_level'] = 'basic';
        $content['access_deny'] = 0;

        if (!empty($video) && strpos($video['url'], 'gensee') !== false) {
            $videoVendor = $this->talkshow->getVideoVendor('video_gensee');
            $content['jump_type'] = 'video_gensee';
            $videoUrlArr = explode('-', $video['url']);
            $content['jump_params'] = [
                "sdk_video_vodid" => array_get($videoUrlArr, count($videoUrlArr) - 1), // 当视频类型时，给视频SDK使用的视频id
                "sdk_video_domain" => $videoVendor['domain'], // 当视频类型时，给视频SDK使用的播放域名
                "sdk_video_vendor" => $videoVendor['code'], // 当视频类型时，视频SDK的供应商
            ];
        } elseif (!empty($video) && strpos($video['url'], 'vhall') !== false && $this->versionCompare($this->vhallVideoCompatibleVersion)) {
            $videoVendor = $this->talkshow->getVideoVendor('video_vhall');
            $content['jump_type'] = 'video_vhall';
            $videoUrlArr = explode('/', $video['url']);
            if (strpos($videoUrlArr[count($videoUrlArr) - 1], '?') !== false) {
                $sdkVideoVodid = explode('?', $videoUrlArr[count($videoUrlArr) - 1])[0];
            } else {
                $sdkVideoVodid = $videoUrlArr[count($videoUrlArr) - 1];
            }
            $content['jump_params'] = [
                'sdk_video_vodid' => $sdkVideoVodid,
                'sdk_video_vendor' => $videoVendor['code'],
            ];
        } else {
            $content['jump_type'] = 'common_web';
            $content['source_url'] = sprintf('%s%s', config('video.video.url'), (string)array_get($video, 'video_key'));
        }
    }

    public function getCourseVideo(array &$content, string $detailId, $serviceCodeList, $feedAccessLevel = '')
    {
        $videoData = $this->video->getVideoSigninInfo($detailId);
        $video = array_get($videoData, 'data');
        $content['title'] = $video['title'];
        $content['content_id'] = $video['video_key'];

        $courseVideoData = $this->courseVideo->getCourseVideoInfo(0, $video['id']);
        $courseVideo = array_get($courseVideoData, 'data.course_video_info');

        $courseData = $this->course->getCourseInfoByCode($courseVideo['course_code']);
        $course = array_get($courseData, 'data');

        $courseSystemCode = (string)array_get($course, 'course_system_code');
        $courseSystem = $this->courseSystem->getCourseSystemByCode($courseSystemCode);
        $content['category_name'] = $courseSystem->name;

        $content['summary'] = sprintf("%s\n%s", $course['description'], $video['description']);
        $content['media_type'] = 'news';

        $content['thumb_cdn_url'] = $courseVideo['picture_path'];
        $content['add_time'] = $courseVideo['created_at'];

        # ZYAPP-505 综合详情接口增加返回 课程名与课程编码
        $content['course_name'] = (string)array_get($course, 'name');
        $content['course_code'] = $courseVideo['course_code'];
        if (!empty($video['author'])) {
            $authorUserData = $this->user->getUserInfo($video['author']);
            $authorUser = array_get($authorUserData, 'userInfo');
            $authorUc = array_get($authorUserData, 'ucInfo');
            $content['owner_user_id'] = (int)array_get($authorUser, 'id');
            $content['owner_name'] = (string)array_get($authorUser, 'name');
            $content['owner_avatar'] = (string)array_get($authorUser, 'icon_url');
            $content['owner_id'] = (string)array_get($authorUc, 'enterprise_userid');
        }

        if (empty($feedAccessLevel)) {
            $contentGuard = $this->contentGuard->getOneAccessCode($course['course_system_code'], $course['code'], ContentGuardService::URI_COURSE_ACCESS);
            $content['access_level'] = empty($contentGuard) ? 'basic' : $contentGuard['service_code'];
        } else {
            $content['access_level'] = $feedAccessLevel;
        }

        $content['access_deny'] = empty($content['access_level']) ? 0 : (int)!in_array($content['access_level'], $serviceCodeList);

        if (empty($content['access_deny'])) {
            $content['source_url'] = sprintf("%s/api/v2/client/course/detail/%s", config('app.url'), $video['video_key']);

            if (!empty($video) && strpos($video['url'], 'gensee') !== false) {
                $videoVendor = $this->talkshow->getVideoVendor('video_gensee');
                $content['jump_type'] = 'video_gensee';
                # APP版本大于2.6 课程jump_type 变成 course_gensee  http://task.daohehui.com/browse/ZYAPP-505
                $versionValid = $this->versionCompare("2.7.*");
                if ($versionValid) {
                    $content['jump_type'] = 'course_gensee';
                }
                $videoUrlArr = explode('-', $video['url']);
                $content['jump_params'] = [
                    "sdk_video_vodid" => array_get($videoUrlArr, count($videoUrlArr) - 1), // 当视频类型时，给视频SDK使用的视频id
                    "sdk_video_domain" => $videoVendor['domain'], // 当视频类型时，给视频SDK使用的播放域名
                    "sdk_video_vendor" => $videoVendor['code'], // 当视频类型时，视频SDK的供应商
                ];
            } elseif (!empty($video) && strpos($video['url'], 'vhall') !== false && $this->versionCompare($this->vhallVideoCompatibleVersion)) {
                $videoVendor = $this->talkshow->getVideoVendor('video_vhall');
                $content['jump_type'] = 'course_gensee';

                $videoUrlArr = explode('/', $video['url']);
                if (strpos($videoUrlArr[count($videoUrlArr) - 1], '?') !== false) {
                    $sdkVideoVodid = explode('?', $videoUrlArr[count($videoUrlArr) - 1])[0];
                } else {
                    $sdkVideoVodid = $videoUrlArr[count($videoUrlArr) - 1];
                }
                $content['jump_params'] = [
                    'sdk_video_vodid' => $sdkVideoVodid,
                    'sdk_video_vendor' => $videoVendor['code'],
                ];
            } else {
                $content['jump_type'] = 'common_web';
            }
        } else {
            $content['source_url'] = $courseVideo['demo_url'];
            $versionValid = $this->versionCompare("2.7.*");
            if ($versionValid) {
                $content['jump_type'] = 'course_gensee';
            }
        }

        if (!empty($content['access_deny'])) {
            $content['guide_media'] = '';
            $content['guide_msg'] = $courseVideo['ad_guide'];
            $content['demo_url'] = $courseVideo['demo_url'];
            // 无权限时 返回对应试看视频的信息
            // 试看视频 链接有两种形式  1） 视频登记之后的链接  2）视频登记之前的链接
            // 这里处理 视频登记之前的链接 （只处理 展示互动 形式的链接）
            if (!empty($courseVideo) && strpos($courseVideo['demo_url'], 'gensee') !== false) {
                $videoVendor = $this->talkshow->getVideoVendor('video_gensee');
                # APP版本大于2.6 进行处理 试看视频
                if ($versionValid) {
                    $videoUrlArr = explode('-', $courseVideo['demo_url']);
                    $content['jump_params'] = [
                        "sdk_video_vodid" => array_get($videoUrlArr, count($videoUrlArr) - 1), // 当视频类型时，给视频SDK使用的视频id
                        "sdk_video_domain" => $videoVendor['domain'], // 当视频类型时，给视频SDK使用的播放域名
                        "sdk_video_vendor" => $videoVendor['code'], // 当视频类型时，视频SDK的供应商
                    ];
                }
            } elseif (!empty($courseVideo) && strpos($courseVideo['demo_url'], 'vhall') !== false && $this->versionCompare($this->vhallVideoCompatibleVersion)) {
                $videoVendor = $this->talkshow->getVideoVendor('video_vhall');
                $videoUrlArr = explode('/', $video['url']);
                if (strpos($videoUrlArr[count($videoUrlArr) - 1], '?') !== false) {
                    $sdkVideoVodid = explode('?', $videoUrlArr[count($videoUrlArr) - 1])[0];
                } else {
                    $sdkVideoVodid = $videoUrlArr[count($videoUrlArr) - 1];
                }
                $content['jump_params'] = [
                    'sdk_video_vodid' => $sdkVideoVodid,
                    'sdk_video_vendor' => $videoVendor['code'],
                ];
            }
        }
    }

    public function getNewsInfo(array &$content, $detailId)
    {
        $twitter = $this->twitter->getTwitterInfoByRefer($content['category_key'], $detailId);
        $params['content_id'] = (string)$twitter->ref_id;
        $params['title'] = (string)$twitter->ref_title;
        $params = [
            'news_id' => $detailId,
        ];

        if (!empty($twitter->ref_thumb)) {
            $params['pic_url'] = $twitter->ref_thumb;
            $content['thumb_cdn_url'] = (string)$twitter->ref_thumb;
        }

        $content['source_url'] = sprintf('%s%s?%s', config('app.url'), '/api/v2/client/news', http_build_query($params));
    }

    public function getStockReportInfo(array &$content, $detailId)
    {
        $stockReport = $this->stockReport->getStockReportInfoByStockReportId($detailId);
        $content['content_id'] = $stockReport['report_id'];
        $content['title'] = $stockReport['report_title'];
        $content['summary'] = $stockReport['report_summary'];
        $content['thumb_cdn_url'] = '';
        $content['add_time'] = $stockReport['created_at'];

        if (array_get($content, "access_deny")) {
            $content['jump_type'] = "cyzb";
            $content['guide_media'] = 'ad';
        } else {
            $content['jump_type'] = "common_web";
        }

        $content['source_url'] = sprintf('%s/api/v2/client/stock_report/%s', config('app.url'), $detailId);
    }

    public function getKitReportInfo(array &$content, $detailId)
    {

        $kitReport = $this->kitReport->getKitReportInfoByKitReportId($detailId);
        $content['content_id'] = $kitReport['report_id'];
        $content['title'] = $kitReport['title'];
        $content['summary'] = $kitReport['summary'];
        $content['thumb_cdn_url'] = $this->fitDetailUrl($kitReport['cover_url']);
        $content['add_time'] = $kitReport['start_at'];
        $content['jump_type'] = "common_web";

        $content['source_url'] = sprintf('%s/api/v2/client/kit_report/%s', config('app.url'), $detailId);

        if (!empty($content['access_deny'])) {
            $content['jump_params'] = [
                'guide_url' => $this->fitDetailUrl(sprintf('%s/api/v2/client/kit/detail/%s', config('app.url'), $kitReport['kit_code']))
            ];
        }
    }

    public function getContentInfo($detailId = NULL)
    {
        $credentials = $this->request->validate([
            'category_key' => 'required|string',
            'feed_id' => 'nullable|string',
        ]);

        $feed = [];
        $feedAccessLevel = '';

        try {
            $sessionId = $this->request->header('X-SessionId');
            if (empty($sessionId)) {
                $sessionId = $this->request->cookie('X-SessionId');
            }

            if (empty($sessionId)) {
                throw new PermissionException('尚未登录', SYS_STATUS_PERMISSION_ERROR);
            }

            $ucUserInfo = $this->ucenter->getUserInfoBySessionId($sessionId);
            $openId = (string)array_get($ucUserInfo, 'data.user.openId');
            //$this->ucenter->getAccessCodeByOpenId($openId, 'default', true);
            $accessCodeList = array_get($ucUserInfo, 'data.user.accessCodes', []); 
            

            $qyUserId = (string)array_get($ucUserInfo, 'data.user.qyUserId');
            $userInfo = $this->user->getUserByEnterpriseUserId($qyUserId);
            $userId = (int)array_get($userInfo, 'data.id');

            $content = [];
            $categoryCode = array_get($credentials, 'category_key');
            $feedId = (string)array_get($credentials, 'feed_id');
            if (empty($feedId) && empty($detailId)) {
                throw new MatrixException('内容没有找到', CONTENT_NOT_FOUND);
            }

            if (!empty($feedId)) { // feed_id priority
                $feed = $this->feed->getFeedInfo($feedId);
                if (empty($feed)) { // feed not found
                    throw new MatrixException('内容没有找到', CONTENT_NOT_FOUND);
                }
                $detailId = $feed['source_id'];
            }

            $content['detail_id'] = $detailId;

            $isCourseCategory = $this->isCourseCategory($categoryCode);

            $content['category_key'] = $categoryCode;

            try {
                // 根据 detail_id 查询时，传值 category_key 符合 个股报告等类型 根据 传值 category_key 值返回数据以及权限
                $category = $this->category->getCategoryInfoByCode($categoryCode);
                $content['category_name'] = (string)$category['name'];
                $content['category_ad_url'] = (string)array_get($category, 'ad_image_url');
                $content['access_level'] = (string)array_get($category, 'service_key');
                $content['access_deny'] = (int)!in_array($content['access_level'], $accessCodeList);
            } catch (MatrixException $e) {
            }

            if (!empty($feed) && !empty($feed['access_level'])) {
                // 根据 feed_id 查询 详情  需要根据 feed 表中 access_level 值 返回权限
                // 因为 根据 category_key 获取数据详情时，需要 根据 access_deny 返回 无权限 跳转字段
                $content['access_level'] = (string)$feed['access_level'];
                $content['access_deny'] = (int)!in_array($content['access_level'], $accessCodeList);
            }

            $feedFlag = false;
            if ($this->isDailyTalkshowCategory($categoryCode)) {
                $this->getDailyTalkshowInfo($content, $detailId);
                $contentType = 'talkshow';
            } elseif ($this->isForwardTalkshowCategory($categoryCode)) {
                $this->getForwardTalkshowInfo($content, $detailId);
                $contentType = 'talkshow';
            } elseif ($isCourseCategory) {
                if (!empty($feed) && !empty($feed['access_level'])) {
                    $feedAccessLevel = $feed['access_level'];
                }
                $this->getCourseVideo($content, $detailId, $accessCodeList, $feedAccessLevel);
                $contentType = 'course';
            } elseif ($this->isArticleCategory($categoryCode)) {
                $this->getArticleInfo($content, $detailId);
                $contentType = 'article';
            } elseif ($this->isTwitterCategory($categoryCode)) {
                $this->getTwitterInfo($content, $detailId);
                $contentType = 'twitter';
            } elseif ($this->isNewsCategory($categoryCode)) {
                $this->getNewsInfo($content, $detailId);
                $contentType = 'news';
            } elseif ($this->isStockReport($categoryCode)) {
                $this->getStockReportInfo($content, $detailId);
                $contentType = 'stock_report';
            } elseif ($this->isKitReport($categoryCode)) {
                $this->getKitReportInfo($content, $detailId);
                $contentType = "kit_report";
            } elseif (!empty($detailId)) {
                if (!empty($feed) && !empty($feed['access_level'])) {
                    $feedAccessLevel = $feed['access_level'];
                }
                try {
                    $this->getTalkshowInfo($content, $detailId, $accessCodeList, $feedAccessLevel);
                } catch (MatrixException $e) {
                    $feedFlag = true;
                }
                $contentType = 'talkshow';
            }

            if ($feedFlag) {
                if (empty($feed)) {
                    throw new MatrixException('内容没有找到', CONTENT_NOT_FOUND);
                }
                $content['category_key'] = $feed['category_key'];
                $content['media_type'] = $feed['msg_type'];
                $content['title'] = $feed['title'];
                $content['summary'] = $feed['summary'];
                $content['source_url'] = $this->fitDetailUrl((string)$feed['source_url']);
                $content['thumb_cdn_url'] = $this->fitDetailUrl((string)$feed['thumb_cdn_url']);
                $content['thumb_local_url'] = $this->fitDetailUrl((string)$feed['thumb_local_url']);
                $content['access_level'] = $feed['access_level'];
                $content['add_time'] = $feed['add_time'];
                $content['feed_type'] = $feed['feed_type'];

                if (!empty($feed['owner_id'])) {
                    $authorUserData = $this->user->getUserByEnterpriseUserId($feed['owner_id']);
                    $authorUser = array_get($authorUserData, 'data');

                    $content['owner_id'] = $feed['owner_id'];
                    $content['owner_name'] = (string)array_get($authorUser, 'name');
                    $content['owner_avatar'] = (string)array_get($authorUser, 'icon_url');
                }
            }

            if (!empty($feed) && !empty($feed['category_key'])) {
                // 根据 feed_id 查询 详情 需要根据 feed 表中 category_key 返回数据
                $content['category_key'] = $feed['category_key'];
            }

            try {
                // 1. 根据 feed_id 查询 详情 需要根据 feed 表中 category_key 返回对应数据
                // 2. 根据 detail_id 查询， 走 tj_wx_send_log_detail 表 查询数据时，需要根据 tj_wx_send_log_detail 表中记录的 category 返回 category数据以及权限字段
                $category = $this->category->getCategoryInfoByCode((string)array_get($content, 'category_key'));
                $content['category_name'] = (string)$category['name'];
                $content['category_ad_url'] = (string)array_get($category, 'ad_image_url');
                $content['access_level'] = (string)array_get($category, 'service_key');
            } catch (MatrixException $e) {
            }

            if (!empty($feed) && !empty($feed['access_level'])) {
                $content['access_level'] = (string)$feed['access_level'];
            }

            if (array_key_exists('category_ad_url', $content)) {
                $content['category_ad_url'] = $this->fitDetailUrl($content['category_ad_url']);
            }

            if (array_key_exists('access_level', $content)) {
                $content['access_deny'] = (int)!in_array($content['access_level'], $accessCodeList);
            }

            $this->jumpSettingMask($content);

            $this->getLikeNumAndReplyList($content, $contentType, $openId, $userId);

            if (isset($contentType)) {
                $content['type'] = $contentType;
            }

            if (array_key_exists('detail_id', $content)) {
                $content['detail_id'] = (string)$content['detail_id'];
            }
            if (array_key_exists('content_id', $content)) {
                $content['content_id'] = (string)$content['content_id'];
            }

            $ret = [
                'code' => SYS_STATUS_OK,
                'msg' => 'success',
                'data' => [
                    $content,
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
                'msg' => '发生了一个不可预知的错误',
            ];
        }

        return $ret;
    }

    public function getCategoryInfo(array &$content, array $accessCodeList)
    {
        try {
            $category = $this->category->getCategoryInfoByCode((string)array_get($content, 'category_key'));
            $content['category_name'] = (string)array_get($category, 'name');
            $content['category_ad_url'] = (string)array_get($category, 'ad_image_url');
            $content['access_level'] = (string)array_get($category, 'service_key');
            $content['access_deny'] = (int)!in_array($content['access_level'], $accessCodeList);
        } catch (MatrixException $e) {
        }
    }

    public function getContentInfoByReplyId()
    {
        $credentials = $this->request->validate([
            'reply_id' => 'required|string',
        ]);

        try {
            $replyId = array_get($credentials, 'reply_id');

            $sessionId = $this->request->header('X-SessionId');
            if (empty($sessionId)) {
                $sessionId = $this->request->cookie('X-SessionId');
            }

            if (empty($sessionId)) {
                throw new PermissionException('尚未登录', SYS_STATUS_PERMISSION_ERROR);
            }

            $ucUserInfo = $this->ucenter->getUserInfoBySessionId($sessionId);
            $openId = (string)array_get($ucUserInfo, 'data.user.openId');
            //$accessCodeList = $this->ucenter->getAccessCodeByOpenId($openId, 'default', true);
            $accessCodeList = array_get($ucUserInfo, 'data.user.accessCodes', []); 

            $qyUserId = (string)array_get($ucUserInfo, 'data.user.qyUserId');
            $userInfo = $this->user->getUserByEnterpriseUserId($qyUserId);
            $userId = (int)array_get($userInfo, 'data.id');

            $content = [];

            $reply = $this->interaction->getReplyInfo($replyId);

            if (empty($reply)) {
                throw new MatrixException('评论没有找到', REPLY_NOT_FOUND);
            }

            $content['detail_id'] = $reply['article_id'];
            $contentType = (string)array_get($reply, 'type');

            switch ($contentType) {
                case 'course':
                    // 获取对应的 category_key
                    $videoData = $this->video->getVideoSigninInfo($reply['article_id']);
                    $video = array_get($videoData, 'data');
                    
                    $courseVideoData = $this->courseVideo->getCourseVideoInfo(0, $video['id']);
                    $courseVideo = array_get($courseVideoData, 'data.course_video_info');

                    $courseData = $this->course->getCourseInfoByCode($courseVideo['course_code']);
                    $course = array_get($courseData, 'data');

                    $content['category_key'] = $course['course_system_code'];

                    // 获取 category信息
                    $this->getCategoryInfo($content, $accessCodeList);

                    $feed = $this->feed->getFeedInfoByCategoryAndSourceId($content['category_key'], $reply['article_id']);

                    if (!empty($feed)) {
                        $content['access_level'] = (string)array_get($feed, 'access_level');
                        $content['access_deny'] = (int)!in_array($content['access_level'], $accessCodeList);
                    }

                    $this->getCourseVideo($content, $reply['article_id'], $accessCodeList, '');
                    break;
                case 'article':
                    // 获取对应的 category_key
                    $articleData = $this->article->getArticleInfo($reply['article_id']);
                    $article = array_get($articleData, 'data.article');

                    $content['category_key'] = $article['category_code'];

                    // 获取 category信息
                    $this->getCategoryInfo($content, $accessCodeList);

                    $feed = $this->feed->getFeedInfoByCategoryAndSourceId($content['category_key'], $reply['article_id']);

                    if (!empty($feed)) {
                        $content['access_level'] = (string)array_get($feed, 'access_level');
                        $content['access_deny'] = (int)!in_array($content['access_level'], $accessCodeList);
                    }

                    $this->getArticleInfo($content, $reply['article_id']);
                    break;
                case 'twitter':
                    // 获取对应的 category_key
                    $twitterInfo = $this->twitter->getTwitterInfo($reply['article_id']);

                    $content['category_key'] = $twitterInfo['category_code'];

                    // 获取 category信息
                    $this->getCategoryInfo($content, $accessCodeList);

                    $feed = $this->feed->getFeedInfoByCategoryAndSourceId($content['category_key'], $reply['article_id']);

                    if (!empty($feed)) {
                        $content['access_level'] = (string)array_get($feed, 'access_level');
                        $content['access_deny'] = (int)!in_array($content['access_level'], $accessCodeList);
                    }

                    $this->getTwitterInfo($content, $reply['article_id']);
                    break;
                case 'news':
                    // 获取对应的 category_key
                    $content['category_key'] = 'news_stock_a';

                    // 获取 category信息
                    $this->getCategoryInfo($content, $accessCodeList);

                    $feed = $this->feed->getFeedInfoByCategoryAndSourceId($content['category_key'], $reply['article_id']);

                    if (!empty($feed)) {
                        $content['access_level'] = (string)array_get($feed, 'access_level');
                        $content['access_deny'] = (int)!in_array($content['access_level'], $accessCodeList);
                    }

                    $this->getNewsInfo($content, $reply['article_id']);
                    break;
                case 'stock_report':
                    // 获取对应的 category_key
                    $content['category_key'] = 'cyzb';

                    // 获取 category信息
                    $this->getCategoryInfo($content, $accessCodeList);

                    $feed = $this->feed->getFeedInfoByCategoryAndSourceId($content['category_key'], $reply['article_id']);

                    if (!empty($feed)) {
                        $content['access_level'] = (string)array_get($feed, 'access_level');
                        $content['access_deny'] = (int)!in_array($content['access_level'], $accessCodeList);
                    }

                    $this->getStockReportInfo($content, $reply['article_id']);
                    break;
                case 'kit_report':
                    // 获取对应的 category_key
                    $kitReportInfo = $this->kitReport->getKitReportInfoByKitReportId($reply['article_id']);
                    $content['category_key'] = $kitReportInfo['kit_code'];

                    // 获取 category信息
                    $this->getCategoryInfo($content, $accessCodeList);

                    $feed = $this->feed->getFeedInfoByCategoryAndSourceId($content['category_key'], $reply['article_id']);

                    if (!empty($feed)) {
                        $content['access_level'] = (string)array_get($feed, 'access_level');
                        $content['access_deny'] = (int)!in_array($content['access_level'], $accessCodeList);
                    }

                    $this->getKitReportInfo($content, $reply['article_id']);
                    break;
                case 'talkshow':
                    if (is_numeric($reply['article_id'])) {
                        // 获取对应的 category_key
                        $talkshowInfo = $this->feed->getTjWxSendLogDetail((int)$reply['article_id']);
                        $content['category_key'] = (string)$talkshowInfo['category'];

                        // 获取 category信息
                        $this->getCategoryInfo($content, $accessCodeList);

                        $feed = $this->feed->getFeedInfoByCategoryAndSourceId($content['category_key'], $reply['article_id']);

                        if (!empty($feed)) {
                            $content['access_level'] = (string)array_get($feed, 'access_level');
                            $content['access_deny'] = (int)!in_array($content['access_level'], $accessCodeList);
                        }

                        if (!empty($feed) && !empty($feed['access_level'])) {
                            $feedAccessLevel = $feed['access_level'];
                        }

                        $this->getTalkshowInfo($content, $reply['article_id'], $accessCodeList, $feedAccessLevel);
                    } else {
                        // 获取对应的 category_key
                        $content['category_key'] = 'forward_talkshow';

                        // 获取 category信息
                        $this->getCategoryInfo($content, $accessCodeList);

                        $feed = $this->feed->getFeedInfoByCategoryAndSourceId($content['category_key'], $reply['article_id']);

                        if (!empty($feed)) {
                            $content['access_level'] = (string)array_get($feed, 'access_level');
                            $content['access_deny'] = (int)!in_array($content['access_level'], $accessCodeList);
                        }

                        $this->getForwardTalkshowInfo($content, $reply['article_id']);
                    }
                    break;
                default:
                    throw new MatrixException('未知的类型', SYS_STATUS_ERROR_UNKNOW);
                    break;
            }

            if (array_key_exists('category_ad_url', $content)) {
                $content['category_ad_url'] = $this->fitDetailUrl($content['category_ad_url']);
            }

            $this->jumpSettingMask($content);

            $this->getLikeNumAndReplyList($content, $contentType, $openId, $userId);

            if (isset($contentType)) {
                $content['type'] = $contentType;
            }

            if (array_key_exists('detail_id', $content)) {
                $content['detail_id'] = (string)$content['detail_id'];
            }
            if (array_key_exists('content_id', $content)) {
                $content['content_id'] = (string)$content['content_id'];
            }

            $ret = [
                'code' => SYS_STATUS_OK,
                'msg' => 'success',
                'data' => [
                    $content,
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
                'msg' => '发生了一个不可预料的错误',
            ];
        }

        return $ret;
    }

    public function getKgsTwitterInfo()
    {
        $credentials = $this->request->validate([
            'source_id' => 'required|string',
        ]);

        try {
            $sessionId = $this->request->header('X-SessionId');
            if (empty($sessionId)) {
                $sessionId = $this->request->cookie('X-SessionId');
            }

            if (empty($sessionId)) {
                throw new PermissionException('尚未登录', SYS_STATUS_PERMISSION_ERROR);
            }

            $ucUserInfo = $this->ucenter->getUserInfoBySessionId($sessionId);
            $openId = (string)array_get($ucUserInfo, 'data.user.openId');
            //$accessCodeList = $this->ucenter->getAccessCodeByOpenId($openId, 'default', true);
            $accessCodeList = array_get($ucUserInfo, 'data.user.accessCodes', []); 

            $qyUserId = (string)array_get($ucUserInfo, 'data.user.qyUserId');
            $userInfo = $this->user->getUserByEnterpriseUserId($qyUserId);
            $userId = (int)array_get($userInfo, 'data.id');

            $content = [];

            $sourceId = array_get($credentials, 'source_id');
            $this->getTwitterInfoBySourceId($content, $sourceId);
            $contentType = 'twitter';

            try {
                $category = $this->category->getCategoryInfoByCode((string)$content['category_key']);
                $content['category_name'] = (string)$category['name'];
                $content['category_ad_url'] = (string)array_get($category, 'ad_image_url');
                $content['access_level'] = (string)array_get($category, 'service_key');
                $content['access_deny'] = (int)!in_array($content['access_level'], $accessCodeList);
            } catch (MatrixException $e){
            }

            if (array_key_exists('category_ad_url', $content)) {
                $content['category_ad_url'] = $this->fitDetailUrl($content['category_ad_url']);
            }

            if (array_key_exists('access_level', $content)) {
                $content['access_deny'] = (int)!in_array($content['access_level'], $accessCodeList);
            }

            if ($content['access_deny'] == 1) {
                $content['summary'] = preg_replace("/(\x{ff08}[\x{4e00}-\x{9fa5}a-zA-Z\s+]*\d{5,6}[\x{4e00}-\x{9fa5}a-zA-Z\s+]*\s*\x{ff09})/u","（金股***）", $content['summary']);
                $content['summary'] = sprintf('%s...', mb_substr($content['summary'], 0 , 50));
                $content['guide_media'] = 'ad';
            }

            $this->jumpSettingMask($content);

            $this->getLikeNumAndReplyList($content, $contentType, $openId, $userId);

            if (isset($contentType)) {
                $content['type'] = $contentType;
            }

            if (array_key_exists('detail_id', $content)) {
                $content['detail_id'] = (string)$content['detail_id'];
            }
            if (array_key_exists('content_id', $content)) {
                $content['content_id'] = (string)$content['content_id'];
            }

            $ret = [
                'code' => SYS_STATUS_OK,
                'msg' => 'success',
                'data' => [
                    $content,
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
                'msg' => '发生了一个不可预知的错误',
            ];
        }

        return $ret;
    }

    public function getLikeNumAndReplyList (array &$content, string $contentType, string $openId, int $userId) {
        $contentId = array_get($content, 'content_id');
        if (!empty($contentId) && isset($contentType)) { // 去拿赞评数据
            $voteData = $this->interaction->getLikeRecord($contentId, $contentType, $openId);
            $content['is_vote'] = (int)array_get($voteData, 'data.like');
            $voteCntData = $this->interaction->getLikeSum($contentId, $contentType);
            $content['vote_cnt'] = (int)array_get($voteCntData, 'data.statisticInfo.like_sum');
            $content['reply_cnt'] = $this->interaction->getReplyCnt($contentType, $contentId);
            $replyListData = $this->interaction->getReplyListOfClient($contentType, $contentId, ArticleReply::STATUS_APPROVE, $openId, 0, 30, 1);
            $replyList = array_get($replyListData, 'reply_list');
            $this->getReplyInfoList($replyList, $openId, $userId);

            $appVersion = self::getAPPVersion($this->agent->getUserAgent());
            if (version_compare($appVersion, '2.8.*') < 0) {
                $content['reply_list'] = $replyList;
            }else{
                $refReplyList = array_get($replyListData, 'ref_reply_list');
                $this->getReplyInfoList($refReplyList, $openId, $userId);
                // dd($refReplyList);
                foreach ($replyList as &$reply) {
                    foreach ($refReplyList as $refReply) {
                        if ($reply['id'] === $refReply['ref_id']) {
                            $reply['ref_content_list'][] = $refReply;
                        }
                    }
                }
                $content['reply_list'] = $replyList;
            }
        }
    }

    public function getReplyInfoList (array &$replyList, string $openId, int $userId) {
        if (!empty($replyList)) {
            $customerOpenIdList = array_column($replyList, 'open_id');
            $customerRefOpenIdList = array_column($replyList, 'ref_open_id');
            $customerAllOpenIdList = array_merge($customerOpenIdList, $customerRefOpenIdList);
            $customerAllOpenIdList = array_unique($customerAllOpenIdList);

            // 获取对应的点赞状态
            $replyIdList = array_column($replyList, 'id');
            $replyLikeList = $this->interaction->getLikeRecordList($replyIdList, self::REPLY_TYPE, $openId);
            $replyIdOfLike = array_column($replyLikeList, 'article_id');

            // 获取对应评论的点赞总数
            $replyLikeSumList = $this->interaction->getLikeSumList($replyIdList, self::REPLY_TYPE);
            $likeSumOfReplyId = array_column($replyLikeSumList, NULL, 'article_id');

            $customerList = $this->customer->getCustomerList($customerAllOpenIdList);
            $customerMap = array_column($customerList, NULL, 'open_id');

            $supermanUserList = $this->user->getUserListByGroupCode(self::USER_GROUP_CODE_SUPERMAN_TAG);
            $supermanUserIdList = array_column($supermanUserList, 'id');
            $supermanUcList = $this->user->getUcListByUserIdList($supermanUserIdList);
            $supermanQyUseridList = array_column($supermanUcList, 'enterprise_userid');

            foreach ($replyList as &$reply) {
                $customer = array_get($customerMap, $reply['open_id']);
                if (!empty($customer)) {
                    $reply['nickname'] = $customer['name'];
                    if (!empty($openId) && $openId == $customer['open_id']) {
                        $reply['nickname'] .= '(我)';
                    }
                    $reply['icon_url'] = $customer['icon_url'];

                    if (!empty($supermanQyUseridList) && in_array($customer['qy_userid'], $supermanQyUseridList)) {
                        $reply['is_teacher'] = 1;
                        $reply['teacher_qy_userid'] = $customer['qy_userid'];
                    } else {
                        $reply['is_teacher'] = 0;
                    }
                }

                // ZYAPP-840 修改is_auth字段逻辑
                if (!empty($userId) && $reply['article_author_user_id'] === $userId) {
                    $reply['is_auth'] = 1;
                } else {
                    $reply['is_auth'] = 0;
                }

                // 本人是否店在哪
                if (in_array($reply['id'], $replyIdOfLike)) {
                    $reply['is_like'] = 1;
                } else {
                    $reply['is_like'] = 0;
                }

                // 点赞总数
                if (empty($likeSumOfReplyId[$reply['id']])) {
                    $reply['like_sum'] = 0;
                } else {
                    $likeSum = (int)array_get($likeSumOfReplyId[$reply['id']], 'like_sum');
                    $reply['like_sum'] = $likeSum;
                }

                if (!empty($reply['ref_open_id'])) {
                    $refCustomer = array_get($customerMap, $reply['ref_open_id']);
                    $reply['ref_nickname'] = $refCustomer['name'];
                    if (!empty($openId) && $openId == $refCustomer['open_id']) {
                        $reply['ref_nickname'] .= '(我)';
                    }
                    $reply['ref_icon_url'] = $refCustomer['icon_url'];

                    if (!empty($supermanQyUseridList) && in_array($refCustomer['qy_userid'], $supermanQyUseridList)) {
                        $reply['ref_is_teacher'] = 1;
                        $reply['ref_teacher_qy_userid'] = $refCustomer['qy_userid'];
                    } else {
                        $reply['ref_is_teacher'] = 0;
                    }
                }

                $reply['send_time_text'] = '';
                if (!empty($reply['created_at'])) {
                    $sendTimeStamp = strtotime($reply['created_at']);
                    $nowTime = time();
                    $diffTime = $nowTime - $sendTimeStamp;
                    if ($diffTime < 60) { // 一分钟内
                        $reply['send_time_text'] = '刚刚';
                    } elseif ($diffTime <= 3600) { // 一小时内
                        $reply['send_time_text'] = intval($diffTime / 60).'分钟前';
                    } elseif ($diffTime <= 86400) { // 一天内
                        $reply['send_time_text'] = intval($diffTime / 3600).'小时前';
                    } else { // 大于一天
                        $oneYearAgoTime = strtotime('-1 year');
                        if ($sendTimeStamp >= $oneYearAgoTime) { // 一年之内
                            $reply['send_time_text'] = date('m月d日 H:i', $sendTimeStamp);
                        } else { // 一年以上
                            $reply['send_time_text'] = date('Y年m月d日 H:i', $sendTimeStamp);
                        }
                    }
                }
                unset($reply['session_id']);
            }
        }
    }
}
