<?php

namespace Matrix\Http\Controllers;

use Illuminate\Http\Request;
use Matrix\Contracts\ArticleManager;
use Matrix\Contracts\CategoryManager;
use Matrix\Contracts\UserManager;
use Matrix\Contracts\BossManager;
use Exception; use Log;
use Auth;
use Matrix\Exceptions\MatrixException;

class ArticleController extends Controller
{
    const CATEGORY_ERROR = '5001';
    //
    private $request;
    private $articleManager;

    public function __construct (Request $request, ArticleManager $articleManager)
    {
        $this->request = $request;
        $this->articleManager = $articleManager;
    }

    public function attachCategoryToArticleList(array $articleList, array $categoryList, array $subCategoryList)
    {
        $categoryList = array_column($categoryList, 'name', 'code');
        $subCategoryList = array_column($subCategoryList, 'name', 'code');
        foreach ($articleList as &$article) {
            $article['category_name'] = $categoryList[$article['category_code']];
            $article['sub_category_name'] = $subCategoryList[$article['sub_category_code']];
        }

        return $articleList;
    }

    public function attachModifyUserNameToArticleList(array $articleList, array $userList)
    {
        $userList = array_column($userList, 'name', 'id');

        foreach ($articleList as &$article) {
            $article['modify_user_name'] = $userList[$article['modify_user_id']];
        }

        return $articleList;
    }

    protected function removeContentInArticleList(array $articleList)
    {
        foreach ($articleList as &$article) {
            unset($article['content']);
        }

        return $articleList;
    }

    public function getArticleList(CategoryManager $categoryManager, UserManager $userManager)
    {
        $reqData = $this->request->validate([
            'page_no' => 'nullable|integer',
            'page_size' => 'nullable|integer',
            'show' => 'nullable|integer',
            'category_code' => 'nullable|string',
            'sub_category_code' => 'nullable|string',
            'title' => 'nullable|string',
        ]);

        try {
            $pageNo = array_get($reqData, 'page_no', 1);
            $pageSize = array_get($reqData, 'page_size', 10);

            $articleList = $this->articleManager->getArticleList($pageNo, $pageSize, $reqData);
            $articleCnt = $this->articleManager->getArticleCnt($reqData);

            $categoryList = array_get($categoryManager->getCategoryList(), 'data.category_list');
            $subCategoryList = array_get($categoryManager->getSubCategoryList(), 'data.sub_category_list');
            $articleList = $this->attachCategoryToArticleList($articleList, $categoryList, $subCategoryList);

            $userList = $userManager->getAllUserList();
            $articleList = $this->attachModifyUserNameToArticleList($articleList, $userList);
            $articleList = $this->removeContentInArticleList($articleList);
            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'article_list' => $articleList,
                    'article_cnt' => $articleCnt,
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
                'msg' => '未知错误',
            ];
        }

        return $ret;
    }

    /**
    *获取推送到企业微信的栏目分类
    *
    *@return array
    */
    public function getCategoryOfPushQywx(CategoryManager $categoryManager)
    {
        try{
            $categoryListData = $categoryManager->getCategoryListByGroupCode('article_group_push_qywx');
            $categoryList = array_get($categoryListData, 'data.category_list');
            $categoryCodeList = array_column($categoryList, 'code');
            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => $categoryCodeList,
            ];
        }catch(Exception $e){
            Log::error($e->getMessage(), [$e]);
            $ret = ['code' => SYS_STATUS_ERROR_UNKNOW];
        }

        return $ret;
    }

    public function createArticle(CategoryManager $categoryManager)
    {
        $articleData = $this->request->validate([
            'category_code' => 'required',
            'sub_category_code' => 'required',
            'title' => 'required',
            'summary' => 'required',
            'content' => 'required',
            'audio_url' => 'string',
            'teacher_id' => 'required|integer',
            'show' => 'integer',
            'cover_url' => 'string|nullable',
            'ad_guide' => 'string|nullable',
            'is_push_qywx' => 'integer',
            'published_at' => 'required|date',
        ]);

        try {
            $articleData['content'] = str_replace(config('app.tencent_img_domain_name'), config('cdn.cdn_url'), array_get($articleData, 'content'));
            $articleData['content'] = str_replace('tp=webp', '', array_get($articleData, 'content'));
            $articleRes = $this->articleManager->createArticle($articleData);
            $categoryCode = array_get($articleData, 'category_code');
            $categoryInfo = $categoryManager->getCategoryInfoByCode($categoryCode);
            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'article' => array_get($articleRes, 'data.article'),
                ],
            ];
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = ['code' => SYS_STATUS_ERROR_UNKNOW];
        }

        return $ret;
    }

    public function updateArticleShow($articleId)
    {
        try {
            $articleInfoRes = $this->articleManager->updateArticleShow($articleId);
            $articleInfo = array_get($articleInfoRes, 'data.article');
            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'article' => $articleInfo,
                ],
            ];
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = ['code' => SYS_STATUS_ERROR_UNKNOW];
        }

        return $ret;
    }

    public function updateArticlePushQywx($articleId)
    {
        try {
            $articleInfoRes = $this->articleManager->updateArticlePushQywx($articleId);
            $articleInfo = array_get($articleInfoRes, 'data.article');
            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'article' => $articleInfo,
                ],
            ];
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = ['code' => SYS_STATUS_ERROR_UNKNOW];
        }

        return $ret;
    }


    public function updateArticle(CategoryManager $categoryManager, $articleId)
    {
        $articleData = $this->request->validate([
            'category_code' => 'string',
            'sub_category_code' => 'string',
            'title' => 'string',
            'summary' => 'string',
            'content' => 'string',
            'audio_url' => 'string',
            'teacher_id' => 'integer',
            'cover_url' => 'string|nullable',
            'ad_guide' => 'string|nullable',
            'published_at' => 'required|date',
        ]);

        try {
            $articleData['content'] = str_replace(config('app.tencent_img_domain_name'), config('cdn.cdn_url'), array_get($articleData, 'content'));
            $articleData['content'] = str_replace('tp=webp', '', array_get($articleData, 'content'));

            $articleInfoRes = $this->articleManager->updateArticle($articleId, $articleData);
            $articleInfo = array_get($articleInfoRes, 'data.article');

            $categoryCode = array_get($articleData, 'category_code');
            $categoryInfo = $categoryManager->getCategoryInfoByCode($categoryCode);

            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'article' => $articleInfo,
                ],
            ];
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = ['code' => SYS_STATUS_ERROR_UNKNOW];
        }

        return $ret;
    }

    public function trashArticle($articleId)
    {
        try {
            $trashArticleRes = $this->articleManager->trashArticle($articleId);
            $ret = [ 'code' => SYS_STATUS_OK ];
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = ['code' => SYS_STATUS_ERROR_UNKNOW];
        }

        return $ret;
    }

    public function getArticleInfo($articleId)
    {
        try {
            $articleInfoRes = $this->articleManager->getArticleInfo($articleId);
            $articleInfo = array_get($articleInfoRes, 'data.article');
            $articleInfo['source_url'] = sprintf('%s/api/v2/client/article/%s', config('app.h5_api_url'), array_get($articleInfo, 'id'));

            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'article' => $articleInfo,
                ],
            ];
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = ['code' => SYS_STATUS_ERROR_UNKNOW];
        }

        return $ret;
    }

    /**
    *文章管理预览功能
    *
    */
    public function previewArticle(CategoryManager $categoryManager, UserManager $userManager, BossManager $bossManager)
    {
        try{
            $articleData = $this->request->validate([
                'category_code' => 'required',
                'sub_category_code' => 'required',
                'title' => 'required',
                'summary' => 'required',
                'content' => 'required',
                'audio_url' => 'string',
                'teacher_id' => 'required|integer',
                'show' => 'integer',
                'cover_url' => 'string|nullable',
                'ad_guide' => 'string|nullable',
            ]);

            $categoryListDataOfPushQywx = $categoryManager->getCategoryListByGroupCode('article_group_push_qywx');
            $categoryListOfPushQywx = array_get($categoryListDataOfPushQywx, 'data.category_list');
            $categoryCodeListOfPushQywx = array_column($categoryListOfPushQywx, 'code');

            if(!in_array(array_get($articleData, 'category_code'), $categoryCodeListOfPushQywx)){//避免出现如果 不再推送分组里面，但>是发送文章时也勾选了推送qywx，这种情况
                $ret = [
                    'code' => self::CATEGORY_ERROR,
                    'msg' => '所选栏目暂不支持推送企业微信',
                ];

                return $ret;
            }

            $articleData['content'] = str_replace(config('app.tencent_img_domain_name'), config('cdn.cdn_url'), array_get($articleData, 'content'));
            $articleData['content'] = str_replace('tp=webp', '', array_get($articleData, 'content'));
            $categoryCode = array_get($articleData, 'category_code');

            $categoryInfo = $categoryManager->getCategoryInfoByCode($categoryCode);
            $userId = Auth::user()->id;
            //$userId = 25;
            $teacherIdList = [array_get($articleData, 'teacher_id')];

            $teacherListData = $categoryManager->getTeacherListByIdList($teacherIdList);
            $teacherList = array_get($teacherListData, 'data.teacher_list');
            $userIdList = array_column($teacherList, 'user_id');
            $ucList = $userManager->getUcListByUserIdList($userIdList);
            $ucList = array_column($ucList, 'enterprise_userid', 'user_id');

            $teacherList = array_column($teacherList, NULL, 'id');

            $userInfo = $userManager->getUserInfo($userId);

            //if(strpos(array_get($articleData, 'cover_url'), 'http') === false){
            //    $imageUrl =  str_replace('//', 'http://', array_get($articleData, 'cover_url'));
            //}

            //if(strpos(array_get($articleData, 'cover_url'), 'https') === false){
            //    $imageUrl =  str_replace('//', 'https://', array_get($articleData, 'cover_url'));
            //}

            $msgData = [
                'owner' => $teacherList[$articleData['teacher_id']]['name'], // teacher name,
                'feed_type' => 12,// twitter=11, article=12
                'type' => array_get($articleData, 'category_code'), // category_code 
                'owner_id' => $ucList[$teacherList[$articleData['teacher_id']]['user_id']], // teacher.qy_userid,
                'title' => array_get($articleData, 'title'),
                'description' => array_get($articleData, 'summary'),
                'thumb_cdn_url' => array_get($articleData, 'cover_url'),
                'origin_image_url' => array_get($articleData, 'cover_url'),
                'content' => array_get($articleData, 'content', ''),//内容已经是经过处理之后的内容，boss端即便不处理也不会有问题
                'category_code' => array_get($articleData, 'category_code'),
                'user_enterprise_id' => array_get($userInfo, 'ucInfo.enterprise_userid', ''),
                'url' => '',
                'preview' => 1,
                'is_wechat_push' => 1,
                'is_app_push' => 0,
            ];
            //dd($msgData);
            $ret = $bossManager->pushArticleToQywx($msgData);
            Log::info(json_encode($ret));

        }catch(Exception $e){
            Log::error($e->getMessage(), [$e]);
            $ret = ['code' => SYS_STATUS_ERROR_UNKNOW];
        }

        return $ret;
    }
}
