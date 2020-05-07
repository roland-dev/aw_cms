<?php

namespace Matrix\Http\Controllers;

use Illuminate\Http\Request;
use Matrix\Contracts\InteractionContract;
use Matrix\Contracts\UserManager;
use Matrix\Contracts\UcManager;
use Matrix\Contracts\CustomerManager;
use Matrix\Contracts\CategoryManager;
use Matrix\Contracts\TwitterManager;

use Matrix\Models\ArticleReply;

use Matrix\Exceptions\InteractionException;
use Matrix\Exceptions\MatrixException;
use Matrix\Exceptions\PermissionException;
use Matrix\Exceptions\UcException;
use Exception;
use Auth;
use Log;
use DB;

class InteractionController extends Controller
{
    //
    const REPLY_CONTENT_TYPE_LIST = [
        [
            'type' => 'article',
            'name' => '文章',
        ],
        [
            'type' => 'talkshow',
            'name' => '节目',
        ],
        [
            'type' => 'course',
            'name' => '课程',
        ],
        [
            'type' => 'stock_report',
            'name' => '个股报告'
        ],
        [
            'type' => 'kit_report',
            'name' => '锦囊报告'
        ]
    ];

    protected $user;
    protected $ucenter;
    protected $request;
    protected $customer;
    protected $interaction;
    protected $twitter;
    protected $category;

    public function __construct(Request $request, InteractionContract $interaction, UserManager $user, CustomerManager $customer, UcManager $ucenter, TwitterManager $twitter, CategoryManager $category)
    {
        $this->user = $user;
        $this->ucenter = $ucenter;
        $this->request = $request;
        $this->customer = $customer;
        $this->interaction = $interaction;
        $this->twitter = $twitter;
        $this->category = $category;
    }

    public function getReplyList()
    {
        $credentials = $this->request->validate([
            'page_size' => 'required|integer', // 页容量，条目数
            'page_no' => 'required|integer', // 页码
            'status' => 'required|integer', // 审批状态
            'article_author_user_id' => 'integer|nullable',
            'article_title' => 'string|nullable',
            'article_type' => 'string|nullable',
        ]);

        try {
            $pageNo = array_get($credentials, 'page_no');
            $pageSize = array_get($credentials, 'page_size');
            $status = array_get($credentials, 'status');

            if (!in_array($status, [
                ArticleReply::STATUS_NEW,
                ArticleReply::STATUS_APPROVE,
                ArticleReply::STATUS_DENIED,
            ])) {
                abort(400);
            }

            $teacherList = $this->interaction->getTeacherList();
            $myTeacher = $teacherList->filter(function ($item, $key) {
                return Auth::id() == $item->id;
            })->first();

            $authorUserId = empty($myTeacher) ? (int)array_get($credentials, 'article_author_user_id') : $myTeacher->id;

            $articleTitle = (string)array_get($credentials, 'article_title');
            $articleType = (string)array_get($credentials, 'article_type');

            $replyList = $this->interaction->getExamineReplyList($pageNo, $pageSize, $status, $authorUserId, $articleTitle, $articleType)->toArray();

            $replyCnt = $this->interaction->getExamineReplyCnt($status, $authorUserId, $articleTitle, $articleType);

            $teacherList = $this->interaction->getTeacherList()->toArray();
            $teacherList = array_column($teacherList, NULL, 'id');

            $examineUserIdList = array_column($replyList, 'examine_user_id');
            $examineUserListData = $this->user->getUserListByUserIdList($examineUserIdList);
            $examineUserList = (array)array_get($examineUserListData, 'data.user_list');
            $examineUserNameList = array_column($examineUserList, 'name', 'id');

            $customerOpenIdList = array_column($replyList, 'open_id');
            $customerList = $this->customer->getCustomerList($customerOpenIdList);
            $customerMap = array_column($customerList, NULL, 'open_id');

            foreach ($replyList as &$reply) {
                $reply['article_author_name'] = (string)array_get(array_get($teacherList, $reply['article_author_user_id']), 'name');
                $reply['examine_user_name'] = (string)array_get($examineUserNameList, $reply['examine_user_id']);
                $customer = array_get($customerMap, (string)$reply['open_id']);
                $reply['customer_name'] = empty($customer) ? '' : (string)array_get($customer, 'nickname');
                if ($reply['article_author_user_id'] === Auth::id()) {
                    $reply['is_auth'] = true;
                } else {
                    $reply['is_auth'] = false;
                }
            }

            $ret = [
                'code' => SYS_STATUS_OK,
                'msg' => '',
                'data' => [
                    'reply_cnt' => $replyCnt,
                    'reply_list' => $replyList,
                ],
            ];
        } catch (Exception $e) {
            Log::error("获取评论列表错误: {$e->getMessage()}", [$e]);
            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => '未知错误',
            ];
        }

        return $ret;
    }

    public function examineReply()
    {
        $credentials = $this->request->validate([
            'reply_id' => 'required|integer', // 评论编号
            'operate' => 'required|integer', // 审批意见
        ]);

        $replyId = array_get($credentials, 'reply_id');
        $operate = array_get($credentials, 'operate');

        try {
            $reply = $this->interaction->examineReply($replyId, $operate);

            $ret = [
                'code' => SYS_STATUS_OK,
                'msg' => '审批成功',
                'data' => [
                    'reply' => $reply,
                ],
            ];
        } catch (InteractionException $e) {
            Log::error("审批失败: {$e->getMessage()}", [$e]);
            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => $e->getMessage(),
            ];
        } catch (Exception $e) {
            Log::error("审批失败: {$e->getMessage()}", [$e]);
            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => '未知错误',
            ];
        }

        return $ret;
    }

    public function batchExamineReply()
    {
        $credentials = $this->request->validate([
            'reply_id_list' => 'required|array', // 评论编号列表
            'operate' => 'required|integer', // 审批意见
        ]);

        $replyIdList = array_get($credentials, 'reply_id_list');
        $operate = array_get($credentials, 'operate');

        DB::beginTransaction();
        try {
            $effectRows = $this->interaction->batchExamineReply($replyIdList, $operate);

            $ret = [
                'code' => SYS_STATUS_OK,
                'msg' => '审批成功',
                'data' => [
                    'effect_rows' => $effectRows,
                ],
            ];
            DB::commit();
        } catch (InteractionException $e) {
            Log::error("审批失败: {$e->getMessage()}", [$e]);
            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => $e->getMessage(),
            ];
            DB::rollBack();
        } catch (Exception $e) {
            Log::error("审批失败: {$e->getMessage()}", [$e]);
            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => '未知错误',
            ];
            DB::rollBack();
        }

        return $ret;
    }

    public function postReply()
    {
        $credentials = $this->request->validate([
            'type' => 'required|string',
            'article_id' => 'required|string',
            'article_title' => 'required|string',
            'article_author_user_id' => 'required|integer',
            'content' => 'required|string',
            'ref_id' => 'integer|nullable',
            'ref_content' => 'string|nullable',
            'ref_open_id' => 'string|nullable',
            'is_all_visible' => 'required|integer',
        ]);

        try {
            $userId = Auth::id();
            $userInfoData = $this->user->getUserInfo($userId);
            $qyUserid = array_get($userInfoData, 'ucInfo.enterprise_userid');

            $ucUserInfo = $this->ucenter->getUserInfoByQyUserid($qyUserid, 'default', true);

            $nickName = array_get($ucUserInfo, 'data.nickName');
            if (empty($nickName)) {
                throw new InteractionException('回复失败，没有设置昵称', INTERACTION_NICKNAME_NOT_SET);
            }

            //只有发布该节目视频/课程视频/文章内容的老师，才能回复评论区中用户的评论
            if(!empty($credentials['ref_id'])){//判断是否为回复，而不是评论
                //获取文章作者Id
                $replyInfo = $this->interaction->getReplyInfo($credentials['ref_id']);
                $authorId = array_get($replyInfo, 'article_author_user_id');

                // 在判定是否是内容生成老师，有权限回复该评论
                if((int)$authorId !== (int)$userId){
                    throw new PermissionException('不是内容作者无权限回复该评论', USER_OPERATE_PERMISSION_DENY);
                }
            }

            $customerData = [
                'open_id' => (string)array_get($ucUserInfo, 'data.openId'),
                'code' => (string)array_get($ucUserInfo, 'data.customerCode'),
                'qy_userid' => (string)array_get($ucUserInfo, 'data.qyUserId'),
                'name' => (string)array_get($ucUserInfo, 'data.name'),
                'mobile' => (string)array_get($ucUserInfo, 'data.mobile'),
                'nickname' => (string)array_get($ucUserInfo, 'data.nickName'),
                'icon_url' => (string)array_get($ucUserInfo, 'data.iconUrl'),
            ];

            $customer = $this->customer->updateCustomer($customerData);

            $type = $this->request->input('type');
            $articleId = $this->request->input('article_id');
            $articleTitle = $this->request->input('article_title');
            $articleAuthorUserId = $this->request->input('article_author_user_id');
            $content = $this->request->input('content');
            $refId = (int)$this->request->input('ref_id');
            $refContent = (string)$this->request->input('ref_content');
            $refOpenId = (string)$this->request->input('ref_open_id');
            $isAllVisible = (int)$this->request->input('is_all_visible');

            $ucUserInfo = ['user' => array_get($ucUserInfo, 'data')];
            $newReply = $this->interaction->reply($type, $articleId, $articleTitle, $articleAuthorUserId, $content, $ucUserInfo, $refId, $refContent, $refOpenId, $isAllVisible);

            unset($newReply['session_id']);

            $ret = [
                'code' => SYS_STATUS_OK,
                'msg' => '回复成功',
                'data' => [
                    'reply' => $newReply,
                ],
            ];

            if (!empty($refId) || !empty(array_get($credentials, 'article_author_user_id'))) {
                try {
                    if (empty($refId)) {
                        $userInfo = $this->user->getUserInfo(array_get($credentials, 'article_author_user_id'));
                        $qyUserId = array_get($userInfo, 'ucInfo.enterprise_userid');
                        $userIds = [$qyUserId];
                    } else {
                        $userIds = [$refOpenId];
                    }

                    // 针对 解盘 进行处理 解盘类型数据没有 title
                    if ($type == 'twitter') {
                        $twitterInfo = $this->twitter->getTwitterInfo($articleId);
                        $categoryCode = (string)array_get($twitterInfo, 'category_code');
                        $categoryInfo = $this->category->getCategoryInfoByCode($categoryCode);
                        $articleTitle = (string)array_get($categoryInfo, 'name');
                    }
    
                    $messageFormData = [
                        'appCode' => 62,
                        'boxCode' => '',
                        'boxIconUrl' => '',
                        'boxTitle' => '',
                        'title' => '点评：' . $articleTitle,
                        'opTitle' => '点评',
                        'content' => array_get($credentials, 'content'),
                        'msgKind' => 'commentReply',
                        'toAll' => 0,
                        'traceId' => array_get($newReply, 'id'),
                        'traceType' => array_get($credentials, 'type'),
                        'userIds' => $userIds,
                        'sender' => $nickName,
                        'senderUserId' => array_get($ucUserInfo, 'user.qyUserId'),
                    ];

                    $this->ucenter->sendMessageToUc($messageFormData);
    
                } catch (UcException $e) {
                    Log::error('消息同步到UC 出错'.$e->getMessage(), $e->getCode());
                    $ret['error'] = '消息同步到 UC 出错';
                }
            }
        } catch (PermissionException $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = [
                'code' => $e->getCode(),
                'msg' => $e->getMessage(),
            ];
        } catch (MatrixException $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = [
                'code' => $e->getCode(),
                'msg' => $e->getMessage(),
            ];
        } catch (Exception $e) {
            Log::error('回复失败', [$e]);
            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => '回复失败',
            ];
        }

        return $ret;
    }

    public function getTeacherList()
    {
        try {
            $teacherList = $this->interaction->getTeacherList();
            $myTeacherList = $teacherList->filter(function ($item, $key) {
                return Auth::id() == $item->id;
            });

            if (!empty($myTeacherList->toArray())) {
                $teacherList = $myTeacherList;
            }

            $ret = [
                'code' => SYS_STATUS_OK,
                'msg' => '',
                'data' => [
                    'teacher_list' => $teacherList,
                ],
            ];
        } catch (Exception $e) {
            Log::error("获取评论审批老师列表失败：{$e->getMessage()}", [$e]);
            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => '获取老师列表失败',
            ];
        }

        return $ret;
    }

    public function getContentTypeList()
    {
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'content_type_list' => self::REPLY_CONTENT_TYPE_LIST,
            ],
        ];

        return $ret;
    }
}
