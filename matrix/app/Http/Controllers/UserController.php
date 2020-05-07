<?php

namespace Matrix\Http\Controllers;

use Illuminate\Http\Request;

use Matrix\Contracts\UserManager;
use Matrix\Contracts\UcManager;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Exception;
use Log;
use Matrix\Exceptions\MatrixException;

class UserController extends Controller
{
    //
    private $request;
    private $ucManager;

    public function __construct(Request $request, UcManager $ucManager)
    {
        $this->request = $request;
        $this->ucManager = $ucManager;
    }

    public function logout(UserManager $userManager)
    {
        $logoutResult = $userManager->logout();
        $this->checkServiceResult($logoutResult, 'UserService');

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'logout' => true,
            ],
        ];

        return $ret;
    }

    public function getUcEnterpriseLoginUrl(UcManager $ucManager)
    {
        $ucEnterpriseLoginUrlData = $ucManager->getEnterpriseLoginUrl();
        $this->checkServiceResult($ucEnterpriseLoginUrlData, 'UcService');
        $ucEnterpriseLoginUrl = array_get($ucEnterpriseLoginUrlData, 'data.url');

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'loginUrl' => $ucEnterpriseLoginUrl,
            ],
        ];

        return $ret;
    }

    public function ucEnterpriseLoginCallback(UserManager $userManager, UcManager $ucManager)
    {
        $token = $this->request->cookie('token');
        $enterpriseUserInfoData = $ucManager->getUserInfoByToken($token);
        $this->checkServiceResult($enterpriseUserInfoData, 'UcService');
        $enterpriseUserId = array_get($enterpriseUserInfoData, 'data');
        $loginResultData = $userManager->loginByEnterpriseUserId($enterpriseUserId);
        $loginResult = array_get($loginResultData, 'code');

        $frontUrl = config('front.url');
        return redirect()->intended($frontUrl);
    }

    public function getTeacherList(UserManager $userManager)
    {
        $teacherList = $userManager->getTeacherList(); 
        return [
            'code' => SYS_STATUS_OK,
            'teacherList' => $teacherList,
        ];
    }

    public function getUserInfo(UserManager $userManager, $userId = 0)
    {
        $userId = empty($userId) ? Auth::id() : $userId;
        $userInfo = $userManager->getUserInfo($userId);

        try {
            $this->checkServiceResult($userInfo, 'UserService');
            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'user_info' => array_get($userInfo, 'userInfo', []),
                    'uc_info' => array_get($userInfo, 'ucInfo', []),
                    'teacher_tabs' => array_get($userInfo, 'teacherTabs', []),
                ],
            ];
        } catch (ServiceException $e) {
           $ret = ['code' => USER_NOT_FOUND];
        }

        return $ret;
    }

    public function getUserList(UserManager $userManager)
    {
        $reqData = $this->request->validate([
            'page_no' => 'nullable|integer',
            'page_size' => 'nullable|integer',
            'name' => 'nullable|string',
            'type' => 'nullable|string',
        ]);

        try {
            $pageNo = array_get($reqData, 'page_no', 1);
            $pageSize = array_get($reqData, 'page_size', 10);

            $userList = $userManager->getUserList($pageNo, $pageSize, $reqData);
            $userCnt = $userManager->getUserCnt($reqData);

            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'user_list' => $userList,
                    'user_cnt' => $userCnt,
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

    public function getAllUserList(UserManager $userManager)
    {
        try {
            $allUserList = $userManager->getAllUserList();

            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'user_list' => $allUserList
                ],
            ];
        }  catch (MatrixException $e) {
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

    public function create(UserManager $userManager)
    {
        $reqData = $this->request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'password' => 'nullable|string',
            'type' => 'required|string',
            'icon_url' => 'nullable|string',
            'enterprise_userid' => 'nullable|string',
            'cert_no' => 'nullable|string',
            'description' => 'nullable|string',
            'teacher_tabs' => 'nullable|array',
        ]);

        $enterpriseBindData = $userManager->createUser($reqData);

        try {
            $this->checkServiceResult($enterpriseBindData, 'UserService');
            $userInfo = array_get($enterpriseBindData, 'data.userInfo');
            $enterpriseBind = array_get($enterpriseBindData, 'data.enterpriseBind');
            $userInfo['enterprise_userid'] = array_get($enterpriseBind, 'enterprise_userid');
            $userInfo['teacher_tabs'] = array_get($enterpriseBindData, 'data.teacherTabs');
            $ret = [
                'code' => SYS_STATUS_OK,
                'user_info' => $userInfo,
            ];
        } catch(Exception $e) {
            $ret = [
                'code' => array_get($enterpriseBindData, 'code', SYS_STATUS_ERROR_UNKNOW),
            ];
        }

        // 同步用户信息到 UC
        try {
            $credentials = [
                'qy_user_id' => array_get($reqData, 'enterprise_userid'),
                'description' => array_get($reqData, 'description'),
                'cert_no' => array_get($reqData, 'cert_no'),
                'name' => array_get($reqData, 'name'),
                'icon_url' => array_get($reqData, 'icon_url'),
            ];
            $this->ucManager->syncUserInfo($credentials);
            $ret['msg'] = '数据添加成功，用户信息同步到UC成功';
        } catch (UcException $e) {
            Log::error($e->getMessage(), [$e]);
            $ret['msg'] = '数据添加成功，用户信息同步到UC失败:' . $e->getMessage();
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret['msg'] = '数据添加成功，用户信息同步到UC失败: 发生了一个不可预知的错误';
        }
        

        return $ret;
    }

    public function activeUser(UserManager $userManager, $userId, $active)
    {
        $activeUserData = $userManager->activeUser($userId, $active);
        try {
            $this->checkServiceResult($activeUserData, 'UserService');
            $userInfo = array_get($activeUserData, 'data.userInfo');
            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'user_info' => $userInfo,
                ],
            ];
        } catch(Exception $e) {
            $ret = [
                'code' => array_get($activeUserData, 'code', SYS_STATUS_ERROR_UNKNOW),
            ];
        }

        return $ret;
    }

    public function update(UserManager $userManager, $userId)
    {
        $reqData = $this->request->validate([
            'name' => 'nullable|string',
            'email' => 'nullable|email',
            'password' => 'nullable|string',
            'type' => 'nullable|string',
            'icon_url' => 'nullable|string',
            'enterprise_userid' => 'nullable|string',
            'cert_no' => 'nullable|string',
            'description' => 'nullable|string',
            'teacher_tabs' => 'nullable|array',
        ]);

        $userInfoData = $userManager->updateUser($userId, $reqData);
        try {
            $this->checkServiceResult($userInfoData, 'UserService');
            $userInfo = array_get($userInfoData, 'data.userInfo');
            $ucInfo = array_get($userInfoData, 'data.ucInfo');
            $teacherTab = array_get($userInfoData, 'data.teacherTabs');
            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'user_info' => $userInfo,
                    'uc_info' => $ucInfo,
                    'teacher_tabs' => $teacherTab,
                ],
            ];
        } catch(Exception $e) {
            $ret = [
                'code' => array_get($userInfoData, 'code', SYS_STATUS_ERROR_UNKNOW),
            ];
        }

        // 同步用户信息到 UC
        try {
            $credentials = [
                'qy_user_id' => array_get($reqData, 'enterprise_userid'),
                'description' => array_get($reqData, 'description'),
                'cert_no' => array_get($reqData, 'cert_no'),
                'name' => array_get($reqData, 'name'),
                'icon_url' => array_get($reqData, 'icon_url'),
            ];
            $this->ucManager->syncUserInfo($credentials);
            $ret['msg'] = '数据更新成功，用户信息同步到UC成功';
        } catch (UcException $e) {
            Log::error($e->getMessage(), [$e]);
            $ret['msg'] = '数据更新成功，用户信息同步到UC失败:' . $e->getMessage();
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            $ret['msg'] = '数据更新成功，用户信息同步到UC失败: 发生了一个不可预知的错误';
        }

        return $ret;
    }

    public function getVideoAuthorList(UserManager $userManager)
    {
        $videoAuthorList = $userManager->getVideoAuthorList(); 
        $List = array_get($videoAuthorList, 'videoAuthorList');
        return [
            'code' => SYS_STATUS_OK,
            'data' => [
                'video_author_list' => $List,
            ],
        ];
    }

    public function selectedUser(UserManager $userManager, $userId, $selected)
    {
        $selectedUserData = $userManager->selectedUser($userId, $selected);
        try {
            $this->checkServiceResult($selectedUserData, 'UserService');
            $userInfo = array_get($selectedUserData, 'data.userInfo');
            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'user_info' => $userInfo,
                ],
            ];
        } catch(Exception $e) {
            $ret = [
                'code' => array_get($selectedUserData, 'code', SYS_STATUS_ERROR_UNKNOW),
            ];
        }

        return $ret;
    }

    public function getTeacherTabList(UserManager $userManager)
    {
        $teacherTabListData = $userManager->getTeacherTabList();
        try {
            $this->checkServiceResult($teacherTabListData, 'UserService');
            $teacherTabList = array_get($teacherTabListData, 'data.teacher_tab_list');
            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'teacher_tab_list' => $teacherTabList,
                ],
            ];
        } catch(Exception $e) {
            $ret = [
                'code' => array_get($teacherTabListData, 'code', SYS_STATUS_ERROR_UNKNOW),
            ];
        }

        return $ret;
    }

    public function getSignTypeList(UserManager $userManager)
    {
        $signTypeList = $userManager->getSignTypeList();
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'sign_type_list' => $signTypeList,
            ],
        ];
        return $ret;
    }

    public function uploadIcon()
    {
        if (!$this->request->hasFile('image')) {
            abort(401);
        }
        $path = $this->request->image->store('public/user/icon');

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'path' => config('app.cdn.base_url').Storage::url($path),
            ],
        ];

        return $ret;
    }
}
