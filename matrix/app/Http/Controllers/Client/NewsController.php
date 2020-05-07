<?php

namespace Matrix\Http\Controllers\Client;

use Illuminate\Http\Request;
use Matrix\Contracts\UcManager;
use Matrix\Contracts\UserManager;
use Matrix\Models\UserGroup;
use Matrix\Contracts\UserGroupManager;
use Matrix\Exceptions\MatrixException;
use Exception;
use Log;

class NewsController extends Controller
{
    //
    protected $request;
    protected $ucenter;
    protected $user;
    protected $userGroup;

    public function __construct(Request $request, UcManager $ucenter, UserManager $user, UserGroupManager $userGroup)
    {
        $this->request = $request;
        $this->ucenter = $ucenter;
        $this->user = $user;
        $this->userGroup = $userGroup;
    }

    public function getNewInfo()
    {
        $loginUrl = $this->h5WechatAutoLogin($this->request, $this->ucenter);

        if(!empty($loginUrl)){
            return redirect()->away($loginUrl);
        }
        $isTeacher = 0;
        $sessionId = (string)$this->request->cookie('X-SessionId');
        if (!empty($sessionId)) {
            try {
                $sessionIdExpired = time() + 60 * 60 * 10;
                setcookie('X-SessionId', $sessionId, $sessionIdExpired, config('session.path'), config('session.domain'), false, true);
                $ucUserInfo = $this->ucenter->getUserInfoBySessionId($sessionId);
                $enterpriseUserId = array_get($ucUserInfo, 'data.user.qyUserId');
                if (!empty($enterpriseUserId)) {
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
                }
            } catch (MatrixException $e) {
                $ret = [
                    'session_id' => $sessionId,
                     'is_forward_teacher' => 0,
                ];
            } catch (Exception $e) {
                Log::error($e->getMessage(), [$e]);
                $ret = [
                    'session_id' => $sessionId,
                     'is_forward_teacher' => 0,
                ];
            }
        }

        $ret = [
            'session_id' => $sessionId,
            'is_forward_teacher' => $isTeacher,
        ];
        return view('news.detail', $ret);
    }
}
