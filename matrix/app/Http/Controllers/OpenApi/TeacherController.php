<?php

namespace Matrix\Http\Controllers\OpenApi;

use Illuminate\Http\Request;
use Matrix\Contracts\UserManager;
use Matrix\Contracts\UcManager;
use Matrix\Contracts\CustomerManager;
use Matrix\Contracts\TeacherManager;
use Exception;
use Log;

class TeacherController extends BaseController
{
    //
    const USER_GROUP_STOCK_A = 'teacher_stock_a';
    const USER_GROUP_SELECT_STOCK_A = 'teacher_select_stock_a';

    public function getTeacherListForSelect(UserManager $userManager, string $userGroupCode = self::USER_GROUP_SELECT_STOCK_A, string $parentUserGroupCode = self::USER_GROUP_STOCK_A)
    {
        $userList = $userManager->getUserListByGroupCode($userGroupCode);
        $userIdList = array_column($userList, 'id');

        $parentUserList = $userManager->getUserListByGroupCode($parentUserGroupCode);
        $parentUserIdList = array_column($parentUserList, 'id');

        $ucList = $userManager->getUcListByUserIdList($userIdList);
        $ucList = array_column($ucList, 'enterprise_userid', 'user_id');

        $teacherList = collect($userList)->reject(function ($user, $key) use ($parentUserIdList) {
//            return empty(array_get($user, 'selected'));
// TODO
// Remove the parent group contant where child user group function online
            if (empty(array_get($user, 'selected')) || !in_array(array_get($user, 'id'), $parentUserIdList)) {
                return true;
            }
        })->map(function ($user) use ($ucList) {
            return [
                'name' => array_get($user, 'name'),
                'icon_url' => array_get($user, 'icon_url'),
                'enterprise_userid' => array_get($ucList, array_get($user, 'id')),
            ];
        })->toArray();

        shuffle($teacherList);

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'teacher_list' => array_values($teacherList),
            ],
        ];

        return $ret;
    }

    public function selectTeacher(Request $request, UserManager $userManager, TeacherManager $teacherManager, CustomerManager $customerManager, UcManager $ucenter, string $enterpriseUserId)
    {
        $credentials = $request->validate([
            'customer_code' => 'required',
        ]);

        try {
            $customerCode = array_get($credentials, 'customer_code');
            $customerInfo = $ucenter->getUserInfoByCustomerCode($customerCode);
            $customerData = [
                'open_id' => (string)array_get($customerInfo, 'data.openId'),
                'code' => (string)array_get($customerInfo, 'data.customerCode'),
                'name' => (string)array_get($customerInfo, 'data.name'),
                'mobile' => (string)array_get($customerInfo, 'data.mobile'),
                'icon_url' => (string)array_get($customerInfo, 'data.iconUrl'),
                'qy_userid' => (string)array_get($customerInfo, 'data.qyUserId'),
            ];
            $customerInfo = $customerManager->updateCustomer($customerData);
            $customerOpenId = array_get($customerInfo, 'open_id');

            $teacherUserInfo = $userManager->getUserByEnterpriseUserId($enterpriseUserId);
            $teacherUserId = array_get($teacherUserInfo, 'data.id');
            $teacherManager->followTeacher($teacherUserId, $customerOpenId);

            $ret = ['code' => SYS_STATUS_OK];
        } catch (Exception $e) {
            Log::error("Select Teacher Error.", [$e]);
            $ret = ['code' => SYS_STATUS_ERROR_UNKNOW];
        }

        return $ret;
    }
}
