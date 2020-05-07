<?php

namespace Matrix\Http\Controllers\Interaction;

use Illuminate\Http\Request;
use Matrix\Http\Controllers\Controller;
use Matrix\Contracts\InteractionContract;
use Matrix\Contracts\TwitterManager;
use Matrix\Contracts\UcManager;
use Matrix\Contracts\UserManager;
use Matrix\Contracts\CategoryManager;

use Matrix\Exceptions\MatrixException;
use Matrix\Exceptions\InteractionException;
use Matrix\Exceptions\UcException;
use Exception;
use Log;
use DB;

class ContentForwardController extends Controller
{
    //
    protected $request;
    protected $twitter;
    protected $ucenter;
    protected $interaction;
    protected $user;
    protected $category;

    public function __construct(Request $request, TwitterManager $twitter, UcManager $ucenter, InteractionContract $interaction, UserManager $user, CategoryManager $category)
    {
        $this->request = $request;
        $this->twitter = $twitter;
        $this->ucenter = $ucenter;
        $this->interaction = $interaction;
        $this->user = $user;
        $this->category = $category;
    }

    public function toTwitter()
    {
        $credentials = $this->request->validate([
            'content' => 'string',
            'is_reply' => 'required|integer',
            'author_user_id' => 'required|integer',
            'ref_type' => 'required|string',
            'ref_id' => 'required|string',
            'ref_title' => 'required|string',
            'ref_thumb' => 'string|nullable',
            'ref_summary' => 'string|nullable',
        ]);

	    $credentials['ref_thumb'] = (string)array_get($credentials, 'ref_thumb');
	    $credentials['ref_summary'] = (string)array_get($credentials, 'ref_summary');

        DB::beginTransaction();
        try {
            $sessionId = $this->request->header('X-SessionId');
            $ucUserInfo = $this->ucenter->getUserInfoBySessionId($sessionId);
            $enterpriseUserId = array_get($ucUserInfo, 'data.user.qyUserId');

            $content = $this->request->input('content');
            $credentials['content'] = (string)$content;

            $isReply = array_get($credentials, 'is_reply');
            if (!empty($isReply)) { // need send content to reply
                $nickName = array_get($ucUserInfo, 'data.user.nickName');
                if (empty($nickName)) {
                    throw new InteractionException('回复失败，没有设置昵称', INTERACTION_NICKNAME_NOT_SET);
                }

                $authorUserId = array_get($credentials, 'author_user_id');

                $type = $this->request->input('ref_type');
                $articleId = $this->request->input('ref_id');
                $articleTitle = $this->request->input('ref_title');
                $content = $this->request->input('content');

                $newReply = $this->interaction->reply($type, $articleId, $articleTitle, $authorUserId, $content, array_get($ucUserInfo, 'data'), 0, '', '', 1, 1); // refId, refContent, refOpenId
                unset($newReply['session_id']);
            }

            $newTwitter = $this->twitter->forward2Twitter($credentials, $enterpriseUserId);

            $ret = [
                'code' => SYS_STATUS_OK,
                'msg' => '转发成功',
                'data' => [
                    'twitter' => $newTwitter,
                ],
            ];
            DB::commit();

            if (!empty(array_get($credentials, 'author_user_id'))) {
                try {
                    $userInfo = $this->user->getUserInfo(array_get($credentials, 'author_user_id'));
                    $qyUserId = array_get($userInfo, 'ucInfo.enterprise_userid');
                    $userIds = [$qyUserId];
    
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
                        'traceType' => array_get($credentials, 'ref_type'),
                        'userIds' => $userIds,
                        'sender' => $nickName,
                        'senderUserId' => array_get($ucUserInfo, 'data.user.qyUserId'),
                    ];
    
                    $this->ucenter->sendMessageToUc($messageFormData);
    
                } catch (UcException $e) {
                    Log::error('消息同步到UC 出错'.$e->getMessage(), $e->getCode());
                    $ret['error'] = '消息同步到 UC 出错';
                }
            }
        } catch (MatrixException $e) {
            Log::error("MatrixException: {$e->getMessage()}", [$e]);
            $ret = [
                'code' => $e->getCode(),
                'msg' => $e->getMessage(),
            ];
            DB::rollBack();
        } catch (Exception $e) {
            Log::error("InteractionException: 不可预料的错误", [$e]);
            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => '转发失败',
            ];
            DB::rollBack();
        }

        return $ret;
    }
}
