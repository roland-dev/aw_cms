<?php
namespace Matrix\Services;

use Matrix\Contracts\TwitterManager;
use Matrix\Models\PrivateMessageGuard;
use Matrix\Models\PrivateMessage;
use Matrix\Models\CategoryGroup;
use Matrix\Models\SystemNotice;
use Matrix\Models\TwitterGuard;
use Matrix\Models\ArticleLike;
use Matrix\Models\Category;
use Matrix\Models\Twitter;
use Matrix\Models\Teacher;
use Matrix\Models\Ucenter;
use Matrix\Models\User;
use Matrix\Models\Article;
use Matrix\Models\VideoSignin;
use Matrix\Models\TjWxSendLogDetail;

use Matrix\Models\Course;
use Matrix\Models\CourseVideo;
use Matrix\Models\CourseSystem;

use Matrix\Contracts\UcManager;
use Matrix\Contracts\LogManager;
use Matrix\Contracts\CustomerManager;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Matrix\Exceptions\MatrixException;
use Matrix\Exceptions\InteractionException;
use Matrix\Models\LikeStatistic;
use Exception;
use DB;
use Log;

use Auth;
use Matrix\Models\Customer;
use Matrix\Models\StockReport;
use Matrix\Models\KitReport;
use Matrix\Models\Kit;

class TwitterService extends BaseService implements TwitterManager
{
    const FORWARD_CATEGORY_CODE = [
        'default' => 'twitter_forward_stock_a',
        'hk' => 'twitter_forward_stock_h',
    ];

    const TWITTER = 'twitter';

    const DELETE = 'delete';

    const SEPARATOR = ';';

    private $twitter;
    private $teacher;
    private $twitterGuard;
    private $articleLike;
    private $privateMessageGuard;
    private $privateMessage;
    private $systemNotice;
    private $category;
    private $categoryGroup;
    private $user;
    private $ucenter;
    private $uc;
    private $likeStatistic;
    private $customerManager;
    private $customer;
    private $logManager;

    public function __construct(TwitterGuard $twitterGuard,
                                Twitter $twitter,
                                Teacher $teacher,
                                ArticleLike $articleLike,
                                PrivateMessageGuard $privateMessageGuard,
                                PrivateMessage $privateMessage,
                                SystemNotice $systemNotice,
                                Category $category,
                                CategoryGroup $categoryGroup,
                                User $user,
                                UcManager $ucenter,
                                LikeStatistic $likeStatistic,
                                Ucenter $uc,
                                LogManager $logManager,
                                CustomerManager $customerManager,
                                Customer $customer)
    {
        $this->twitter = $twitter;
        $this->teacher = $teacher;
        $this->twitterGuard = $twitterGuard;
        $this->articleLike = $articleLike;
        $this->privateMessageGuard = $privateMessageGuard;
        $this->privateMessage = $privateMessage;
        $this->systemNotice = $systemNotice;
        $this->category = $category;
        $this->categoryGroup = $categoryGroup;
        $this->user = $user;
        $this->ucenter = $ucenter;
        $this->likeStatistic = $likeStatistic;
        $this->uc = $uc;
        $this->customerManager = $customerManager;
        $this->customer = $customer;
        $this->logManager = $logManager;
    }

    public function createTwitterRequest(string $categoryCode, string $openId, string $sourceType = 'customer')
    {
        $requestData = [
            'category_code' => $categoryCode,
            'open_id' => $openId,
            'operator_user_id' => 0,
            'status' => TwitterGuard::STATUS_REQUEST,
            'source_type' => $sourceType,
        ];

        $category = $this->category->getCategoryInfo($categoryCode);

        try {
            $twitterGuard = $this->twitterGuard->getLastTwitterGuard($openId, $categoryCode);
            if ($twitterGuard['status'] == TwitterGuard::STATUS_REJECT) {
                if ($twitterGuard['source_type'] == TwitterGuard::SOURCE_AUTO_PROGRAM) {
                    $requestData['review_status'] = TwitterGuard::REVIEW_REJECT;
                }
                $twitterGuard = $this->twitterGuard->create($requestData)->toArray();
            }
        } catch (Exception $e) {
            $twitterGuard = $this->twitterGuard->create($requestData)->toArray();
        }

        $systemNotice = $this->systemNotice->noticeCustomer('动态申请通知', sprintf(SystemNotice::NOTICE_CONTENT_TWITTER_REQUEST, $category['name']), $openId);

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'twitter_request' => $twitterGuard,
                'system_notice' => $systemNotice,
            ],
        ];

        return $ret;
    }

    public function processTwitterRequest(int $twitterGuardId, int $operate)
    {
        $currentUserId = Auth::user()->id;

        $twitterGuardData = $this->twitterGuard->find($twitterGuardId)->toArray();
        $openId = array_get($twitterGuardData, 'open_id');


        $isQualified = 1;
        $ucInfoData = $this->ucenter->getUserInfoByOpenId($openId, 'hk');
        $ucUserInfo = array_get($ucInfoData, 'data');
        $totalRemitHKD =  (double)array_get($ucUserInfo, 'cashBalance.totalRemitHKD');
        $totalWithdrawHKD = (double)array_get($ucUserInfo, 'cashBalance.totalWithdrawHKD');
        $netProceeds = $totalRemitHKD - $totalWithdrawHKD;
        if ($netProceeds < config('app.customer_money_standard')) {
            $isQualified = 0;
        }

        $twitterGuard = $this->twitterGuard->process($twitterGuardId, $operate, $currentUserId, $isQualified);
        $privateMessageGuard = [];

        switch ($twitterGuard['status']) {
            case TwitterGuard::STATUS_APPROVE:
                $systemNoticeContent = SystemNotice::NOTICE_CONTENT_TWITTER_PROCESS_APPROVED;
                break;
            case TwitterGuard::STATUS_REJECT:
                if ($twitterGuard['source_type'] === TwitterGuard::SOURCE_AUTO_PROGRAM) {
                    $systemNoticeContent = SystemNotice::NOTICE_CONTENT_TWITTER_REVIEW_DENIED;
                    $categoryCode = array_get($twitterGuard, 'category_code');
                    $openId = array_get($twitterGuard, 'open_id');
                    $privateMessageGuard = self::processPrivateMessageRequestByReview($categoryCode, $openId);
                } else {
                    $systemNoticeContent = SystemNotice::NOTICE_CONTENT_TWITTER_PROCESS_DENIED;
                }
                break;
        }
        $category = $this->category->getCategoryInfo($twitterGuard['category_code']);

        $systemNotice = $this->systemNotice->noticeCustomer('动态申请通知', sprintf($systemNoticeContent, $category['name']), $twitterGuard['open_id']);

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'twitter_request' => $twitterGuard,
                'system_notice' => $systemNotice,
                'private_message_request' => $privateMessageGuard,
            ],
        ];

        return $ret;
    }

    private function processPrivateMessageRequestByReview(string $categoryCode, string $openId)
    {
        $createPrivateMessageGuard = [];

        $primaryTeacher = $this->teacher->getPrimaryTeacher($categoryCode);
        if ( !empty($primaryTeacher) ) {
            $teacherId = array_get($primaryTeacher, 'id');
            try {
                $privateMessageGuard = $this->privateMessageGuard->getLastPrivateMessageGuard($teacherId, $openId);
            } catch(Exception $e) {
                $privateMessageGuard = NULL;
            }

            if ( !empty($privateMessageGuard) && PrivateMessageGuard::STATUS_APPROVE === array_get($privateMessageGuard, 'status') ) {
                //组织 新的记录 字段数据
                $newPrivateMessageGuard = [
                    'teacher_id' => $teacherId,
                    'open_id' => $openId,
                    'operator_user_id' => Auth::user()->id,
                    'status' => PrivateMessageGuard::STATUS_REJECT,
                    'source_type' => PrivateMessageGuard::SOURCE_RE_REVIEW,
                ];
                $createPrivateMessageGuard = self::createPrivateMessageRequestOfArray($newPrivateMessageGuard);
            }
        }
        
        return $createPrivateMessageGuard;
    }

    public function addTwitterRequest( array $condition)
    {
        $operatorUserId = Auth::user()->id;
        
        $mobile = array_get($condition, 'mobile');

        $ucInfoData = $this->ucenter->getUserInfoByMobile($mobile, 'hk');
        $openId = array_get($ucInfoData, 'data.user.openId');

        $isQualified = 1;
        $ucUserInfo = array_get($ucInfoData, 'data.user');
        $totalRemitHKD =  (double)array_get($ucUserInfo, 'cashBalance.totalRemitHKD');
        $totalWithdrawHKD = (double)array_get($ucUserInfo, 'cashBalance.totalWithdrawHKD');
        $netProceeds = $totalRemitHKD - $totalWithdrawHKD;
        if ($netProceeds < config('app.customer_money_standard')) {
            $isQualified = 0;
        }

        $newTwiiterGuardArray = [];
        $checkCategoryList = array_get($condition, 'checkList');


        foreach ($checkCategoryList as $categoryCode) {
            try {
                $twitterGuard = $this->twitterGuard->getLastTwitterGuard($openId, $categoryCode);
            } catch (Exception $e) {
                $twitterGuard = [];
            }

            if (!empty($twitterGuard) && (array_get($twitterGuard, 'status') == TwitterGuard::STATUS_REQUEST || array_get($twitterGuard, 'status') == TwitterGuard::STATUS_APPROVE)) {
                $ret = [
                    'code' => SYS_STATUS_ERROR_UNKNOW,
                ];
                return $ret;
            }

            $item = [
                'category_code' => $categoryCode,
                'open_id' => $openId,
                'operator_user_id' => $operatorUserId,
                'status' => TwitterGuard::STATUS_APPROVE,
                'source_type' => TwitterGuard::SOURCE_MANAGE_SYSTEM,
                'is_qualified' => $isQualified,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            array_push($newTwiiterGuardArray, $item);
        }

        try {
            DB::beginTransaction();

            $bool = $this->twitterGuard->insert($newTwiiterGuardArray);
            if ( !$bool ) {
                throw new Exception('添加失败');
            }

            $this->updateCustomerInfoOfOpenId($openId);

            $ret = [
                'code' => SYS_STATUS_OK,
                'msg' => '添加成功'
            ];

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => '添加失败或者更新客户信息错误'
            ];
        }

        return $ret;
    }

    private function updateCustomerInfoOfOpenId($openId)
    {
        $result = [];

        $customerInfo = $this->ucenter->getUserInfoByOpenId($openId);

        $customerData = [
            'open_id' => (string)array_get($customerInfo, 'data.openId'),
            'code' => (string)array_get($customerInfo, 'data.customerCode'),
            'name' => (string)array_get($customerInfo, 'data.name'),
            'mobile' => (string)array_get($customerInfo, 'data.mobile'),
            'icon_url' => (string)array_get($customerInfo, 'data.iconUrl'),
            'qy_userid' => (string)array_get($customerInfo, 'data.qyUserId'),
        ];

        $customerInfoRes = $this->customerManager->updateCustomer($customerData);

        if (!empty($customerInfoRes)) {
            $result = $customerData;
        }

        return $result;
    }

    public function createPrivateMessageRequestOfArray(array $newPrivateMessageGuard)
    {
        $privateMessageGuard = $this->privateMessageGuard->createPrivateMessageRequest($newPrivateMessageGuard);
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'private_message_guard' => $privateMessageGuard,
            ],
        ];

        return $ret;
    }

    public function createTwitter(array $twitterData)
    {
        $currentUserId = Auth::user()->id;
        $categoryCode = array_get($twitterData, 'category_code');
        $teacherInfo = $this->teacher->getTeacherInfoByUserIdAndCategoryCode($currentUserId, $categoryCode);
        $twitterData['teacher_id'] = array_get($teacherInfo, 'id');
        $twitterData['operator_user_id'] = $currentUserId;
        $twitter = $this->twitter->create($twitterData);
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'twitter' => $twitter->toArray(),
            ],
        ];

        return $ret;
    }

    public function forward2Twitter(array $refInfo, string $enterpriseUserId, string $business = 'default')
    {
        $twitterData = $refInfo;
        $twitterData['category_code'] = self::FORWARD_CATEGORY_CODE[$business];
        $myUc = $this->uc->getUcByEnterpriseUserId($enterpriseUserId);
        if (empty($myUc)) {
            throw new InteractionException('无权转发内容', SYS_STATUS_ERROR_UNKNOW);
        }

        $userId = array_get($myUc, 'user_id');
        if (empty($userId)) {
            throw new InteractionException('无权转发内容', SYS_STATUS_ERROR_UNKNOW);
        }


        try {
            $teacher = $this->teacher->getTeacherInfoByUserIdAndCategoryCode($userId, $twitterData['category_code']);
        } catch (ModelNotFoundException $e) {
            throw new InteractionException('无权转发内容', SYS_STATUS_ERROR_UNKNOW);
        }

        $twitterData['teacher_id'] = $teacher['id'];
        $twitterData['operator_user_id'] = $userId;

        try {
            if ($twitterData['ref_type'] == 'article') {
                $article = Article::findOrFail($twitterData['ref_id']);
                $twitterData['ref_category_code'] = $article->category_code;
            } elseif($twitterData['ref_type'] == 'talkshow') {
                $video = VideoSignin::where('video_key', $twitterData['ref_id'])->take(1)->first();
                $twitterData['ref_category_code'] = empty($video) ? 'talkshow' : 'forward_talkshow';
            } elseif($twitterData['ref_type'] == 'course') {
                $twitterData['ref_category_code'] = 'xuezhanfa_course';
                $video = VideoSignin::where('video_key', $twitterData['ref_id'])->take(1)->firstOrFail();
                $courseVideo = CourseVideo::where('video_signin_id', $video->id)->take(1)->firstOrFail();
                $course = Course::where('code', $courseVideo->course_code)->take(1)->firstOrFail();
                $twitterData['ref_category_code'] = $course->course_system_code;
            } elseif($twitterData['ref_type'] == 'news') {
                $twitterData['ref_category_code'] = 'news_stock_a';
            } elseif($twitterData['ref_type'] == StockReport::STOCK_REPORT_TWITTER_REF_TYPE) {
                $twitterData['ref_category_code'] = 'cyzb';
            } elseif($twitterData['ref_type'] == KitReport:: KIT_REPORT_TWITTER_REF_TYPE) {
                $kitReport = KitReport::where('report_id', $twitterData['ref_id'])->take(1)->firstOrFail();
                $kit = Kit::where('code', $kitReport->kit_code)->take(1)->firstOrFail();
                $twitterData['ref_category_code'] = $kit->service_key;
            } elseif ($twitterData['ref_type'] == 'twitter') {
                $twitter = Twitter::where('id', $twitterData['ref_id'])->take(1)->firstOrFail();
                $twitterData['ref_category_code'] = $twitter->category_code;
            }

            $twitterData['ref_title'] = mb_strlen($twitterData['ref_title']) > 100 ? sprintf('%s...', mb_substr($twitterData['ref_title'], 0, 100)) : $twitterData['ref_title'];
            $newTwitter = $this->twitter->create($twitterData);
            return $newTwitter;
        } catch (ModelNotFoundException $e) {
            throw new InteractionException('转发失败', SYS_STATUS_ERROR_UNKNOW);
        }
    }

    public function createrTwitterByKgsRequest(array $twitterDataByKgs)
    {
        // 兼容多图片问题
        $twitterDataByKgs['image_url'] = (string)implode(self::SEPARATOR, $twitterDataByKgs['image_url']);
        $twitter = $this->twitter->updateOrCreate(['category_code' => $twitterDataByKgs['category_code'], 'source_id' => $twitterDataByKgs['source_id']], $twitterDataByKgs);
        if (empty(array_get($twitter, 'image_url'))) {
            $twitter['image_url'] = null;
        } else {
            $twitter['image_url'] = explode(self::SEPARATOR, array_get($twitter, 'image_url'));
        }

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'twitter' => $twitter->toArray(),
            ],
        ];

        return $ret;
    }

    public function getTwitterList(array $categoryCodeList, string $openId = '')
    {
        $twitterFollow = '';
        if (!empty($openId) && count($categoryCodeList) == 1) {
            $categoryCode = $categoryCodeList[0];
            $twitterGuard = $this->twitterGuard->getLastTwitterGuard($openId, $categoryCode);
            $twitterFollow = $twitterGuard['status'];
        }

        $twitterList = $this->twitter->getTwitterList($categoryCodeList);
        $twitterIdList = array_column($twitterList, 'id');
        $twitterLikeCountList = $this->articleLike->getLikeCountListByArticleIdList($twitterIdList, ArticleLike::TYPE_TWITTER);
        $twitterLikeCountList = array_column($twitterLikeCountList, 'cnt', 'article_id');

        if (!empty($openId)) {
            $twitterLikeList = $this->articleLike->getMyArticleLikeList($openId, ArticleLike::TYPE_TWITTER);
            $twitterLikeIdList = array_column($twitterLikeList, 'article_id');
        }

        foreach ($twitterList as &$twitter) {
            $twitter['like_count'] = array_key_exists($twitter['id'], $twitterLikeCountList) ? $twitterLikeCountList[$twitter['id']] : 0;
            if (!empty($openId)) {
                $twitter['like'] = (int)in_array($twitter['id'], $twitterLikeIdList);
            }
        }

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'twitter_list' => $twitterList,
                'twitter_follow' => $twitterFollow,
            ],
        ];

        return $ret;
    }

    public function getTwitterListOfPaging(int $pageNo, int $pageSize, string $categoryCode)
    {
        $twitterList = Twitter::where('category_code', $categoryCode)
            ->orderBy('created_at', 'desc')
            ->skip($pageSize * ($pageNo - 1))
            ->take($pageSize)
            ->get()
            ->toArray();

        $twitterIdList = array_column($twitterList, 'id');

        $twitterLikeCountList = $this->articleLike->getLikeCountListByArticleIdList($twitterIdList, ArticleLike::TYPE_TWITTER);
        $twitterIdList = array_column($twitterLikeCountList, 'article_id');
        $twitterLikeCountList = array_column($twitterLikeCountList, 'cnt', 'article_id');

        foreach ($twitterList as &$twitter) {
            if (in_array(array_get($twitter, 'id'), $twitterIdList)) {
                $twitter['like_count'] = $twitterLikeCountList[$twitter['id']];
            } else {
                $twitter['like_count'] = 0;
            }
        }

        return $twitterList;
    }

    public function getTwitterCnt(string $categoryCode)
    {
        $twitterCnt = Twitter::where('category_code', $categoryCode)->count();
        return $twitterCnt;
    }


    public function getTwitterApprovedCategoryCodeList(string $openId)
    {
        $approvedList = $this->twitterGuard->getApprovedList($openId);
        $categoryCodeList = array_column($approvedList, 'category_code');

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'category_code_list' => $categoryCodeList,
            ],
        ];

        return $ret;
    }

    public function getCustomerPrivateMessageList(array $condition)
    {
        $privateMessageGuard = $this->privateMessageGuard->getLastPrivateMessageGuard($condition['teacher_id'], $condition['open_id']);

        $privateMessageList = $this->privateMessage->getPrivateMessageList($condition);

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'private_message_list' => $privateMessageList,
                'private_message_follow' => $privateMessageGuard['status'],
            ],
        ];

        return $ret;
    }

    public function getPrivateMessageList(array $condition)
    {
        $privateMessageList = $this->privateMessage->getPrivateMessageList($condition);

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'private_message_list' => $privateMessageList,
            ],
        ];

        return $ret;
    }

    public function postPrivateMessage(array $privateMessageData)
    {
        $privateMessage = $this->privateMessage->create($privateMessageData);

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'private_message' => $privateMessage->toArray(),
            ],
        ];

        return $ret;
    }

    public function createPrivateMessageRequest(int $teacherId, string $openId, string $sourceType = 'customer')
    {
        $requestData = [
            'teacher_id' => $teacherId,
            'open_id' => $openId,
            'operator_user_id' => 0,
            'status' => PrivateMessageGuard::STATUS_REQUEST,
            'source_type' => $sourceType,
        ];

        try {
            $privateMessageGuard = $this->privateMessageGuard->getLastPrivateMessageGuard($teacherId, $openId);
            if ($privateMessageGuard['status'] == PrivateMessageGuard::STATUS_REJECT) {
                if ($privateMessageGuard['source_type'] == PrivateMessageGuard::SOURCE_RE_REVIEW) {
                    $requestData['review_status'] = PrivateMessageGuard::REVIEW_REJECT;
                }
                $privateMessageGuard = $this->privateMessageGuard->create($requestData)->toArray();
            }
        } catch (Exception $e) {
            $privateMessageGuard = $this->privateMessageGuard->create($requestData)->toArray();
        }

        $teacher = $this->teacher->getTeacherInfo($teacherId);
        $user = $this->user->getUserInfo($teacher['user_id']);
        $systemNotice = $this->systemNotice->noticeCustomer('私信申请通知', sprintf(SystemNotice::NOTICE_CONTENT_PM_REQUEST, $user['name']), $openId);

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'private_message_request' => $privateMessageGuard,
                'system_notice' => $systemNotice,
            ],
        ];

        return $ret;
    }

    public function processPrivateMessageRequest(int $privateMessageGuardId, int $operate)
    {
        $currentUserId = Auth::user()->id;
        $privateMessageGuard = $this->privateMessageGuard->process($privateMessageGuardId, $operate, $currentUserId);

        $teacher = $this->teacher->getTeacherInfo($privateMessageGuard['teacher_id']);
        $user = $this->user->getUserInfo($teacher['user_id']);

        switch ($privateMessageGuard['status']) {
            case PrivateMessageGuard::STATUS_APPROVE:
                $systemNoticeContent = SystemNotice::NOTICE_CONTENT_PM_PROCESS_APPROVED;
                break;
            case PrivateMessageGuard::STATUS_REJECT:
                $systemNoticeContent = SystemNotice::NOTICE_CONTENT_PM_PROCESS_DENIED;
                break;
        }

        $systemNotice = $this->systemNotice->noticeCustomer('私信申请通知', sprintf($systemNoticeContent, $user['name']), array_get($privateMessageGuard, 'open_id'));

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'private_message_request' => $privateMessageGuard,
                'system_notice' => $systemNotice,
            ],
        ];

        return $ret;
    }

    public function getSessionList(int $teacherId)
    {
        $openIdList = $this->privateMessage->getOpenIdListByTeacherId($teacherId);

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'session_list' => $openIdList,
            ],
        ];

        return $ret;
    }

    public function getTwitterRequestList(array $cond)
    {
        $requestList = $this->twitterGuard->getRequestList($cond);

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'twitter_request_list' => $requestList,
            ],
        ];

        return $ret;
    }

    public function getTwitterRequestListOfPaging(int $pageNo, int $pageSize, array $credentials)
    {
        $cond = [];

        $query = TwitterGuard::select();

        $status = array_get($credentials, 'status');
        if ($status != NULL) {
            if (3 == $status) {
                $cond[] = ['status', '=', TwitterGuard::STATUS_REJECT];
                $cond[] = ['source_type', '=', TwitterGuard::SOURCE_AUTO_PROGRAM];
                $cond[] = ['is_qualified', '=', TwitterGuard::UN_QUALIFIED];
            } elseif (TwitterGuard::STATUS_REJECT == $status) {
                $query = $query->where(function ($query) use ($status) {
                    $query->where([
                        ['status', '=', $status],
                        ['source_type', '=', TwitterGuard::SOURCE_AUTO_PROGRAM],
                        ['is_qualified', '=', TwitterGuard::IS_QUALIFIED],
                    ])->orWhere([
                        ['status', '=', $status],
                        ['source_type', '<>', TwitterGuard::SOURCE_AUTO_PROGRAM]
                    ]);
                });
            } else {
                $cond[] = ['status', '=', $status];
            }

            if (!empty($cond)) {
                $query = $query->where($cond);
            }
        }

        $customerName = array_get($credentials, 'customer_name');
        if (!empty($customerName)) {
            $customerList = $this->customer->getCustomerListByName($customerName);
            $customerOpenIdList = array_column($customerList, 'open_id');
            $query = $query->whereIn('open_id', $customerOpenIdList);
        }

        $twitterRequestList = $query->orderBy('created_at', 'desc')
            ->skip($pageSize * ($pageNo - 1))
            ->take($pageSize)
            ->get()
            ->toArray();

        $openIdList = array_column($twitterRequestList, 'open_id');
        $customerList = $this->customer->getCustomerList($openIdList);
        $customerList = array_column($customerList, NULL, 'open_id');
        $customerOpenIdList = array_column($customerList, 'open_id');

        $userIdList = array_column($twitterRequestList, 'operator_user_id');
        $userList = $this->user->getUserListByUserIdList($userIdList);
        $userList = array_column($userList, NULL, 'id');
        $userIdList = array_column($userList, 'id');
        
        $categoryList = $this->category->getCategoryList();
        $categoryList = array_column($categoryList, NULL, 'code');
        $categoryCodeList = array_column($categoryList, 'code');

        foreach ($twitterRequestList as &$twitterRequest) {
            if (in_array(array_get($twitterRequest, 'open_id'), $customerOpenIdList)) {
                $twitterRequest['customer_name'] = $customerList[$twitterRequest['open_id']]['name'];
            }

            if (in_array(array_get($twitterRequest, 'category_code'), $categoryCodeList)) {
                $twitterRequest['category_name'] = $categoryList[$twitterRequest['category_code']]['name'];
            }

            if (in_array(array_get($twitterRequest, 'operator_user_id'), $userIdList)) {
                $twitterRequest['operator_user_name'] = $userList[$twitterRequest['operator_user_id']]['name'];
            } else {
                $twitterRequest['operator_user_name'] = '';
            }
        }

        return $twitterRequestList;
    }

    public function getTwitterRequestCnt(array $credentials)
    {
        $cond = [];

        $query = TwitterGuard::select();

        $status = array_get($credentials, 'status');
        if ($status != NULL) {
            if (3 == $status) {
                $cond[] = ['status', '=', TwitterGuard::STATUS_REJECT];
                $cond[] = ['source_type', '=', TwitterGuard::SOURCE_AUTO_PROGRAM];
                $cond[] = ['is_qualified', '=', TwitterGuard::UN_QUALIFIED];
            } elseif (TwitterGuard::STATUS_REJECT == $status) {
                $query = $query->where(function ($query) use ($status) {
                    $query->where([
                        ['status', '=', $status],
                        ['source_type', '=', TwitterGuard::SOURCE_AUTO_PROGRAM],
                        ['is_qualified', '=', TwitterGuard::IS_QUALIFIED],
                    ])->orWhere([
                        ['status', '=', $status],
                        ['source_type', '<>', TwitterGuard::SOURCE_AUTO_PROGRAM],
                    ]);
                });
            } else {
                $cond[] = ['status', '=', $status];
            }

            if (!empty($cond)) {
                $query = $query->where($cond);
            }
        }

        $customerName = array_get($credentials, 'customer_name');
        if (!empty($customerName)) {
            $customerList = $this->customer->getCustomerListByName($customerName);
            $customerOpenIdList = array_column($customerList, 'open_id');
            $query = $query->whereIn('open_id', $customerOpenIdList);
        }

        $twitterRequestCnt = $query->count();

        return $twitterRequestCnt;
    }

    public function getPrivateMessageRequestList(array $cond)
    {
        $requestList = $this->privateMessageGuard->getRequestList($cond);

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'private_message_request_list' => $requestList,
            ],
        ];

        return $ret;
    }

    public function getPrivateMessageRequestListOfPaging(int $pageNo, int $pageSize, array $credentials)
    {
        $cond = [];

        $status = array_get($credentials, 'status');
        if ($status !== NULL) {
            if (3 == $status) {
                $cond[] = ['status', '=', PrivateMessageGuard::STATUS_REJECT];
                $cond[] = ['source_type', '=', PrivateMessageGuard::SOURCE_RE_REVIEW];
            } elseif (2 == $status) {
                $cond[] = ['status', '=', $status];
                $cond[] = ['source_type', '<>', PrivateMessageGuard::SOURCE_RE_REVIEW];
            } else {
                $cond[] = ['status', '=', $status];
            }
        }

        $query = PrivateMessageGuard::where($cond);

        $customerName = array_get($credentials, 'customer_name');
        if (!empty($customerName)) {
            $customerList = $this->customer->getCustomerListByName($customerName);
            $customerOpenIdList = array_column($customerList, 'open_id');
            $query = $query->whereIn('open_id', $customerOpenIdList);
        }

        $privateMessageRequestList = $query->orderBy('created_at', 'desc')
            ->skip($pageSize * ($pageNo - 1))
            ->take($pageSize)
            ->get()
            ->toArray();

        $openIdList = array_column($privateMessageRequestList, 'open_id');
        $customerList = $this->customer->getCustomerList($openIdList);
        $customerList = array_column($customerList, NULL, 'open_id');
        $customerOpenIdList = array_column($customerList, 'open_id');

        $userIdList = array_column($privateMessageRequestList, 'operator_user_id');
        $userList = $this->user->getUserListByUserIdList($userIdList);
        $userList = array_column($userList, NULL, 'id');
        $userIdList = array_column($userList, 'id');

        $categoryList = $this->category->getCategoryList();
        $categoryList = array_column($categoryList, NULL, 'code');
        $categoryCodeList = array_column($categoryList, 'code');

        $teacherList = $this->teacher->getTeacherListByCategoryCodeList($categoryCodeList);
        $teacherList = array_column($teacherList, NULL, 'id');
        $teacherIdList = array_column($teacherList, 'id');
        $teacherUserIdList = array_column($teacherList, 'user_id');

        $teacherUserList = $this->user->getUserListByUserIdList($teacherUserIdList);
        $teacherUserList = array_column($teacherUserList, NULL, 'id');
        $teacherUserIdList = array_column($teacherUserList, 'id');

        foreach ($teacherList as &$teacher) {
            if (in_array(array_get($teacher, 'user_id'), $teacherUserIdList)) {
                $teacher['name'] = $teacherUserList[$teacher['user_id']]['name'];
                if (empty($teacher['icon_url'])) {
                    $teacher['icon_url'] = $teacherUserList[$teacher['user_id']]['icon_url'];
                }
            }
            if (in_array(array_get($teacher, 'category_code'), $categoryCodeList)) {
                $teacher['category_name'] = $categoryList[$teacher['category_code']]['name'];
            }
        }

        foreach ($privateMessageRequestList as &$request) {
            if (in_array(array_get($request, 'open_id'), $customerOpenIdList)) {
                $request['customer_name'] = $customerList[$request['open_id']]['name'];
            }
            if (in_array(array_get($request, 'operator_user_id'), $userIdList)) {
                $request['operator_user_name'] = $userList[$request['operator_user_id']]['name'];
            } else {
                $request['operator_user_name'] = '';
            }
            if (in_array(array_get($request, 'teacher_id'), $teacherIdList)) {
                $request['teacher'] = $teacherList[$request['teacher_id']];
            }
        }

        return $privateMessageRequestList;
    }

    public function getPrivateMessageRequestCnt(array $credentials)
    {
        $cond = [];

        $status = array_get($credentials, 'status');
        if ($status !== NULL) {
            if (3 == $status) {
                $cond[] = ['status', '=', PrivateMessageGuard::STATUS_REJECT];
                $cond[] = ['source_type', '=', PrivateMessageGuard::SOURCE_RE_REVIEW];
            } elseif (2 == $status) {
                $cond[] = ['status', '=', $status];
                $cond[] = ['source_type', '<>', PrivateMessageGuard::SOURCE_RE_REVIEW];
            } else {
                $cond[] = ['status', '=', $status];
            }
        }

        $query = PrivateMessageGuard::where($cond);

        $customerName = array_get($credentials, 'customer_name');
        if (!empty($customerName)) {
            $customerList = $this->customer->getCustomerListByName($customerName);
            $customerOpenIdList = array_column($customerList, 'open_id');
            $query = $query->whereIn('open_id', $customerOpenIdList);
        }

        $privateMessageRequestCnt = $query->count();

        return $privateMessageRequestCnt;
    }

    public function readManagePrivateMessage(int $privateMessageId)
    {
        $userId = Auth::user()->id;
        try {
            $teacherList = $this->teacher->getTeacherListByUserId($userId);
            $teacherIdList = array_column($teacherList, 'id');
            $privateMessage = $this->privateMessage->readPrivateMessage($privateMessageId, PrivateMessage::DIRECTION_UP, $teacherIdList);
        } catch (Exception $e) {
            abort(403);
        }

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'private_message' => $privateMessage,
            ],
        ];

        return $ret;
    }

    public function readCustomerPrivateMessage(int $privateMessageId, $openId)
    {
        if (empty($openId)) {
            abort(401);
        }

        try {
            $privateMessage = $this->privateMessage->readPrivateMessage($privateMessageId, PrivateMessage::DIRECTION_DOWN, $openId);
        } catch (Exception $e) {
            abort(403);
        }

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'private_message' => $privateMessage,
            ],
        ];

        return $ret;
    }

    public function getLastPrivateMessageRequest(string $openId, int $teacherId)
    {
        try {
            $pmGuard = $this->privateMessageGuard->getLastPrivateMessageGuard($teacherId, $openId);
            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'private_message_guard' => $pmGuard,
                ],
            ];
        } catch (Exception $e) {
            $ret = [
                'code' => PM_NOT_FOUND,
            ];
        }

        return $ret;
    }

    public function getPageTwitterList(array $categoryCodeList, int $twitterId, int $pageSize, string $openId = '', array $operatorUserIdList = [], bool $hasReferContent = true, int $month = 0)
    {
        $twitterList = $this->twitter->getPageTwitterList($categoryCodeList, $twitterId, $pageSize, $operatorUserIdList, $hasReferContent, $month);
        $twitterIdList = array_column($twitterList, 'id');
        $twitterLikeCountList = $this->articleLike->getLikeCountListByArticleIdList($twitterIdList, ArticleLike::TYPE_TWITTER);
        $twitterLikeCountList = array_column($twitterLikeCountList, 'cnt', 'article_id');

        if (!empty($openId)) {
            $twitterLikeList = $this->articleLike->getMyArticleLikeList($openId, ArticleLike::TYPE_TWITTER);
            $twitterLikeIdList = array_column($twitterLikeList, 'article_id');
        }

        foreach ($twitterList as &$twitter) {
            $twitter['like_count'] = array_key_exists($twitter['id'], $twitterLikeCountList) ? $twitterLikeCountList[$twitter['id']] : 0;
            if (!empty($openId)) {
                $twitter['like'] = (int)in_array($twitter['id'], $twitterLikeIdList);
            }
            
            // 兼容多图片问题
            if (empty(array_get($twitter, 'image_url'))) {
                $twitter['image_url'] = null;
            } else {
                $twitter['image_url'] = explode(self::SEPARATOR, array_get($twitter, 'image_url'));
            }
        }

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'twitter_list' => $twitterList,
            ],
        ];

        return $ret;
    }

    public function getUnfeedTwitterList(array $categoryCodeList)
    {
        $twitterList = $this->twitter->getUnfeedTwitterList($categoryCodeList);

        // 兼容多图片问题
        foreach ($twitterList as &$twitter) {
            if (empty(array_get($twitter, 'image_url'))) {
                $twitter['image_url'] = null;
            } else {
                $twitter['image_url'] = explode(self::SEPARATOR, array_get($twitter, 'image_url'));
            }
        }

        return $twitterList;
    }

    public function setTwitterFeed(array $twitterIdList)
    {
        $this->twitter->setTwitterFeed($twitterIdList);
    }

    public function twitterRemove(int $twitterId)
    {
        try{
            $twitterInfo = self::getTwitterInfo($twitterId);
            $this->logManager->createOperationLog(self::TWITTER, Auth::id(), json_encode($twitterInfo), self::DELETE);

            $this->twitter->removeRecord($twitterId);

            $ret = ['code' => SYS_STATUS_OK];
            return $ret;
        }catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
        }
    }

    public function getTwitterInfo(int $twitterId)
    {
        $twitterInfo = $this->twitter->getTwitterInfo($twitterId);

        if (!empty($twitterInfo)) {
            if (empty(array_get($twitterInfo, 'image_url'))) {
                $twitterInfo['image_url'] = null;
            } else {
                $twitterInfo['image_url'] = explode(self::SEPARATOR, array_get($twitterInfo, 'image_url'));
            }
        }

        return $twitterInfo;
    }

    public function getTwitterInfoBySourceId(string $sourceId)
    {
        $twitterInfo = $this->twitter->getTwitterInfoBySourceId($sourceId);

        // 兼容多图片问题
        if (!empty($twitterInfo)) {
            if (empty(array_get($twitterInfo, 'image_url'))) {
                $twitterInfo['image_url'] = null;
            } else {
                $twitterInfo['image_url'] = explode(self::SEPARATOR, array_get($twitterInfo, 'image_url'));
            }
        }

        return $twitterInfo;
    }

    public function likeTwitter(int $twitterId, string $openId, $udid = '', $sessionId = '', $userType = '')
    {
        $like = $this->articleLike->record($twitterId, 'twitter', $openId, $udid, $sessionId, $userType);

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'like' => (int)array_get($like, 'status'),
                'effect_rows' => (int)array_get($like, 'effect_rows'),
            ],
        ];

        return $ret;
    }

    public function likeStatistic($twitterId, $type, $userType, $isLike)
    {
        $likeStatistic = $this->likeStatistic->likeSum($twitterId, $type, $userType, $isLike);

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'like_statistic' => $likeStatistic,
            ],
        ];

        return $ret;
    }

    public function getLikeOfTwitter($articleId, $openId = '', $udid = '')
    {

        if (!empty($openId) || !empty($udid)) {
            $like = $this->articleLike->getRecord($articleId, 'twitter', $openId, $udid);
        } else {
            $like = 0;
        }


        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'like' => $like,
            ],
        ];

        return $ret;
    }

    public function getLikeSumOfTwitter($articleId, $type)
    {
        $statisticInfo = $this->likeStatistic->getLikeStatisticInfo($articleId, $type);
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'statisticInfo' => $statisticInfo,
            ],
        ];

        return $ret;
    }

    public function getCategoryCodeList(string $openId, string $categoryGroupCode)
    {
        $twitterGuardList = $this->twitterGuard->getTwitterGuardList($openId);
        $approvedTwitterGuardList = self::formatTwitterGuardListForPersonal($twitterGuardList);
        
        $approvedCategoryCodeList = [];
        foreach ($approvedTwitterGuardList as $twitterGuard) {
            if ($twitterGuard['status'] == TwitterGuard::STATUS_REQUEST || $twitterGuard['status'] == TwitterGuard::STATUS_APPROVE) {
                array_push($approvedCategoryCodeList, $twitterGuard['category_code']);
            }
        }

        $categoryGroupList = $this->categoryGroup->getCategoryGroupListByCode($categoryGroupCode);
        $categoryCodeList = array_column($categoryGroupList, 'category_code');
        $categoryList = $this->category->getCategoryListByCodeList($categoryCodeList);

        foreach($categoryList as &$category) {
            if (in_array($category['code'], $approvedCategoryCodeList)) {
                $category['disabled'] = true;
            } else {
                $category['disabled'] = false;
            }
        }

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'category_list' => $categoryList,
            ],
        ];

        return $ret;
    }

    public function getPageTwitterListByRoomId($roomId, $startTime, $endTime, $hasReferContent = true)
    {
        $twitterList = $this->twitter->getTwitterListByRoomId($roomId, $startTime, $endTime, $hasReferContent);

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                 'twitter_list' => $twitterList, 
             ]
        ];

        return $ret;
    }

    public function getTeacherListByIdList(array $teacherIdList)
    {
        $teacherList = $this->teacher->getTeacherListByIdList($teacherIdList);

        $userIdList = array_column($teacherList, 'user_id');

        $userList = $this->user->getUserListByUserIdList($userIdList);

        $userList = array_column($userList, NULL, 'id');

        $ucList = $this->uc->getUcListByUserIdList($userIdList);

        $ucList = array_column($ucList, NULL, 'user_id');

        foreach ($teacherList as &$teacher) {
            $teacher['name'] = $userList[$teacher['user_id']]['name'];
            $teacher['icon_url'] = empty($teacher['icon_url']) ? $userList[$teacher['user_id']]['icon_url'] : $teacher['icon_url'];
        }

        foreach ($teacherList as &$teacher) {
            $teacher['enterprise_userid'] = $ucList[$teacher['user_id']]['enterprise_userid'];
        }

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'teacher_list' => $teacherList,
            ],
        ];

        return $ret;
    }

    private function formatTwitterGuardListForPersonal(array $twitterGuardList)
    {
        $result = [];
        $categoryList = [];

        foreach ($twitterGuardList as $twitterGuard) {
            if (array_key_exists($twitterGuard['category_code'], $categoryList)) {
                continue;
            }

            $categoryList[$twitterGuard['category_code']] = $twitterGuard;
            if (TwitterGuard::STATUS_APPROVE == $twitterGuard['status'] || TwitterGuard::STATUS_REQUEST == $twitterGuard['status']) {
                $result[] = $twitterGuard;
            }
        }

        return $result;
    }

    public function getTwitterInfoByRefer(string $refCategoryCode, string $refId)
    {
        try {
            $twitter = Twitter::where('ref_category_code', $refCategoryCode)->where('ref_id', $refId)->take(1)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new MatrixException("新闻没找到{$refId}", CONTENT_NOT_FOUND);
        }

        return $twitter;
    }
}


