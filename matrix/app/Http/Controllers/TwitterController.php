<?php

namespace Matrix\Http\Controllers;

use Illuminate\Http\Request;
use Matrix\Contracts\TwitterManager;
use Matrix\Contracts\CategoryManager;
use Matrix\Contracts\CustomerManager;
use Matrix\Contracts\UserManager;
use Matrix\Models\TwitterGuard;
use Matrix\Models\PrivateMessage;
use Auth;
use Exception;
use Log;
use Matrix\Exceptions\MatrixException;
use Matrix\Models\PrivateMessageGuard;

class TwitterController extends Controller
{
    //
    private $request;
    private $twitterManager;
    private $customerManager;
    private $userManager;
    private $categoryManager;

    public function __construct(Request $request, TwitterManager $twitterManager, CustomerManager $customerManager, UserManager $userManager, CategoryManager $categoryManager)
    {
        $this->request = $request;
        $this->twitterManager = $twitterManager;
        $this->customerManager = $customerManager;
        $this->userManager = $userManager;
        $this->categoryManager = $categoryManager;
    }

    public function readPrivateMessage(int $privateMessageId)
    {
        try {
            $privateMessageRes = $this->twitterManager->readManagePrivateMessage($privateMessageId);
            $privateMessage = array_get($privateMessageRes, 'data.private_message');

            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'private_message' => $privateMessage,
                ],
            ];
        } catch (Exception $e) {
            Log::error("Read private message fail.", [$e->getMessage()]);
            abort(403);
        }

        return $ret;
    }

    public function getTwitterRequestList()
    {
        $condition = $this->request->validate([
            'customer_name' => 'string|nullable',
            'status' => 'numeric|between:0,3|nullable',
        ]);

        $cond = [];
        $cond['status'] = [0, 1, 2];

        $customerName = array_get($condition, 'customer_name');
        if (array_key_exists('customer_name', $condition) && !empty($customerName)) {
            $customerList = $this->customerManager->getCustomerListByName($customerName);
            $customerNameList = array_column($customerList, 'name', 'open_id');
            $openIdList = array_column($customerList, 'open_id');
            $cond['open_id'] = $openIdList;
        }

        $status = array_get($condition, 'status');
        if (array_key_exists('status', $condition) && $status !== NULL) {
            if (3 == $status) {
                $cond['status'] = [TwitterGuard::STATUS_REJECT];
                $cond['source_type'] = TwitterGuard::SOURCE_AUTO_PROGRAM;
                $cond['is_qualified'] = TwitterGuard::UN_QUALIFIED;
            } else {
                $cond['status'] = [$status];
            }
        }

        $requestListRes = $this->twitterManager->getTwitterRequestList($cond);
        $requestList = array_get($requestListRes, 'data.twitter_request_list');

        $openIdList = array_column($requestList, 'open_id');
        $customerList = $this->customerManager->getCustomerList($openIdList);
        $customerNameList = array_column($customerList, 'name', 'open_id');

        $categoryListRes = $this->categoryManager->getCategoryList();
        $categoryList = array_get($categoryListRes, 'data.category_list');
        $categoryNameList = array_column($categoryList, 'name', 'code');

        $userIdList = array_column($requestList, 'operator_user_id');
        $userListRes = $this->userManager->getUserListByUserIdList($userIdList);
        $userList = array_get($userListRes, 'data.user_list');
        $userNameList = array_column($userList, 'name', 'id');

        foreach ($requestList as &$request) {
            $request['customer_name'] = $customerNameList[$request['open_id']];
            $request['category_name'] = $categoryNameList[$request['category_code']];
            $request['operator_user_name'] = empty($request['operator_user_id']) ? '' : $userNameList[$request['operator_user_id']];
        }

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'twitter_request_list' => $requestList,
            ],
        ];

        return $ret;
    }

    public function getTwitterRequestListOfPaging ()
    {
        $credentials = $this->request->validate([
            'page_no' => 'nullable|integer',
            'page_size' => 'nullable|integer',
            'customer_name' => 'nullable|string',
            'status' => 'numeric|between:0,3|nullable'
        ]);

        try {
            $pageNo = array_get($credentials, 'page_no', 1);
            $pageSize = array_get($credentials, 'page_size', 10);

            $twitterRequestList = $this->twitterManager->getTwitterRequestListOfPaging($pageNo, $pageSize, $credentials);
            $twitterRequestCnt = $this->twitterManager->getTwitterRequestCnt($credentials);

            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'twitter_request_list' => $twitterRequestList,
                    'twitter_request_cnt' => $twitterRequestCnt,
                ],
            ];
        } catch (MatrixException $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = [
                'code' => $e->getCode(),
                'msg' => $e->getMessage()
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

    public function getPrivateMessageRequestList()
    {
        $condition = $this->request->validate([
            'customer_name' => 'string|nullable',
            'status' => 'numeric|between:0,3|nullable',
        ]);

        $cond = [];
        $cond['status'] = [0, 1, 2];

        $customerName = array_get($condition, 'customer_name');
        if (array_key_exists('customer_name', $condition) && !empty($customerName)) {
            $customerList = $this->customerManager->getCustomerListByName(array_get($condition, 'customer_name'));
            $openIdList = array_column($customerList, 'open_id');
            $cond['open_id'] = $openIdList;
        }

        $status = array_get($condition, 'status');
        if (array_key_exists('status', $condition) && $status !== NULL) {
            if (3 == $status) {
                $cond['status'] = [PrivateMessageGuard::STATUS_REJECT];
                $cond['source_type'] = PrivateMessageGuard::SOURCE_RE_REVIEW;
            } else {
                $cond['status'] = [array_get($condition, 'status')];
            }
        }

        $requestListRes = $this->twitterManager->getPrivateMessageRequestList($cond);
        $requestList = array_get($requestListRes, 'data.private_message_request_list');

        $openIdList = array_column($requestList, 'open_id');
        $customerList = $this->customerManager->getCustomerList($openIdList);
        $customerNameList = array_column($customerList, 'name', 'open_id');

        $userIdList = array_column($requestList, 'operator_user_id');
        $userListRes = $this->userManager->getUserListByUserIdList($userIdList);
        $userList = array_get($userListRes, 'data.user_list');
        $userNameList = array_column($userList, 'name', 'id');

        $categoryListRes = $this->categoryManager->getCategoryList();
        $categoryList = array_get($categoryListRes, 'data.category_list');
        $categoryCodeList = array_column($categoryList, 'code');
        $categoryNameList = array_column($categoryList, 'name', 'code');

        $teacherListRes = $this->categoryManager->getTeacherListByCategoryCodeList($categoryCodeList);
        $teacherList = array_get($teacherListRes, 'data.teacher_list');

        $teacherUserIdList = array_column($teacherList, 'user_id');
        $teacherUserListRes = $this->userManager->getUserListByUserIdList($teacherUserIdList);
        $teacherUserList = array_get($teacherUserListRes, 'data.user_list');
        $teacherUserNameList = array_column($teacherUserList, 'name', 'id');

        foreach ($teacherList as &$teacher) {
            $teacher['name'] = $teacherUserNameList[$teacher['user_id']];
            $teacher['category_name'] = $categoryNameList[$teacher['category_code']];
        }

        $teacherList = array_column($teacherList, NULL, 'id');

        foreach ($requestList as &$request) {
            $request['customer_name'] = $customerNameList[$request['open_id']];
            $request['operator_user_name'] = empty($request['operator_user_id']) ? '' : $userNameList[$request['operator_user_id']];
            $request['teacher'] = $teacherList[$request['teacher_id']];
        }

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'private_message_request_list' => $requestList,
            ],
        ];

        return $ret;
    }

    public function getPrivateMessageRequestListOfPaging()
    {
        $credentials = $this->request->validate([
            'page_no' => 'nullable|integer',
            'page_size' => 'nullable|integer',
            'customer_name' => 'nullable|string',
            'status' => 'numeric|between:0,3|nullable',
        ]);

        try {
            $pageNo = array_get($credentials, 'page_no', 1);
            $pageSize = array_get($credentials, 'page_size', 10);

            $privateMessageRequestList = $this->twitterManager->getPrivateMessageRequestListOfPaging($pageNo, $pageSize, $credentials);
            $privateMessageRequestCnt = $this->twitterManager->getPrivateMessageRequestCnt($credentials);

            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'private_message_request_list' => $privateMessageRequestList,
                    'private_message_request_cnt' => $privateMessageRequestCnt,
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


    public function processTwitterRequest(int $twitterGuardId)
    {
        $options = $this->request->validate([
            'operate' => 'numeric|between:0,2',
        ]);
        $operate = array_get($options, 'operate');

        try {
            $twitterGuardData = $this->twitterManager->processTwitterRequest($twitterGuardId, $operate);

            $twitterGuard = array_get($twitterGuardData, 'data.twitter_request');

            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'twitter_guard' => $twitterGuard,
                ],
            ];
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = ['code' => SYS_STATUS_ERROR_UNKNOW];
        }

        return $ret;
    }

    public function addTwitterRequest()
    {
        $reqData = $this->request->validate([
            'mobile' => 'required|string',
            'checkList' => 'required|array',
        ]);
        
        $respData = $this->twitterManager->addTwitterRequest($reqData);

        try {
            $this->checkServiceResult($respData, 'TwitterService');
            $ret = [
                'code' => SYS_STATUS_OK,
                'msg' => array_get($respData, 'msg')
            ];
        } catch (Exception $e) {
            $ret = [
                'code' => array_get($respData, 'code', SYS_STATUS_ERROR_UNKNOW),
            ];
        }

        return $ret;
    }

    public function postTwitter()
    {
        $twitterData = $this->request->validate([
            'content' => 'string',
            'category_code' => 'string',
        ]);
        try {
            $twitterInfoData = $this->twitterManager->createTwitter($twitterData);
            $twitterInfo = array_get($twitterInfoData, 'data.twitter');
            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'twitter' => $twitterInfo,
                ],
            ];
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = ['code' => SYS_STATUS_ERROR_UNKNOW];
        }

        return $ret;
    }

    public function getTwitterList()
    {
        $options = $this->request->validate([
            'page_no' => 'nullable|integer',
            'page_size' => 'nullable|integer',
            'category_code' => 'string',
        ]);

        try {
            $pageNo = array_get($options, 'page_no', 1);
            $pageSize = array_get($options, 'page_size', 50);

            $categoryCode = array_get($options, 'category_code');
            $twitterList = $this->twitterManager->getTwitterListOfPaging($pageNo, $pageSize, $categoryCode);
            $twitterCnt = $this->twitterManager->getTwitterCnt($categoryCode);

            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'twitter_list' => $twitterList,
                    'twitter_cnt' => $twitterCnt,
                ],
            ];
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = ['code' => SYS_STATUS_ERROR_UNKNOW];
        }

        return $ret;
    }

    public function processPrivateMessageRequest(int $privateMessageGuardId)
    {
        $options = $this->request->validate([
            'operate' => 'integer|between:0,2',
        ]);
        $operate = array_get($options, 'operate');

        try {
            $privateMessageGuardData = $this->twitterManager->processPrivateMessageRequest($privateMessageGuardId, $operate);

            $privateMessageGuard = array_get($privateMessageGuardData, 'data.private_message_request');

            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'private_message_guard' => $privateMessageGuard,
                ],
            ];
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = ['code' => SYS_STATUS_ERROR_UNKNOW];
        }

        return $ret;
    }

    public function postPrivateMessage(CategoryManager $categoryManager)
    {
        $privateMessage = $this->request->validate([
            'open_id' => 'string',
            'teacher_id' => 'integer',
            'content' => 'string',
        ]);

        $privateMessage['direction'] = PrivateMessage::DIRECTION_DOWN;

        try {
            $teacherId = array_get($privateMessage, 'teacher_id');
            $teacherInfo = $categoryManager->getTeacherById($teacherId);
            if (Auth::user()->id != array_get($teacherInfo, 'data.teacher.user_id')) {
                abort(403);
            }
            $privateMessageData = $this->twitterManager->postPrivateMessage($privateMessage);

            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'private_message' => array_get($privateMessageData, 'data.private_message'),
                ],
            ];
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = ['code' => SYS_STATUS_ERROR_UNKNOW];
        }

        return $ret;
    }

    public function getPrivateMessageList()
    {
        $condition = $this->request->validate([
            'teacher_id' => 'integer',
            'open_id' => 'string',
            'read' => 'integer:0,1|nullable',
        ]);
        try {
            $privateMessageListData = $this->twitterManager->getCustomerPrivateMessageList($condition);
            $customerList = $this->customerManager->getCustomerList([array_get($condition, 'open_id')]);

            if (empty($customerList)) {
                abort(400);
            }

            $privateMessageList = array_get($privateMessageListData, 'data.private_message_list');
            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'customer' => $customerList[0],
                    'private_message_list' => $privateMessageList,
                ],
            ];
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = ['code' => SYS_STATUS_ERROR_UNKNOW];
        }

        return $ret;
    }

    public function getSessionList()
    {
        $options = $this->request->validate([
            'teacher_id' => 'required|integer',
        ]);

        try {
            $teacherId = array_get($options, 'teacher_id');
            $sessionListData = $this->twitterManager->getSessionList($teacherId);
            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'session_list' => array_get($sessionListData, 'data.session_list'),
                ],
            ];
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret = ['code' => SYS_STATUS_ERROR_UNKNOW];
        }

        return $ret;
    }
}
