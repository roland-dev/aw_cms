<?php

namespace Matrix\Http\Controllers\Client;

use Illuminate\Http\Request;
use Matrix\Contracts\UserManager;
use Matrix\Contracts\UcManager;
use Matrix\Contracts\CategoryManager;
use Matrix\Contracts\ArticleManager;
use Matrix\Models\UserGroup;
use Matrix\Contracts\UserGroupManager;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Matrix\Contracts\InteractionContract;
use Log;
use Exception;
use Matrix\Exceptions\MatrixException;
use Matrix\Models\ArticleReply;
use Matrix\Contracts\CustomerManager;

class ArticleController extends Controller
{
    const CATEGORY_GROUP = 'article_group_a';

    const ARTICLE_TYPE = 'article';

    const USER_GROUP_CODE_SUPERMAN_TAG = 'teacher_superman_tag';

    private $request;
    private $userGroup;
    private $user;
    private $interactionContract;
    protected $customer;

    public function __construct(    Request $request,
                                    UserGroupManager $userGroup,
                                    UserManager $user,
                                    InteractionContract $interactionContract,
                                    CustomerManager $customer
                               )
    {
        $this->request = $request;
        $this->userGroup = $userGroup;
        $this->user = $user;
        $this->interactionContract = $interactionContract;
        $this->customer = $customer;
    }

    public function Test(UcManager $ucenter)
    {
        $sessionId = $this->request->header('X-SessionId');
        $currentUserInfo = $ucenter->getUserInfoBySessionId($sessionId);
        $currentOpenId = (string)array_get($currentUserInfo, 'data.user.openId');
        $accessCodeList = $ucenter->getAccessCodeByOpenId($currentOpenId);
    }

    public function getArticleInfo(ArticleManager $articleManager, CategoryManager $categoryManager, UcManager $ucenter, $articleId)
    {
        $loginUrl = $this->h5WechatAutoLogin($this->request, $ucenter);

        if(!empty($loginUrl)){
            return redirect()->away($loginUrl);
        }

        $udid = '';

        $currentOpenId = '';

        $sessionId = '';

        $isTeacher = 0;
        $accessCodeList = ['basic', 'dp2', 'index', 'i_dpqs', 'i_lnhy', 'i_zlcb'];

        $sessionId = $this->request->header('X-SessionId');
        if (empty($sessionId)) {
            $sessionId = $this->request->cookie('X-SessionId');
        }

        if (empty($sessionId)) {
            try {
                $anonymousUserInfo = $ucenter->getAnonymousUserInfo();
                $sessionId = (string)array_get($anonymousUserInfo, 'data.sessionId');
                $accessCodeList = $ucenter->getAccessCodeBySessionId($sessionId);

                Log::info("X-SessionId: $sessionId not found.");
            }catch(MatrixException $e){
                $accessCodeList = ['basic', 'dp2', 'index', 'i_dpqs', 'i_lnhy', 'i_zlcb'];
                Log::info($e->getMessage());
                $currentUserInfo = [];
            }catch(Exception $e){
                $ret = [
                    'code' => SYS_STATUS_ERROR_UNKNOW,
                    'msg' => $e->getMessage(),
                ];

                return $ret;
            }
        } else {
            try {
                $currentUserInfo = $ucenter->getUserInfoBySessionId($sessionId);
            } catch (MatrixException $e) {
                Log::info($e->getMessage());
                $currentUserInfo = [];
            } catch (Exception $e) {
                $ret = [
                    'code' => SYS_STATUS_ERROR_UNKNOW,
                    'msg' => $e->getMessage(),
                ];

                return $ret;
            }

            if (empty($currentUserInfo)) {
                try {
                    $anonymousUserInfo = $ucenter->getAnonymousUserInfo();
                    $sessionId = (string)array_get($anonymousUserInfo, 'data.sessionId');
                    $accessCodeList = $ucenter->getAccessCodeBySessionId($sessionId);
                } catch (MatrixException $e) {
                    $accessCodeList = ['basic', 'dp2', 'index', 'i_dpqs', 'i_lnhy', 'i_zlcb'];
                    Log::info($e->getMessage());
                } catch (Exception $e) {
                    $ret = [
                        'code' => SYS_STATUS_ERROR_UNKNOW,
                        'msg' => $e->getMessage(),
                    ];

                    return $ret;
                }
            }
            else{ //获取当前登录用户信息成功,通过接口获取权限
                $openId = (string)array_get($currentUserInfo, 'data.user.openId');
                //$accessCodeList = $ucenter->getAccessCodeByOpenId($openId, 'default', true);
                $accessCodeList = array_get($currentUserInfo, 'data.user.accessCodes', []); 
            }
            
            $enterpriseUserId = array_get($currentUserInfo, 'data.user.qyUserId');
            $userMobile = array_get($currentUserInfo, 'data.user.mobile');

            if (!empty($enterpriseUserId)) {
                try{
                    $teacherUserData = $this->user->getUserByEnterpriseUserId($enterpriseUserId);

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
                }catch(MatrixException $e){
                    Log::info($e->getMessage());
                }catch(Exception $e){
                    $ret = [
                        'code' => SYS_STATUS_ERROR_UNKNOW,
                        'msg' => $e->getMessage(),
                    ];

                    return $ret;
                }
            }
        }

        try {
            $articleInfoData = $articleManager->getArticleInfo($articleId);

            $isLike = $this->interactionContract->getLikeRecord($articleId, self::ARTICLE_TYPE, $currentOpenId, $udid);

            $likeSum = $this->interactionContract->getLikeSum($articleId, self::ARTICLE_TYPE);
        } catch (ModelNotFoundException $e) {
            $ret = [
                'code' => 404,
                'msg' => '文章不存在',
            ];
            Log::info($ret['msg']);
            abort($ret['code'], $ret['msg']);
        }

        $articleInfo = array_get($articleInfoData, 'data.article');

        if (empty($articleInfo) || $articleInfo['show'] == 0) {
            $ret = [
                'code' => 404,
                'msg' => '文章不存在',
            ];
            Log::info($ret['msg']);
            abort($ret['code'], $ret['msg']);
        }

        $categoryCode = array_get($articleInfo, 'category_code');
        $categoryInfo = $categoryManager->getCategoryInfoByCode($categoryCode);

        if (empty($categoryInfo)) {
            $ret = [
                'code' => 404,
                'msg' => '栏目不存在',
            ];
            Log::info($ret['msg']);
            abort($ret['code'], $ret['msg']);
        }

        if (!in_array($categoryInfo['service_key'], $accessCodeList)) {
            $ret = [
                'code' => 403,
            ];
            return view('errors.403', $ret);
        }

        $articleInfo['category_name'] = (string)array_get($categoryInfo, 'name');

        $teacherId = array_get($articleInfo, 'teacher_id');
        $teacherListData = $categoryManager->getTeacherListByIdList([$teacherId]);
        $teacherList = array_get($teacherListData, 'data.teacher_list');
        foreach ($teacherList as $teacher) {
            $articleInfo['teacher_user_id'] = $teacher['user_id'];
            $articleInfo['teacher_name'] = $teacher['name'];
            $articleInfo['teacher_icon_url'] = $teacher['icon_url'];
        }

        $articleInfo['guide_msg'] = array_get($articleInfo, 'ad_guide');

        $articleInfo['session_id'] = empty($sessionId) ? '' : $sessionId;

        $articleInfo['is_forward_teacher'] = $isTeacher;

        $articleInfo['forward_open_id'] = empty($isTeacher) ? 0 : $currentOpenId;

        $articleInfo['is_reply'] = isset($userMobile) && !empty($userMobile) ? 1 : 0;

        $articleInfo['is_like'] = array_get($isLike, 'data.like');

        $voteCnt = (int)array_get($likeSum, 'data.statisticInfo.like_sum');
        $voteCnt = $voteCnt > 999 ? '999+' : $voteCnt;

        $articleInfo['like_sum'] = $voteCnt;
        $articleInfo['detail_url'] = sprintf('%s/api/v2/client/article/%s', config('app.h5_api_url'), array_get($articleInfo, 'id'));

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'article' => $articleInfo,
            ],
        ];

        return view('article.detail', $ret);
    }

    public function getArticleList(UserManager $userManager, CategoryManager $categoryManager, ArticleManager $articleManager, UcManager $ucenter)
    {
        $credentials = $this->request->validate([
            'teacher_userid' => 'required|string',
            'index' => 'required|integer',
            'page_size' => 'required|integer',
        ]);

        $sessionId = $this->request->header('X-SessionId');
        $currentUserInfo = $ucenter->getUserInfoBySessionId($sessionId);
        $currentOpenId = (string)array_get($currentUserInfo, 'data.user.openId');
        //$accessCodeList = $ucenter->getAccessCodeByOpenId($currentOpenId);
        $accessCodeList = array_get($currentUserInfo, 'data.user.accessCodes', []); 

        $teacherUserInfo = $userManager->getUserByEnterpriseUserId(array_get($credentials, 'teacher_userid'));
        $code = array_get($teacherUserInfo, 'code');
        if ($code === USER_NOT_FOUND) {
            $ret = ['code' => CLIENT_TEACHER_NOT];
            return $ret;
        } elseif ($code != SYS_STATUS_OK) {
            $ret = ['code' => SYS_STATUS_ERROR_UNKNOW];
            return $ret;
        }
        $teacherUserId = array_get($teacherUserInfo, 'data.id');
        $baseTeacherList = $categoryManager->getTeacherListByUserIdList([$teacherUserId]);
        $teacherList = [];

        $accessCategoryListData = $categoryManager->getCategoryListByGroupCode(self::CATEGORY_GROUP);
        $accessCategoryList = array_get($accessCategoryListData, 'data.category_list');
        $accessCategoryCodeList = array_column($accessCategoryList, 'code');
        $accessCategoryList = array_column($accessCategoryList, NULL, 'code');

        foreach ($baseTeacherList as $baseTeacher) {
            if (in_array($baseTeacher['category_code'], $accessCategoryCodeList)) {
                $teacherList[] = $baseTeacher;
            }
        }

        if (empty($teacherList)) {
            $ret = ['code' => CLIENT_TEACHER_NOT];
            return $ret;
        }
        $teacherIdList = array_column($teacherList, 'id');
        $articleList = $articleManager->getArticleListByTeacherIdList($teacherIdList, array_get($credentials, 'index'), array_get($credentials, 'page_size'));

        $ret = ['code' => SYS_STATUS_OK];
        $ret['data'] = [];
        $ret['data']['article_list'] = [];

        foreach ($articleList as $article) {
            $showArticle = [
                'detail_id' => array_get($article, 'id'),
                'title' => array_get($article, 'title'),
                'summary' => array_get($article, 'summary'),
                'description' => array_get($article, 'description'),
                'content' => $this->makeClientContent((string)array_get($article, 'content')),
                'cover_url' => $this->fitClientUrl(array_get($article, 'cover_url')),
                'created_at' => array_get($article, 'published_at'),
                'published_at' => array_get($article, 'published_at'),
                'name' => array_get($teacherUserInfo, 'data.name'),
                'icon_url' => $this->fitClientUrl(array_get($teacherUserInfo, 'data.icon_url')),
                'category_key' => array_get($article, 'category_code'),
                'category_name' => (string)array_get(array_get($accessCategoryList, array_get($article, 'category_code')), 'name'),
                'jump_type' => 'common_web',
                // 'detail_url' => $ucenter->fitAppWebviewUrl(sprintf('%s/api/v2/client/article/%s', config('app.url'), array_get($article, 'id'))),
                'detail_url' => sprintf('%s/api/v2/client/article/%s', config('app.h5_api_url'), array_get($article, 'id')),
                'author_user_id' => $teacherUserId,
            ];
            if (in_array(array_get(array_get($accessCategoryList, $showArticle['category_key']), 'service_key'), $accessCodeList)) {
                $showArticle['access_deny'] = 0;
            } else {
                $showArticle['access_deny'] = 1;
                $showArticle['guide_media'] = 'page';
            }
            $ret['data']['article_list'][] = $showArticle;
        }

        return $ret;
    }

}
