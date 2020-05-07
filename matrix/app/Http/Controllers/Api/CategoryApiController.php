<?php

namespace Matrix\Http\Controllers\Api;

use Illuminate\Http\Request;
use Matrix\Contracts\TwitterManager;
use Matrix\Contracts\CategoryManager;
use Matrix\Models\TwitterGuard;
use Cache;

class CategoryApiController extends Controller
{
    private $request;
    private $twitterManager;
    private $categoryManager;

    public function __construct(Request $request, CategoryManager $categoryManager, TwitterManager $twitterManager)
    {
        $this->request = $request;
        $this->twitterManager = $twitterManager;
        $this->categoryManager = $categoryManager;
    }

    public function getCategoryInfo(string $categoryCode)
    {
        $sessionId = $this->request->header('X-SessionId');
        $session = [];
        if (!empty($sessionId)) {
            $session = Cache::get($sessionId);
        }

        $openId = array_get($session, 'open_id');
        $categoryInfoData = $this->categoryManager->getCategoryInfo($categoryCode, (string)$openId);
        $categoryInfo = array_get($categoryInfoData, 'data.category_info');

        $teacherListData = $this->categoryManager->getTeacherListByCategoryCode($categoryCode, (string)$openId);
        $teacherList = array_get($teacherListData, 'data.teacher_list');

        foreach ($teacherList as &$teacher) {
            $teacher['icon_url'] = $this->fitDetailUrl(array_get($teacher, 'icon_url'), $this->request);
        }

        $categoryInfo['teacher_list'] = $teacherList;

        foreach ($teacherList as $teacher) {
            if ($teacher['primary'] == 1) {
                $categoryInfo['video_url'] = empty($openId) ? $teacher['visitor_video_url'] : $teacher['customer_video_url'];
            }
        }

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => $categoryInfo,
        ];

        return $ret;
    }

    public function getCategoryListByGroupCode(string $categoryGroupCode)
    {
        $categoryListData = $this->categoryManager->getCategoryListByGroupCode($categoryGroupCode);
        $categoryList = array_get($categoryListData, 'data.category_list');

        $categoryCodeList = array_column($categoryList, 'code');
        $teacherListData = $this->categoryManager->getTeacherListByCategoryCodeList($categoryCodeList);
        $teacherList = array_get($teacherListData, 'data.teacher_list');

        foreach ($categoryList as &$category) {
            if (!array_key_exists('teacher_list', $category)) {
                $category['teacher_list'] = [];
            }
            foreach ($teacherList as $teacher) {
                if (!in_array($teacher, $category['teacher_list']) && $teacher['category_code'] == $category['code']) {
                    $teacher['icon_url'] = $this->fitDetailUrl(array_get($teacher, 'icon_url'), $this->request);
                    $category['teacher_list'][] = $teacher;
                }
            }
        }

        $sessionId = $this->request->header('X-SessionId');
        if (!empty($sessionId)) {
            $session = Cache::get($sessionId);
            $openId = array_get($session, 'open_id');
        }
        if (!empty($openId)) {
            $twitterGuardList = [];
            $twitterRequestListData = $this->twitterManager->getTwitterRequestList([
                'status' => [0, 1, 2],
                'open_id' => [$openId],
            ]);
            $twitterRequestList = array_get($twitterRequestListData, 'data.twitter_request_list');
            foreach ($twitterRequestList as $twitterRequest) {
                if (array_key_exists($twitterRequest['category_code'], $twitterGuardList)) {
                    continue;
                }
                if (TwitterGuard::STATUS_REQUEST == $twitterRequest['status'] && TwitterGuard::SOURCE_AUTO_PROGRAM == $twitterRequest['source_type']) {
                    $twitterGuardList[$twitterRequest['category_code']] = TwitterGuard::STATUS_APPROVE;
                } else {
                    $twitterGuardList[$twitterRequest['category_code']] = $twitterRequest['status'];
                }
            }

            $categoryListFollowed = [];
            $categoryListRejected = [];
            $categoryListRequest  = [];
            $categoryListUnFollow = [];

            foreach ($categoryList as &$category) {
                if (array_key_exists($category['code'], $twitterGuardList)) {
                    $category['follow'] = $twitterGuardList[$category['code']];
                    switch ($twitterGuardList[$category['code']]) {
                        case TwitterGuard::STATUS_REQUEST:
                            $categoryListRequest[] = $category;
                            break;
                        case TwitterGuard::STATUS_APPROVE:
                            $categoryListFollowed[] = $category;
                            break;
                        case TwitterGuard::STATUS_REJECT:
                            $categoryListRejected[] = $category;
                            break;
                    }
                } else {
                    $categoryListUnFollow[] = $category;
                }
            }

            $categoryList = array_merge($categoryListFollowed, $categoryListRequest, $categoryListRejected, $categoryListUnFollow);
        }

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'category_list' => $categoryList,
            ],
        ];

        return $ret;
    }
}

