<?php

namespace Matrix\Http\Controllers\Client;

use Illuminate\Http\Request;

use Matrix\Models\UserGroup;
use Matrix\Contracts\UcManager;
use Matrix\Contracts\UserManager;
use Matrix\Contracts\UserGroupManager;
use Matrix\Contracts\VideoManager;
use Matrix\Contracts\InteractionContract;
use Matrix\Exceptions\MatrixException;
use Exception;
use Log;

class HistoryController extends Controller
{
    //
    const ARTICLE_TYPE = 'talkshow';

    protected $request;
    protected $video;
    protected $ucenter;
    protected $user;
    protected $userGroup;
    protected $interaction;

    private $iv;
    private $key;
    private $cipher;

    public function __construct(Request $request, VideoManager $video, UcManager $ucenter, UserManager $user, UserGroupManager $userGroup, InteractionContract $interaction)
    {
        $this->request = $request;
        $this->video = $video;
        $this->user = $user;
        $this->userGroup = $userGroup;
        $this->ucenter = $ucenter;
        $this->interaction = $interaction;

        $this->iv = config('video.history.aes_iv');
        $this->key = config('video.history.aes_key');
        $this->cipher = config('video.history.aes_cipher');
    }

    protected function encrypt(string $plaintext)
    {
        return base64_encode(openssl_encrypt($plaintext, $this->cipher, $this->key, OPENSSL_RAW_DATA, $this->iv));
    }

    protected function decrypt(string $encryptedText)
    {
        return openssl_decrypt(base64_decode($encryptedText), $this->cipher, $this->key, OPENSSL_RAW_DATA, $this->iv);
    }

    public function getHistoryData()
    {
        $credentials = $this->request->validate([
            'dcode' => 'required|string',
        ]);

        $loginUrl = $this->h5WechatAutoLogin($this->request, $this->ucenter);

        if(!empty($loginUrl)){
            return redirect()->away($loginUrl);
        }

        $udid = '';
        $currentOpenId = '';
        $isTeacher = 0;
        $sessionId = (string)($this->request->header('X-SessionId') ?? $this->request->cookie('X-SessionId'));
        $currentUserInfo = [];
        $accessCodeList = ['basic', 'dp2', 'index', 'i_dpqs', 'i_lnhy', 'i_zlcb'];

        if (empty($sessionId)) {
            try {
                $anonymousUserInfo = $this->ucenter->getAnonymousUserInfo();
                $sessionId = (string)array_get($anonymousUserInfo, 'data.sessionId');
                $accessCodeList = $this->ucenter->getAccessCodeBySessionId($sessionId);

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
                $currentUserInfo = $this->ucenter->getUserInfoBySessionId($sessionId);
                Log::info($currentUserInfo);
            } catch (MatrixException $e) {
                Log::info("SessionId: $sessionId is expired. UC said: {$e->getMessage()};");
                $currentUserInfo = [];
            } catch (Exception $e) {
                Log::error($e->getMessage(), [$e]);
                abort(500, '还没找着你想要的东西就坏了');
            }
            if (empty($currentUserInfo)) {
                try {
                    $anonymousUserInfo = $this->ucenter->getAnonymousUserInfo();
                    $sessionId = (string)array_get($anonymousUserInfo, 'data.sessionId');
                    $accessCodeList = $this->ucenter->getAccessCodeBySessionId($sessionId);
                    Log::info($anonymousUserInfo);
                } catch (MatrixException $e) {
                    Log::info("SessionId: $sessionId is expired. UC said: {$e->getMessage()};");
                    $accessCodeList = ['basic', 'dp2', 'index', 'i_dpqs', 'i_lnhy', 'i_zlcb'];
                } catch (Exception $e) {
                    Log::error($e->getMessage(), [$e]);
                    abort(500, '还没找着你想要的东西就坏了');
                }
            }
        }

        if (!empty($sessionId) && !empty($currentUserInfo)) {
            $enterpriseUserId = array_get($currentUserInfo, 'data.user.qyUserId');
            $userMobile = array_get($currentUserInfo, 'data.user.mobile');

            if (!empty($enterpriseUserId)) {
                try {
                    $teacherUserData = $this->user->getUserByEnterpriseUserId($enterpriseUserId);
                    $teacherUserId = array_get($teacherUserData, 'data.id');
                    $teacherUserActive = array_get($teacherUserData, 'data.active');

                    if (!empty($teacherUserId) && !empty($teacherUserActive)) {
                        $teacherUserListData = $this->userGroup->getUserListByUserGroupCode(UserGroup::USER_GROUP_CODE_APPROVED_REPLY);
                        $teacherUserList = array_get($teacherUserListData, 'user_list');

                        if (!empty($teacherUserList)) {
                            $userIdList = array_column($teacherUserList, 'id');
                            $isTeacher = (int)in_array($teacherUserId, $userIdList);
                        }
                    }
                } catch (MatrixException $e) {
                    Log::info($e->getMessage());
                } catch (Exception $e) {
                    Log::error($e->getMessage(), [$e]);
                    abort(500, '还没找着你想要的东西就坏了');
                }
            }

            $currentOpenId = (string)array_get($currentUserInfo, 'data.user.openId');
            try {
                $accessCodeList = $this->ucenter->getAccessCodeBySessionId($sessionId);
                Log::info("$sessionId: ".json_encode($accessCodeList));
            } catch (MatrixException $e) {
                Log::info("SessionId: $sessionId is expired. UC said: {$e->getMessage()};");
                $accessCodeList = ['basic', 'dp2', 'index', 'i_dpqs', 'i_lnhy', 'i_zlcb'];
            } catch (Exception $e) {
                Log::error($e->getMessage(), [$e]);
                abort(500, '还没找着你想要的东西就坏了');
            }
        }

        try {
            $detailId = $this->decrypt($this->request->dcode);
            $data = $this->video->getHistoryData((int)$detailId);
            if (!empty($data->content_url) && (empty($data->original_content) && empty($data->content_local_data))) {
                return redirect()->away($data->content_url);
            }

            $article = [
                'id' => $data->detail_id,
                'title' => $data->title,
                'type' => 'talkshow',
                'content_url' => $data->content_url,
                'msg_type' => $data->msg_type,
                'published_at' => date('Y-m-d H:i:s', $data->send_time),
                'original_content' => $data->original_content,
                'content' => base64_encode($data->original_content),
                'is_forward_teacher' => $isTeacher,
                'session_id' => $sessionId,
                'is_reply' => isset($userMobile) && !empty($userMobile) ? 1 : 0,
                'forward_open_id' => (string)$currentOpenId,
                'guide_msg' => $data->ad_guide,
            ];
          
            if (!empty($data->owner_id)) {
                $teacherUserData = $this->user->getUserByEnterpriseUserId($data->owner_id);
                $teacherUserId = array_get($teacherUserData, 'data.id');
                $teacherUserActive = array_get($teacherUserData, 'data.active');
                $article['teacher_user_id'] = array_get($teacherUserData, 'data.id');
                $article['teacher_name'] = array_get($teacherUserData, 'data.name');
                $article['teacher_icon_url'] = array_get($teacherUserData, 'data.icon_url');
            } else {
                $article['teacher_user_id'] = 0;
                $article['teacher_name'] = '';
                $article['teacher_icon_url'] = '';
            }

            $isLikeData = $this->interaction->getLikeRecord($data->detail_id, self::ARTICLE_TYPE, (string)$currentOpenId, $udid);
            $likeSumData = $this->interaction->getLikeSum($data->detail_id, self::ARTICLE_TYPE);

            $voteCnt = (int)array_get($likeSumData, 'data.statisticInfo.like_sum');
            $voteCnt = $voteCnt > 999 ? '999+' : $voteCnt;

            $article['is_like'] = (int)array_get($isLikeData, 'data.like');
            $article['like_sum'] = $voteCnt;

            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => [
                    'article' => $article,
                ],
            ];

            return view('history.detail', $ret);
        } catch (MatrixException $e) {
            abort(404, '并没有找到你想要的东西');
        } catch (Exception $e) {
            Log::error($e->getMessage(), [$e]);
            abort(500, '还没找着你想要的东西就坏了');
        }
    }
}
