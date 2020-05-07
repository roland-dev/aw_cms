<?php

namespace Matrix\Http\Controllers\Api;

use Illuminate\Http\Request;
use Matrix\Contracts\CategoryManager;
use Matrix\Contracts\ArticleManager;
use Matrix\Contracts\UcManager;
use Cache;
use Exception;
use Log;

class ArticleApiController extends Controller
{
    //
    private $request;
    private $categoryManager;
    private $articleManager;
    private $ucenter;

    public function __construct(Request $request, CategoryManager $categoryManager, ArticleManager $articleManager, UcManager $ucenter)
    {
        $this->request = $request;
        $this->categoryManager = $categoryManager;
        $this->articleManager = $articleManager;
        $this->ucenter = $ucenter;
    }

    public function getHsggListData(string $categoryCode)
    {
        $sessionId = $this->request->header('X-SessionId');
        $openId = '';
        if (!empty($sessionId)) {
            $session = Cache::get($sessionId);
            $openId = array_get($session, 'open_id');
        }
        $subCategoryListData = $this->categoryManager->getActiveSubCategoryListByCategoryCode($categoryCode);
        $subCategoryList = array_get($subCategoryListData, 'data.sub_category_list');
        $teacherListData = $this->categoryManager->getTeacherListByCategoryCode($categoryCode);
        $teacherList = array_get($teacherListData, 'data.teacher_list');

        foreach ($teacherList as &$teacher) {
            $teacher['icon_url'] = $this->fitDetailUrl(array_get($teacher, 'icon_url'), $this->request);
        }

        $categoryInfoData = $this->categoryManager->getCategoryInfo($categoryCode, (string)$openId);

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => array_get($categoryInfoData, 'data.category_info'),
        ];

        $ret['data']['sub_category_list'] = $subCategoryList;
        $ret['data']['teacher_list'] = $teacherList;

        foreach ($ret['data']['sub_category_list'] as &$subCategory) {
            $articleListData = $this->articleManager->getActiveArticleListBySubCategoryCode($subCategory['code']);
            $articleList = array_get($articleListData, 'data.article_list');
            $subCategory['article_list'] = $articleList;
        }

        return $ret;
    }

    public function read($articleId)
    {
        $sessionId = $this->request->header('X-SessionId');
        $openId = '';
        if (!empty($sessionId)) {
            $session = Cache::get($sessionId);
            $openId = array_get($session, 'open_id');
        }
        try {
            $articleData = $this->articleManager->readArticle($articleId, $openId);
            $article = array_get($articleData, 'data.article');
            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'article' => $article,
                ],
            ];
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = ['code' => SYS_STATUS_ERROR_UNKNOW];
        }

        return $ret;
    }

    public function like($articleId)
    {
        $openId = '';
        $udid = '';
        $userType = '';

        $sessionId = $this->request->header('X-SessionId');
        if (!empty($sessionId)) {
            $session = Cache::get($sessionId);
            $openId = array_get($session, 'open_id');

            $currentUserInfo = $this->ucenter->getUserInfoBySessionId($session);

            $userType = array_get($currentUserInfo, 'data.user.roleCode');
        } else {
            $udid = $this->request->input('udid');
        }

        try {
            $articleData = $this->articleManager->likeArticle($articleId, $openId, $udid, $sessionId, $userType);

            $likeStatus = array_get($articleData, 'data.like');

            $ret = [];
            $ret['code'] = $likeStatus == 2 ? SYS_STATUS_ERROR_UNKNOW : SYS_STATUS_OK;
            $ret['data'] = [
                'article_id' => $articleId,
                'like' => array_get($articleData, 'data.like'),
            ];

        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = ['code' => SYS_STATUS_ERROR_UNKNOW];
        }

        return $ret;
    }

    public function getArticleInfo($articleId)
    {
        $openId = '';
        $udid = '';

        $sessionId = $this->request->header('X-SessionId');
        if (!empty($sessionId)) {
            $session = Cache::get($sessionId);
            $openId = array_get($session, 'open_id');
        } else {
            $udid = $this->request->input('udid');
        }

        try {
            $articleInfoRes = $this->articleManager->getArticleInfo($articleId, $openId, $udid);
            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'article' => array_get($articleInfoRes, 'data.article'),
                ],
            ];
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = ['code' => SYS_STATUS_ERROR_UNKNOW];
        }

        return $ret;
    }
}
