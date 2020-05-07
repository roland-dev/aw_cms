<?php
namespace Matrix\Services;

use Matrix\Contracts\ArticleManager;
use Matrix\Contracts\LogManager;
use Matrix\Models\Article;
use Matrix\Models\ArticleRead;
use Matrix\Models\ArticleLike;
use Matrix\Models\Category;
use Matrix\Models\SubCategory;
use Matrix\Models\User;
use Matrix\Models\Teacher;
use Auth;
use Exception;
use Log;

class ArticleService extends BaseService implements ArticleManager
{
    const ARTICLE = 'article';
    const DELETE = 'delete';

    private $article;
    private $category;
    private $subCategory;
    private $user;
    private $teacher;
    private $articleRead;
    private $articleLike;
    private $logManager;

    public function __construct(Article $article, Category $category, SubCategory $subCategory, User $user, Teacher $teacher, ArticleRead $articleRead, ArticleLike $articleLike, LogManager $logManager)
    {
        $this->article = $article;
        $this->category = $category;
        $this->subCategory = $subCategory;
        $this->user = $user;
        $this->teacher = $teacher;
        $this->articleRead = $articleRead;
        $this->articleLike = $articleLike;
        $this->logManager = $logManager;
    }

    public function getArticleList(int $pageNo, int $pageSize, array $credentials)
    {
        $cond = [];

        foreach ($credentials as $k => $v) {
            if ($k != 'title' && $k !== 'page_no' && $k !== 'page_size' && $v !== "" && $v !== null) {
                $cond[] = [$k, $v];
            }
        }

        $title = array_get($credentials, 'title');
        if (!empty($title)) {
            $cond[] = ['title', 'like', "%$title%"];
        }

        $articleList = Article::where($cond)
            ->orderBy('created_at', 'desc')
            ->skip($pageSize * ($pageNo - 1))
            ->take($pageSize)
            ->get()
            ->toArray();

        return $articleList;
    }

    public function getArticleCnt(array $credentials)
    {
        $cond = [];

        foreach ($credentials as $k => $v) {
            if ($k != 'title' && $k !== 'page_no' && $k !== 'page_size' && $v !== "" && $v !== null) {
                $cond[] = [$k, $v];
            }
        }

        $title = array_get($credentials, 'title');
        if (!empty($title)) {
            $cond[] = ['title', 'like', "%$title%"];
        }

        $articleCnt = Article::where($cond)->count();

        return $articleCnt;
    }

    public function createArticle(array $articleData)
    {
        //$articleData['published_at'] = date('Y-m-d H:i:s');
        $articleData['modify_user_id'] = Auth::user()->id;
        $articleData['cover_url'] = (string)array_get($articleData, 'cover_url');
        $article = $this->article->create($articleData);
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'article' => $article->toArray(),
            ],
        ];

        return $ret;
    }

    public function updateArticleShow($articleId)
    {
        $articleInfo = $this->article->updateShowStatus($articleId);
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'article' => $articleInfo,
            ],
        ];

        return $ret;
    }

    public function updateArticlePushQywx($articleId)
    {
        $articleInfo = $this->article->updatePushQywxStatus($articleId);
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'article' => $articleInfo,
            ],
        ];

        return $ret;
    }

    public function updateArticle(int $articleId, array $articleData)
    {
        $articleData['cover_url'] = (string)array_get($articleData, 'cover_url');
        $articleInfo = $this->article->updateArticle($articleId, $articleData);
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'article' => $articleInfo,
            ],
        ];

        return $ret;
    }

    public function trashArticle(int $articleId)
    {
        try{
            $articleInfo = self::getArticleInfo($articleId);
            $this->logManager->createOperationLog(self::ARTICLE, Auth::id(), json_encode($articleInfo), self::DELETE);

            $this->article->trashArticle($articleId);
            $ret = [ 'code' => SYS_STATUS_OK ];
            return $ret;
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
        }
    }

    public function getArticleInfo($articleId, $openId = '', $udid = '')
    {
        $articleInfo = $this->article->getArticleInfo($articleId);

        $categoryInfo = $this->category->getCategoryInfo(array_get($articleInfo, 'category_code'));
        $articleInfo['category_name'] = array_get($categoryInfo, 'name');

        $subCategoryInfo = $this->subCategory->getSubCategoryInfo(array_get($articleInfo, 'sub_category_code'));
        $articleInfo['sub_category_name'] = array_get($subCategoryInfo, 'name');

        $teacherInfo = $this->teacher->getTeacherInfo(array_get($articleInfo, 'teacher_id'));
        $teacherUserInfo = $this->user->getUserInfo(array_get($teacherInfo, 'user_id'));
        $articleInfo['teacher_user_name'] = array_get($teacherUserInfo, 'name');

        if (!empty($openId) || !empty($udid)) {
            $like = $this->articleLike->getRecord($articleId, 'article', $openId, $udid);
        } else {
            $like = 0;
        }
        $likeCount = $this->articleLike->getArticleLikeCount($articleId, 'article');

        $articleInfo['like'] = $like;
        $articleInfo['like_count'] = $likeCount;

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'article' => $articleInfo,
            ],
        ];

        return $ret;
    }

    public function getActiveArticleListBySubCategoryCode(string $subCategoryCode)
    {
        $articleList = $this->article->getActiveArticleListBySubCategoryCode($subCategoryCode);

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'article_list' => $articleList,
            ],
        ];

        return $ret;
    }

    public function readArticle($articleId, $openId)
    {
        $article = $this->article->read($articleId);
        if (!empty($openId)) {
            $articleReadInfo = $this->articleRead->record([
                'open_id' => $openId,
                'type' => 'article',
                'article_id' => $articleId,
            ]);
        }

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'article' => $article,
            ],
        ];

        return $ret;
    }

    public function likeArticle($articleId, $openId = '', $udid = '', $sessionId = '', $userType = '')
    {
        $like = $this->articleLike->record($articleId, 'article', $openId, $udid, $sessionId, $userType);

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'like' => (int)array_get($like, 'status'),
            ],
        ];

        return $ret;
    }

    public function getArticleListByTeacherIdList(array $teacherIdList, int $index = 0, int $pageSize = 20)
    {
        $nowDate = date('Y-m-d H:i:s');//获取当前时间

        $articleList = $this->article->getArticleList([
            'teacher_id' => $teacherIdList,
            'show' => 1,
            'published_at' => $nowDate,
        ], $index, $pageSize);

        return $articleList;
    }

    public function getUnfeedArticleList(array $categoryCodeList)
    {
        $articleList = $this->article->getUnfeedArticleList($categoryCodeList);

        return $articleList;
    }

    public function setArticleFeed(array $articleIdList)
    {
        $this->article->setArticleFeed($articleIdList);
    }
}

