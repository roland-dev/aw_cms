<?php

namespace Matrix\Http\Controllers\Client;

use Matrix\Exceptions\MatrixException;
use Log;
use Exception;
use Matrix\Contracts\CategoryGroupManager;
use Matrix\Contracts\CategoryManager;
use Matrix\Contracts\ContentGuardContract;
use Matrix\Contracts\CourseManager;
use Matrix\Contracts\CourseSystemManager;
use Matrix\Contracts\CourseVideoManager;
use Matrix\Contracts\UserManager;
use Matrix\Contracts\VideoManager;

class VideoController extends Controller
{
  //
  const DAPANFENXI_CATEGROY_CATEGORY_TYPE = 'allwin_dapanfenxi';
  const SELECT_VIDEO_DAPANFENXI_DAY = 7;

  private $courseVideoManager;
  private $courseManager;
  private $courseSystemManager;
  private $videoManager;
  private $contentGuardContract;
  private $categoryGroupManager;
  private $categoryManager;
  private $userManager;

  public function __construct(
    CourseVideoManager $courseVideoManager,
    CourseManager $courseManager,
    CourseSystemManager $courseSystemManager,
    VideoManager $videoManager,
    ContentGuardContract $contentGuardContract,
    CategoryGroupManager $categoryGroupManager,
    CategoryManager $categoryManager,
    UserManager $userManager
  )
  {
    $this->courseVideoManager = $courseVideoManager;
    $this->courseManager = $courseManager;
    $this->courseSystemManager = $courseSystemManager;
    $this->videoManager = $videoManager;
    $this->contentGuardContract = $contentGuardContract;
    $this->categoryGroupManager = $categoryGroupManager;
    $this->categoryManager = $categoryManager;
    $this->userManager = $userManager;

  }

  public function urlFilter(string $url)
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

        return sprintf('%s%s', config('app.h5_api_url'), $url);
    }


  public function getAllVideoList()
  {
    try {
      // 获取 课程视频 列表
      $courseVideoList = $this->courseVideoManager->getVideoList();

      $videoIdList = array_column($courseVideoList, 'video_signin_id');
      $videoList = $this->videoManager->getVideoListByVideoIds($videoIdList);
      $videoList = array_get($videoList, 'videoSigninList');
      $videoList = array_column($videoList, NULL, 'id');

      $courseCodeList = array_column($courseVideoList, 'course_code');
      $courseList = $this->courseManager->getCourseListByCodeListOrderSort($courseCodeList);

      $courseAccessCodeList = $this->contentGuardContract->getCourseAccessCodeList();
      $courseSystemList = $this->courseSystemManager->getCourseSystemList();
      $courseSystemList = array_column($courseSystemList, NULL, 'code');

      $courseListRet = [];
      
      foreach ($courseList as $course) {
        $courseRes = [
          'is_grouping' => 1,
          'category_key' => $course['course_system_code'],
          'group_id' => $course['code'],
          'group_name' => $course['name'],
          'summary' => $course['description'],
          'poster' => $course['background_picture'],
          'group_articles' => [],
        ];
        foreach ($courseVideoList as $courseVideo) {
          if ($courseVideo['course_code'] != $course['code']) {
            continue;
          }

          $video = [
            'detail_id' => $videoList[$courseVideo['video_signin_id']]['video_key'],
            'column_name' => '学战法',
            'category_key' => $course['course_system_code'],
            'category_name' => array_get(array_get($courseSystemList, $course['course_system_code']), 'name'),
            'title' => $videoList[$courseVideo['video_signin_id']]['title'],
            'summary' => $videoList[$courseVideo['video_signin_id']]['description'],
            'media_type' => 'news',
            'thumb_cdn_url' => $this->urlFilter($courseVideo['picture_path']),
            'thumb_local_url' => '',
            'access_level' => array_get($courseAccessCodeList, array_get($course, 'code')),
            'add_time' => $videoList[$courseVideo['video_signin_id']]['created_at'],
            'tag' => $courseVideo['tag'],
            'jump_type' => 'common_web',
            'guide_media' => 'page',
          ];

          $video['source_url'] = sprintf('%s/api/v2/client/course/detail/%s', config('app.url'), $videoList[$courseVideo['video_signin_id']]['video_key']);
          $courseRes['group_articles'][] = $video;
        }
        $courseListRet[] = $courseRes;
      }

      // 获取 大盘分析 节目 列表
      $categoryListData = $this->categoryManager->getCategoryListByGroupCode(self::DAPANFENXI_CATEGROY_CATEGORY_TYPE, 1);
      $categoryList = array_get($categoryListData, 'data.category_list');
      $categoryCodeList = array_column($categoryList, 'code');

      $programList = $this->videoManager->getDapanfenxiVideoList($categoryCodeList, self::SELECT_VIDEO_DAPANFENXI_DAY);
      $categoryCodeList = array_column($programList, 'category');

      $categoryList = $this->categoryManager->getCategoryListByCodeList($categoryCodeList);
      $categoryCodeList = array_column($categoryList, 'code');

      $programListRet = [];
      foreach ($categoryList as $category) {
        $programRes = [
          'is_grouping' => 1,
          'category_key' => $category['code'],
          'category_name' => $category['name'],
          'group_articles' => [],
        ];

        foreach ($programList as $program) {
          if ($program['category'] != $category['code']) {
            continue;
          }

          $video = [
            'detail_id' => $program['detail_id'],
            'title' => $program['title'],
            'summary' => $program['digest'],
            'thumb_cdn_url' => $program['thumb_cdn_url'],
            'thumb_local_url' => $program['thumb_local_url'],
            'access_level' => $category['service_key'],
            'add_time' => date('Y-m-d H:i:s', $program['send_time']),
            'category_key' => $category['code'],
            'media_type' => $program['msg_type'],
            'file_size' => $program['file_size'],
            'column_name' => '大盘分析',
            'category_name' => $category['name'],
            'jump_type' => 'common_web',
            'guide_media' => $program['msg_type'],
          ];

          // 处理 title 当中的【】
          if (strpos(array_get($program, 'title'), '【') !== false && strpos(array_get($program, 'title'), '【') == 0) {
            $video['title'] = substr(array_get($program, 'title'), strpos(array_get($program, 'title'), '】') + 3);
          }

          if (strpos($program['title'], '[保密]') === 0 && strpos($program['content_url'], 'getsafemsg') !== false && $program['msg_type'] == 'news') {
            $video['source_url'] = sprintf('/getHistoryData.php?did=%s', $program['detail_id']);
          } elseif (in_array($program['msg_type'], ['file', 'pdf'])) {
              $video['source_url'] = $program['content_local_url'];
          } elseif (strpos($program['content_url'], 'open.work.weixin.qq.com') !== false && $program['content_local_url'] == '#local_data#') {
              $video['source_url'] = sprintf('/getHistoryData.php?did=%s', $program['detail_id']);
          } else {
              $video['source_url'] = $program['content_url'];
          }

          $video['source_url'] = $this->urlFilter($video['source_url']);
          if (!empty($program['owner_id'])) {
            $authorUserData = $this->userManager->getUserByEnterpriseUserId($program['owner_id']);
            $authorUser = array_get($authorUserData, 'data');

            $video['owner_id'] = (string)$program['owner_id'];
            $video['owner_user_id'] = (int)array_get($authorUser, 'id');
            $video['owner_name'] = (string)array_get($authorUser, 'name');
            $video['owner_avatar'] = (string)array_get($authorUser, 'icon_url');
          }

          $programRes['group_articles'][] = $video;
        }
        $programListRet[] = $programRes;
      }

      $ret = [
        'code' => SYS_STATUS_OK,
        'data' => [
          'course_list' => $courseListRet,
          'program_list' => $programListRet,
        ]
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
        'msg' => '未知错误'
      ];
    }

    return $ret;
  }

}