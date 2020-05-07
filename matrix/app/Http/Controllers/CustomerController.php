<?php

namespace Matrix\Http\Controllers;

use Illuminate\Http\Request;
use Matrix\Contracts\CustomerManager;
use Matrix\Contracts\UcManager;
use Matrix\Contracts\TwitterManager;
use Matrix\Contracts\CategoryManager;
use Matrix\Models\PrivateMessageGuard;

use Exception;
use Log;

class CustomerController extends Controller
{
    //
    const CATEGORY_XIANGJIANGLUNJIAN = 'xiangjianglunjian';
    const RFZQ_PLAT_NAME = 'rfzq';

    private $request;
    private $customerManager;
    private $ucenter;
    private $twitterManager;
    private $categoryManager;

    public function __construct(Request $request, CustomerManager $customerManager, UcManager $ucenter, TwitterManager $twitterManager, CategoryManager $categoryManager)
    {
        $this->request = $request;
        $this->ucenter = $ucenter;
        $this->customerManager = $customerManager;
        $this->twitterManager = $twitterManager;
        $this->categoryManager = $categoryManager;
    }

    public function getCustomerInfo(string $openId)
    {
        $customerList = $this->customerManager->getCustomerList([$openId]);
        $ret = [
            'code' => empty($customerList) ? SYS_STATUS_ERROR_UNKNOW : SYS_STATUS_OK,
        ];
        $ret['data'] = [
            'customer' => $customerList[0],
        ];

        return $ret;
    }

    public function showInfoCard(string $openId)
    {
        $ucInfoData = $this->ucenter->getUserInfoByOpenId($openId, 'hk');
        $ucUserInfo = array_get($ucInfoData, 'data');
        Log::debug('user info by openId: ', [$ucUserInfo]);
        $customerData = [
            'open_id' => (string)array_get($ucUserInfo,  'openId'),
            'code' => (string)array_get($ucUserInfo,     'customerCode'),
            'name' => (string)array_get($ucUserInfo,     'name'),
            'mobile' => (string)array_get($ucUserInfo,   'mobile'),
            'icon_url' => (string)array_get($ucUserInfo, 'iconUrl'),
        ];
        $customerInfo = $this->customerManager->updateCustomer($customerData);
        $customerInfo['money_total'] = (double)array_get($ucUserInfo, 'moneyTotal');
        $customerInfo['money_date'] = empty(array_get($ucUserInfo, 'cashBalance.lastInTime')) ? '无' : date('Y-m-d H:i:s',strtotime(array_get($ucUserInfo, 'cashBalance.lastInTime')));
        $totalRemitHKD =  (double)array_get($ucUserInfo, 'cashBalance.totalRemitHKD');
        $totalWithdrawHKD = (double)array_get($ucUserInfo, 'cashBalance.totalWithdrawHKD');
        $customerInfo['net_proceeds'] = $totalRemitHKD - $totalWithdrawHKD;
        $categoryCodeListData = $this->twitterManager->getTwitterApprovedCategoryCodeList($openId);
        $categoryCodeList = array_get($categoryCodeListData, 'data.category_code_list');
        $allCategoryListData = $this->categoryManager->getCategoryList();
        $allCategoryList = array_get($allCategoryListData, 'data.category_list');
        $allCategoryList = array_column($allCategoryList, NULL, 'code');
        $customerInfo['approved_twitter_guard_list'] = [];
        foreach ($categoryCodeList as $categoryCode) {
            $customerInfo['approved_twitter_guard_list'][] = [
                'code' => $categoryCode,
                'name' => $allCategoryList[$categoryCode]['name'],
            ];
        }

        $privateMessageRequestListData = $this->twitterManager->getPrivateMessageRequestList([
            'status' => [
                PrivateMessageGuard::STATUS_REQUEST,
                PrivateMessageGuard::STATUS_APPROVE,
                PrivateMessageGuard::STATUS_REJECT,
            ],
            'open_id' => [$openId],
        ]);

        $privateMessageGuardList = [];
        $teacherIdList = [];

        $privateMessageRequestList = array_get($privateMessageRequestListData, 'data.private_message_request_list');
        foreach ($privateMessageRequestList as $privateMessageRequest) {
            if (array_key_exists($privateMessageRequest['teacher_id'], $privateMessageGuardList)) {
                continue;
            }
            $privateMessageGuardList[$privateMessageRequest['teacher_id']] = $privateMessageRequest;
            if (PrivateMessageGuard::STATUS_APPROVE == $privateMessageRequest['status']) {
                $teacherIdList[] = $privateMessageRequest['teacher_id'];
            }
        }

        $teacherListData = $this->categoryManager->getTeacherListByIdList($teacherIdList);
        $teacherList = array_get($teacherListData, 'data.teacher_list');
        $customerInfo['approved_pm_guard_list'] = $teacherList;

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'customer_info_card' => $customerInfo,
            ],
        ];

        return $ret;
    }

    public function getCustomerInfoByMobile(string $mobile)
    {
        $ucInfoData = [];
        try {
            $ucInfoData = $this->ucenter->getUserInfoByMobile($mobile, 'hk');
            $openId = array_get($ucInfoData, 'data.user.openId');

            $totalRemitHKD =  (double)array_get($ucInfoData, 'data.user.cashBalance.totalRemitHKD');
            $totalWithdrawHKD = (double)array_get($ucInfoData, 'data.user.cashBalance.totalWithdrawHKD');
            $netProceeds = $totalRemitHKD - $totalWithdrawHKD;

            $this->checkServiceResult($ucInfoData, 'UcService');

            $categoryCodeListOfUnSubscribed = $this->twitterManager->getCategoryCodeList($openId, self::CATEGORY_XIANGJIANGLUNJIAN);
            $categoryList = array_get($categoryCodeListOfUnSubscribed, 'data.category_list');

            $isRfzqUser = array_get($ucInfoData, 'data.user.platName') == self::RFZQ_PLAT_NAME ? true : false;

            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'customer_info' => [
                        'name' => array_get($ucInfoData, 'data.user.name'),
                        'mobile' => array_get($ucInfoData, 'data.user.mobile'),
                        'is_rfzq_user' => $isRfzqUser,
                        'net_proceeds' => $netProceeds,
                    ],
                    'category_list' => $categoryList,
                ],
            ];
        } catch (Exception $e) {
            $ret = [
                'code' => array_get($ucInfoData, 'code', SYS_STATUS_ERROR_UNKNOW),
            ];
        }

        return $ret;
    }
}
