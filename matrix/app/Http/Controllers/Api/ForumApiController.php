<?php

namespace Matrix\Http\Controllers\Api;

use Illuminate\Http\Request;
use Matrix\Contracts\UcManager;
use Matrix\Contracts\ContentGuardContract;
use Matrix\Contracts\ForumManager;

class ForumApiController extends Controller
{

    private $request;
    private $forumManager;

    public function __construct(Request $request, ForumManager $forumManager)
    {
        $this->request = $request;
        $this->forumManager = $forumManager;
    }

    public function getForumList(UcManager $ucManager, ContentGuardContract $contentGuardContract)
    {
        $ret = [
            "code" => '',
            "msg" => ''
        ];
        $jwt = $this->request->cookie('x-jwt');
        $userDetail = $ucManager->getUserDetail($jwt);
        if (empty(array_get($userDetail,'data.openId'))) {
            $callback = $ucManager->getH5EnterpriseLoginUrl();
            $ret['code'] = CMS_API_X_JWT_INVALID;
            $ret['msg'] = 'Expired x-jwt';
            $ret['callback_url'] = array_get($callback, 'data.callback');
            return $this->respAdapter($ret);
        }
        $customerContentAccessCode = $ucManager->getCustomerProductCodeList(array_get($userDetail,'data.openId'));
        $forumIdList = $contentGuardContract->getOneForumAccessIdList(array_get($customerContentAccessCode,'data.product_key_list'));
        $forumsData = $this->forumManager->getForumsData(array_get($forumIdList,'data.forum_access_id_list'));

        $ret['data'] = array_get($forumsData,'data');
        $ret['code'] = SYS_STATUS_OK;
        $ret['msg'] = null;

        return $ret;
    }

    public function getForumDetail($forumId)
    {
        $ret = [
            "code" => '',
            'data' => '',
            "msg" => ''
        ];

        $forumsData = $this->forumManager->getForumsDataById($forumId);

        $forums = array_get($forumsData,'data');

        if (sizeof($forums) < 1) {
            $ret['data'] = null;
            $ret['code'] = CMS_API_NOT_IN_SHOW_TIME;
            $ret['msg'] = "当前数据不在展示时间段内";
            return $this->respAdapter($ret);
        }

        $ret['data'] = array_get($forumsData,'data');
        $ret['code'] = SYS_STATUS_OK;
        $ret['msg'] = null;

        return $ret;
    }
}