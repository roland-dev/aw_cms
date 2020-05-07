<?php

namespace Matrix\Services;

use Matrix\Contracts\UcManager;
use Matrix\Exceptions\UcException;
use Illuminate\Support\Facades\Cache;
use Matrix\Models\Ucenter;
use Matrix\Models\Customer;
use Log;
use Exception;

class UcService extends HttpService implements UcManager
{
    const URI_BASE = '/api/v2/uc';
    const URI_AUTH_ENTERPRISE_QR = '/oauth/qy/qr';
    const URI_USER_INFO_TOKEN = '/session/check/token';
    const URI_USER_DETAIL_TOKEN = '/session/me';
    const URI_USER_DETAIL = '/facade/user/token/info';
    const URI_USER_PRODUCT_CODE = '/facade/user/products/opens'; // %s => {openId}
    const URI_USER_PRODUCT_CODE_BY_SESSIONID = '/facade/session/products';
    const URI_AUTH_ENTERPRISE_H5 = '/oauth/qy/entry';
    const URI_USER_INFO_SESSION_ID = '/facade/user/info/%s'; // %s => {sessionId}
    const URI_USER_INFO_CUSTOMER_CODE = '/facade/user/code/%s'; // %s => {customerCode}
    const URI_USER_INFO_OPEN_ID = '/facade/user/uc/%s';
    const URI_USER_INFO_MOBILE = '/facade/user/mobile/%s';
    const URI_USER_INFO_QYUSERID = '/facade/user/qy/%s'; // %s => {qyUserId}
    const URI_USER_NICKNAME = '/person/info';

    // Get Users Money
    const URI_USER_MONEY_OPEN_ID = '/facade/user/moneys';

    // friends
    const URI_FRIEND_ADD_BATCH = '/facade/friends/batchs';
    const URI_FRIEND_REMOVE_BATCH = '/facade/friends/batchs';

    // Get Access Code
    const URI_USER_ACCESS_OPENID = '/facade/user/accesses/opens/%s'; // %s => {openId}
    const URI_USER_ACCESS_SESSIONID = '/facade/user/accesses/session/%s'; // %s => {openId}


    const URI_SYNC_USER_INFO = '/facade/user/info';

    const URI_APP_WEBVIEW_URL = '/load/?to=%s'; // %s => urlencode($url)

    const LENGTH_NONCE = 32;

    // FBI Warning: URI_SELF means used by cms
    const URI_SELF_BASE = '/api/v1';
    const URI_SELF_LOGIN_CALLBACK = '/user/auth/uc';

    // 推送消息到UC
    const URI_MESSAGE_SEND = '/api/v2/im/gateway/message/send';

    const UC_USER_DETAIL_BY_JWT_OR_TOKEN_CACHE_FORMAT = 'user_info_by_jwt_or_token_%s';
    const UC_USER_DETAIL_CACHE_FORMAT = 'user_info_%s';
    const UC_USER_DETAIL_NAME_CACHE_FORMAT = 'user_info_name_%s';
    const UC_CUSTOMER_SERVICE_CODE_LIST_CACHE_FORMAT = 'customer_service_code_list_%s';
    const UC_CUSTOMER_PRODUCT_CODE_LIST_CACHE_FORMAT = 'customer_product_code_list_%s';
    const UC_CUSTOMER_PRODUCT_CODE_LIST_BY_SESSIONID_CACHE_FORMAT = 'customer_product_code_list_by_sessionid_%s';

    // Cache Constants
    const CACHE_USERINFO_SESSION_ID = 'cache_uc_%s_userinfo_sessionid_%s'; // %s => {business}, %s => {sessionId}
    const CACHE_USERINFO_OPEN_ID = 'cache_uc_%s_userinfo_openid_%s'; // %s => {business}, %s => {openId}
    const CACHE_USERINFO_MOBILE = 'cache_uc_%s_userinfo_mobile_%s'; // %s => {business}, %s => {mobile}
    const CACHE_USERINFO_QYUSERID = 'cache_uc_%s_userinfo_qyuserid_%s'; // %s => {business}, %s => {qyUserId}

    const CACHE_ACCESSCODE_OPENID = 'cache_uc_%s_accesscode_openid_%s'; // %s => {business}, %s => {openId}
    const CACHE_ACCESSCODE_SESSIONID = 'cache_uc_%s_accesscode_sessionid_%s'; // %s => {business}, %s => {openId}
    const CACHE_ACCESSCODE_TOKEN = 'cache_uc_accesscode_token_%s'; // %s => {token}

    protected $ucUrl;
    protected $algo;
    protected $siteKey;
    protected $siteSecret;

    public function __construct()
    {
        $this->appUrl = config('app.url');
        $this->ucUrl = config('uc.url');
        $this->algo = config('uc.guard.default.algo');
        $this->siteKey = config('uc.guard.default.siteKey');
        $this->siteSecret = config('uc.guard.default.siteSecret');

        parent::__construct($this->ucUrl);
    }

    public function checkUcResponse(array $resp)
    {
        Log::info('Uc response: ', [$resp]);
        $respCode = array_get($resp, 'code', SYS_STATUS_ERROR_UNKNOW);
        if ($respCode !== SYS_STATUS_OK) {
            throw new UcException("Response Code: $respCode .", USER_CENTER_STATUS_ERROR);
        }
    }

    public function getEnterpriseLoginUrl(string $callback = '')
    {
        $uri = sprintf('%s%s', self::URI_BASE, self::URI_AUTH_ENTERPRISE_QR);
        if (empty($callback)) {
            $callback = sprintf('%s%s', $this->appUrl, self::URI_SELF_LOGIN_CALLBACK);
        }
        $data = [
            'callback' => $callback,
        ];

        $resp = $this->getJson($uri, $data);

        $this->checkUcResponse($resp);

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => array_get($resp, 'data'),
        ];

        return $ret;
    }

    public function getUserInfoByToken(string $token)
    {
        // TODO modify it to new connect
        $uri = sprintf('%s%s', self::URI_BASE, self::URI_USER_INFO_TOKEN);
        $nonce = str_random(self::LENGTH_NONCE);
        $data = [
            'token' => $token,
            'nonce' => $nonce,
        ];
        $headers = [
            'uc-app-key' => $this->siteKey,
            'uc-access' => hash_hmac($this->algo, $nonce.$this->siteSecret, $this->siteKey),
        ];
        $resp = $this->postJson($uri, $data, $headers);

        $this->checkUcResponse($resp);

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => array_get($resp, 'data'),
        ];

        return $ret;
    }

    public function getUserDetailByJwt(string $jwt)
    {
        // TODO modify it to new connect
        $cacheKey = sprintf(self::UC_USER_DETAIL_CACHE_FORMAT, $jwt);
        $cacheNameKey = sprintf(self::UC_USER_DETAIL_NAME_CACHE_FORMAT, $jwt);
        $customerCode = Cache::get($cacheKey);
        $customerName = Cache::get($cacheNameKey);
        if (NULL === $customerCode || NULL === $customerName) {
            $uri = sprintf('%s%s', self::URI_BASE, self::URI_USER_DETAIL_TOKEN);
            $data = $headers = [];
            $cookies = [
                'x-jwt' => $jwt,
            ];
            $resp = $this->getJson($uri, $data, $headers, $cookies);
            $code = array_get($resp, 'code');
            if (SYS_STATUS_OK !== $code) {
                $ret = [ 'code' => USER_CENTER_STATUS_ERROR ];
                return $ret;
            }
            $userInfo = array_get($resp, 'data.user');
            $customerCode = array_get($resp, 'data.user.customerCode');
            $customerName = array_get($resp, 'data.user.name');
            Cache::put($cacheKey, $customerCode, 15);
            Cache::put($cacheNameKey, $customerName, 15);
        }
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'customer_code' => $customerCode,
                'customer_name' => $customerName,
            ],
        ];

        return $ret;
    }

    public function getAnonymousUserInfo(string $channel = 'zytg')
    {
        $uri = sprintf('%s%s', self::URI_BASE, self::URI_USER_DETAIL_TOKEN);

        $this->siteKey = config("uc.guard.$channel.siteKey");
        $this->siteSecret = config("uc.guard.$channel.siteSecret");
        $nonce = str_random(self::LENGTH_NONCE);
        $data = [
            'bizName' => $channel,
            'nonce' => $nonce,
        ];
        $headers = [
            'uc-app-key' => $this->siteKey,
            'uc-access' => hash_hmac($this->algo, $nonce.$this->siteSecret, $this->siteKey),
        ];
        $resp = $this->postUc($uri, $data, $headers);

        $this->checkUcResponse($resp);
        $ret = json_decode(array_get($resp, 'data'), true);

        return $ret;
    }

    /**
     * $parameter string x-jwt or token
     */
    public function getUserDetail(string $parameter)
    {
        // TODO modify it to new connect
        $cacheKey = sprintf(self::UC_USER_DETAIL_BY_JWT_OR_TOKEN_CACHE_FORMAT, $parameter);
        $openId = Cache::get($cacheKey);
        if (null === $openId) {
            $uri = sprintf('%s%s', self::URI_BASE, self::URI_USER_DETAIL);
            $nonce = str_random(self::LENGTH_NONCE);
            $data = [
                'nonce' => $nonce,
                'token' => $parameter
            ];
            $headers = [
                'uc-app-key' => $this->siteKey,
                'uc-access' => hash_hmac($this->algo, $nonce . $this->siteSecret, $this->siteKey),
            ];
            $resp = $this->getJson($uri, $data, $headers);
            $code = array_get($resp, 'code');
            if (SYS_STATUS_OK !== $code) {
                $ret = ['code' => USER_CENTER_STATUS_ERROR];
                return $ret;
            }
            $userInfo = array_get($resp, 'data.user');
            $openId = array_get($resp, 'data.user.openId');
            Cache::put($cacheKey, $openId, 15);
        }
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'openId' => $openId,
            ],
        ];

        return $ret;
    }

    public function getAccessCodeByToken(string $token)
    {
        // TODO modify it to new connect
        $cacheKey = sprintf(self::CACHE_ACCESSCODE_TOKEN, $token);
        $accessCodeList = Cache::get($cacheKey);
        if (null === $accessCodeList) {
            $uri = sprintf('%s%s', self::URI_BASE, self::URI_USER_DETAIL);
            $nonce = str_random(self::LENGTH_NONCE);
            $data = [
                'nonce' => $nonce,
                'token' => $token,
                'fields' => 'access'
            ];
            $headers = [
                'uc-app-key' => $this->siteKey,
                'uc-access' => hash_hmac($this->algo, $nonce . $this->siteSecret, $this->siteKey),
            ];
            $resp = $this->getJson($uri, $data, $headers);

            $this->checkUcResponse($resp);

            $accessCodeList = array_get($resp, 'data.user.accessCodes');

            Cache::put($cacheKey, $accessCodeList, 5);
        }

        return $accessCodeList;
    }

    public function getCustomerProductCodeList(string $openId)
    {
        // TODO modify it to support different business
        $cacheKey = sprintf(self::UC_CUSTOMER_PRODUCT_CODE_LIST_CACHE_FORMAT, $openId);
        $productKeyList = Cache::get($cacheKey);
        if (NULL === $productKeyList) {
            $uri = sprintf('%s%s/%s', self::URI_BASE, self::URI_USER_PRODUCT_CODE, $openId);
            $nonce = str_random(self::LENGTH_NONCE);
            $data = [
                'nonce' => $nonce,
            ];
            $headers = [
                'uc-app-key' => $this->siteKey,
                'uc-access' => hash_hmac($this->algo, $nonce.$this->siteSecret, $this->siteKey),
            ];
            $resp = $this->getJson($uri, $data, $headers);
            $code = array_get($resp, 'code');
            if (SYS_STATUS_OK !== $code) {
                $ret = ['code' => USER_CENTER_STATUS_ERROR];
                return $ret;
            }
            $productKeyList = array_get($resp, 'data');
            Cache::put($cacheKey, $productKeyList, 15);
        }

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'open_id' => $openId,
                'product_key_list' => $productKeyList,
            ],
        ];
        return $ret;
    }

    public function getCustomerProductCodeListBySessionId (string $sessionId) {
        $cacheKey = sprintf(self::UC_CUSTOMER_PRODUCT_CODE_LIST_BY_SESSIONID_CACHE_FORMAT, $sessionId);
        $productKeyList = Cache::get($cacheKey);
        if (NULL === $productKeyList) {
            $uri = sprintf('%s%s/%s', self::URI_BASE, self::URI_USER_PRODUCT_CODE_BY_SESSIONID, $sessionId);
            $nonce = str_random(self::LENGTH_NONCE);
            $data = [
                'nonce' => $nonce
            ];
            $headers = [
                'uc-app-key' => $this->siteKey,
                'uc-access' => hash_hmac($this->algo, $nonce.$this->siteSecret, $this->siteKey),
            ];
            $resp = $this->getJson($uri, $data, $headers);
            $this->checkUcResponse($resp);
            $productKeyList = array_get($resp, 'data');

            // 匿名用户不缓存
            if (count($productKeyList) > 1) {
                Cache::put($cacheKey, $productKeyList, 15);
            }
        }

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'session_id' => $sessionId,
                'product_key_list' => $productKeyList,
            ],
        ];

        return $ret;
    }

    public function getH5EnterpriseLoginUrl(string $callback = '')
    {
        $callbackUrl = sprintf('%s%s%s', $this->ucUrl, self::URI_BASE, self::URI_AUTH_ENTERPRISE_H5);

        if (!empty($callback)) {
            $callbackUrl .= '?';
            $callbackUrl .= http_build_query([
                'callback' => $callback,
            ]);
        }

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => [
                'callback' => $callbackUrl,
            ],
        ];

        return $ret;
    }
    
    /**
     * 通过sessionId 获取用户信息
     *  调用UC接口时，同时用户信息与权限集合
     * 
     * @param   string   $sessionId     APP sessionId
     * @param   string   $channel      UC配置，default:众赢投顾，
     * @param   boolean  $refresh     是否强制刷新
     * 
     */
    public function getUserInfoBySessionId(string $sessionId, string $channel = 'default', bool $refresh = false)
    {   
        $cacheKey = sprintf(self::CACHE_USERINFO_SESSION_ID, $channel, $sessionId);
        $ret = Cache::get($cacheKey);

        if (!empty($ret) && !$refresh) {
            return $ret;
        }

        $this->siteKey = config("uc.guard.$channel.siteKey");
        $this->siteSecret = config("uc.guard.$channel.siteSecret");
        $uri = sprintf('%s%s', self::URI_BASE, sprintf(self::URI_USER_INFO_SESSION_ID, $sessionId));
        $nonce = str_random(self::LENGTH_NONCE);
        $data = [ 
            'nonce' => $nonce,
            'fields' => 'access',//同时获取用户accessCodeList
        ];  
        $headers = [ 
            'uc-app-key' => $this->siteKey,
            'uc-access' => hash_hmac($this->algo, $nonce.$this->siteSecret, $this->siteKey),
        ];  
        $resp = $this->getJson($uri, $data, $headers);
        $this->checkUcResponse($resp);

        $openId = (string)array_get($resp, 'data.user.openId');
        if (!empty($openId)) {
            $customer = Customer::updateOrCreate(['open_id' => $openId], [
                'code' => (string)array_get($resp, 'data.user.customerCode'),
                'qy_userid' => (string)array_get($resp, 'data.user.qyUserId'),
                'name' => (string)array_get($resp, 'data.user.name'),
                'nickname' => (string)array_get($resp, 'data.user.nickName'),
                'mobile' => (string)array_get($resp, 'data.user.mobile'),
                'icon_url' => (string)array_get($resp, 'data.user.iconUrl'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
        else{ //判断为无效X-SessionId
            setcookie('X-SessionId', '', time() - 3600, config('session.path'), config('session.domain'), false, true);
            setcookie('X-jwt', '', time() - 3600, config('session.path'), config('session.domain'), false, true);
        }

        //如果权限为空，设置默认权限 
        if (empty($resp['data']['user']['accessCodes'])){
            $resp['data']['user']['accessCodes'] = ['basic', 'dp2', 'index', 'i_dpqs', 'i_lnhy', 'i_zlcb'];
        }
        $ret = [ 
            'code' => SYS_STATUS_OK,
            'data' => array_get($resp, 'data'),
        ];  
        
        // 匿名用户不缓存 0: 匿名用户， 1:正常登录用户
        $isNormalUser = array_get($resp, 'data.status', 0);
        if (1 == $isNormalUser) {
            Cache::put($cacheKey, $ret, 5);
        }

        return $ret;
    }

    public function getUserInfoByOpenId(string $openId, string $channel = 'default', bool $refresh = false)
    {
        $cacheKey = sprintf(self::CACHE_USERINFO_OPEN_ID, $channel, $openId);
        $ret = Cache::get($cacheKey);

        if (!empty($ret) && !$refresh) {
            return $ret;
        }

        $this->siteKey = config("uc.guard.$channel.siteKey");
        $this->siteSecret = config("uc.guard.$channel.siteSecret");
        $uri = sprintf('%s%s', self::URI_BASE, sprintf(self::URI_USER_INFO_OPEN_ID, $openId));
        $nonce = str_random(self::LENGTH_NONCE);
        $data = [
            'nonce' => $nonce,
            'fields' => 'money',
        ];
        $headers = [
            'uc-app-key' => $this->siteKey,
            'uc-access' => hash_hmac($this->algo, $nonce.$this->siteSecret, $this->siteKey),
        ];
        $resp = $this->getJson($uri, $data, $headers);
        //$this->checkUcResponse($resp);
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => array_get($resp, 'data'),
        ];

        Cache::put($cacheKey, $ret, 5);

        return $ret;
    }

    public function getUserInfoByMobile(string $mobile, string $channel = 'default', bool $refresh = false)
    {
        $cacheKey = sprintf(self::CACHE_USERINFO_MOBILE, $channel, $mobile);
        $ret = Cache::get($cacheKey);

        if (!empty($ret) && !$refresh) {
            return $ret;
        }

        $this->siteKey = config("uc.guard.$channel.siteKey");
        $this->siteSecret = config("uc.guard.$channel.siteSecret");
        $uri = sprintf('%s%s', self::URI_BASE, sprintf(self::URI_USER_INFO_MOBILE, $mobile));
        $nonce = str_random(self::LENGTH_NONCE);
        $data = [
            'nonce' => $nonce,
            'fields' => 'money',
        ];
        $headers = [
            'uc-app-key' => $this->siteKey,
            'uc-access' => hash_hmac($this->algo, $nonce.$this->siteSecret, $this->siteKey),
        ];
        $resp = $this->getJson($uri, $data, $headers);
        $this->checkUcResponse($resp);
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => array_get($resp, 'data'),
        ];

        Cache::put($cacheKey, $ret, 5);

        return $ret;
    }

    public function getUserInfoByQyUserid(string $qyUserid, string $channel = 'default', bool $refresh = false)
    {
        $cacheKey = sprintf(self::CACHE_USERINFO_QYUSERID, $channel, $qyUserid);
        $ret = Cache::get($cacheKey);

        if (!empty($ret) && !$refresh) {
            return $ret;
        }

        $this->siteKey = config("uc.guard.$channel.siteKey");
        $this->siteSecret = config("uc.guard.$channel.siteSecret");
        $uri = sprintf('%s%s', self::URI_BASE, sprintf(self::URI_USER_INFO_QYUSERID, $qyUserid));
        $nonce = str_random(self::LENGTH_NONCE);
        $data = [
            'nonce' => $nonce,
        ];
        $headers = [
            'uc-app-key' => $this->siteKey,
            'uc-access' => hash_hmac($this->algo, $nonce.$this->siteSecret, $this->siteKey),
        ];
        $resp = $this->getJson($uri, $data, $headers);
        $this->checkUcResponse($resp);
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => array_get($resp, 'data'),
        ];

        Cache::put($cacheKey, $ret, 5);

        return $ret;
    }

    public function getUserInfoByCustomerCode(string $customerCode, string $channel = 'default')
    {
        $this->siteKey = config("uc.guard.$channel.siteKey");
        $this->siteSecret = config("uc.guard.$channel.siteSecret");
        $uri = sprintf('%s%s', self::URI_BASE, sprintf(self::URI_USER_INFO_CUSTOMER_CODE, $customerCode));
        $nonce = str_random(self::LENGTH_NONCE);
        $data = [
            'nonce' => $nonce,
        ];
        $headers = [
            'uc-app-key' => $this->siteKey,
            'uc-access' => hash_hmac($this->algo, $nonce.$this->siteSecret, $this->siteKey),
        ];
        $resp = $this->getJson($uri, $data, $headers);

        $this->checkUcResponse($resp);
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => array_get($resp, 'data'),
        ];

        return $ret;
    }

    public function fitAppWebviewUrl(string $url)
    {
        return sprintf('%s%s%s', config('uc.url'), self::URI_BASE, sprintf(self::URI_APP_WEBVIEW_URL, urlencode($url)));
    }


    public function getAccessCodeByOpenId(string $openId, string $channel = 'default', bool $refresh = false)
    {
        if (empty($openId)) {
            $accessCodeList = ['basic', 'dp2', 'index', 'i_dpqs', 'i_lnhy', 'i_zlcb'];
            return $accessCodeList;
        }

        $cacheKey = sprintf(self::CACHE_ACCESSCODE_OPENID, $channel, $openId);

        if (empty($openId)) {
            return [];
        }
        $accessCodeList = Cache::get($cacheKey);

        if (!empty($accessCodeList) && !$refresh) {
            return $accessCodeList;
        }

        $this->siteKey = config("uc.guard.$channel.siteKey");
        $this->siteSecret = config("uc.guard.$channel.siteSecret");
        $uri = sprintf('%s%s', self::URI_BASE, sprintf(self::URI_USER_ACCESS_OPENID, $openId));
        $nonce = str_random(self::LENGTH_NONCE);
        $data = [
            'nonce' => $nonce,
        ];
        $headers = [
            'uc-app-key' => $this->siteKey,
            'uc-access' => hash_hmac($this->algo, $nonce.$this->siteSecret, $this->siteKey),
        ];
        $resp = $this->getJson($uri, $data, $headers);

        $this->checkUcResponse($resp);

        $accessCodeList = array_column(array_get($resp, 'data'), 'accessKey');

        Cache::put($cacheKey, $accessCodeList, 5);

        return $accessCodeList;
    }

    public function getAccessCodeBySessionId(string $sessionId, string $channel = 'default', bool $refresh = false)
    {
        $cacheKey = sprintf(self::CACHE_ACCESSCODE_SESSIONID, $channel, $sessionId);

        if (empty($sessionId)) {
            return [];
        }
        $accessCodeList = Cache::get($cacheKey);

        if (!empty($accessCodeList) && !$refresh) {
            return $accessCodeList;
        }

        $this->siteKey = config("uc.guard.$channel.siteKey");
        $this->siteSecret = config("uc.guard.$channel.siteSecret");
        $uri = sprintf('%s%s', self::URI_BASE, sprintf(self::URI_USER_ACCESS_SESSIONID, $sessionId));
        $nonce = str_random(self::LENGTH_NONCE);
        $data = [
            'nonce' => $nonce,
        ];
        $headers = [
            'uc-app-key' => $this->siteKey,
            'uc-access' => hash_hmac($this->algo, $nonce.$this->siteSecret, $this->siteKey),
        ];
        $resp = $this->getJson($uri, $data, $headers);

        $this->checkUcResponse($resp);

        $accessCodeList = array_get($resp, 'data');

        Cache::put($cacheKey, $accessCodeList, 5);

        return $accessCodeList;
    }

    public function batchFriend(array $friendList, string $channel = 'default')
    {
        $this->siteKey = config("uc.guard.$channel.siteKey");
        $this->siteSecret = config("uc.guard.$channel.siteSecret");
        $uri = sprintf('%s%s', self::URI_BASE, self::URI_FRIEND_ADD_BATCH);
        $nonce = str_random(self::LENGTH_NONCE);
        $data = [
            'items' => $friendList,
            'nonce' => $nonce,
        ];
        $headers = [
            'uc-app-key' => $this->siteKey,
            'uc-access' => hash_hmac($this->algo, $nonce.$this->siteSecret, $this->siteKey),
        ];
        $resp = $this->postUc($uri, $data, $headers);

        $this->checkUcResponse($resp);

        $resp = @json_decode(array_get($resp, 'data'), true);

        $respData = array_get($resp, 'data');

        return $respData;
    }

    public function batchRemoveFriend(array $friendList, string $channel = 'default')
    {
        $this->siteKey = config("uc.guard.$channel.siteKey");
        $this->siteSecret = config("uc.guard.$channel.siteSecret");
        $uri = sprintf('%s%s', self::URI_BASE, self::URI_FRIEND_REMOVE_BATCH);
        $nonce = str_random(self::LENGTH_NONCE);
        $data = [
            'items' => $friendList,
            'nonce' => $nonce,
        ];
        $headers = [
            'uc-app-key' => $this->siteKey,
            'uc-access' => hash_hmac($this->algo, $nonce.$this->siteSecret, $this->siteKey),
        ];
        $resp = $this->deleteUc($uri, $data, $headers);

        $this->checkUcResponse($resp);
    }

    public function forgetCache(string $sessionId, string $channel='default')
    {
        try{
            $userInfo = $this->getUserInfoBySessionId($sessionId);

            $openId = array_get($userInfo, 'data.user.openId');

            $jwt = array_get($userInfo, 'data.jwt');

            $mobile = array_get($userInfo, 'data.user.openId');

            //getUserDetailByJwt
            $cacheKey = sprintf(self::UC_USER_DETAIL_CACHE_FORMAT, $jwt);

            $cacheNameKey = sprintf(self::UC_USER_DETAIL_NAME_CACHE_FORMAT, $jwt);

            Cache::forget($cacheKey);

            Cache::forget($cacheNameKey);

            //getUserInfoBySessionId
            $cacheKeyBySessionId = sprintf(self::CACHE_USERINFO_SESSION_ID, $channel, $sessionId);

            Cache::forget($cacheKeyBySessionId);

            //getUserInfoByOpenId
            $cacheKeyByOpenId = sprintf(self::CACHE_USERINFO_OPEN_ID, $channel, $openId);

            Cache::forget($cacheKeyByOpenId);

            //getUserInfoByMobile
            $cacheKeyByMobile = sprintf(self::CACHE_USERINFO_MOBILE, $channel, $mobile);

            Cache::forget($cacheKeyByMobile);
        }catch(Exception $e){
            Log::error($e->getMessage, [$e]);

            $ret = [
                'code' => CMS_API_X_SESSIONID_EXPIRED,
                'msg' => 'x-session expired',
            ];

            return $ret;
        }
    }

    public function modifyNickname(string $nickName, string $channel = 'default')
    {
    //    $this->siteKey = config("uc.guard.$channel.siteKey");
    //    $this->siteSecret = config("uc.guard.$channel.siteSecret");

    //    $uri = sprintf('%s%s', self::URI_BASE, self::URI_USER_NICKNAME);
    //    $nonce = str_random(self::LENGTH_NONCE);
    //    $data = [
    //        'nickName' => $nickName,
    //        'nonce' => $nonce,
    //    ];
    //    $headers = [
    //        'uc-app-key' => $this->siteKey,
    //        'uc-access' => hash_hmac($this->algo, $nonce.$this->siteSecret, $this->siteKey),
    //    ];

    //    $resp = $this->postUc($uri, $data, $headers);

    //    return $resp;
    //    $this->checkUcResponse($resp);

    //    $resp = @json_decode(array_get($resp, 'data'), true);

    //    $respData = array_get($resp, 'data');

    //    return $respData;
    }

    public function getUserMoneys(array $openIds, string $channel = 'default')
    {
        $this->siteKey = config("uc.guard.$channel.siteKey");
        $this->siteSecret = config("uc.guard.$channel.siteSecret");
        $nonce = str_random(self::LENGTH_NONCE);
        $uri = sprintf('%s%s?nonce=%s', self::URI_BASE, self::URI_USER_MONEY_OPEN_ID, $nonce);
        $data = [
            'openIds' => $openIds,
        ];
        $headers = [
            'uc-app-key' => $this->siteKey,
            'uc-access' => hash_hmac($this->algo, $nonce.$this->siteSecret, $this->siteKey),
        ];
        $resp = $this->postUc($uri, $data, $headers);

        $this->checkUcResponse($resp);

        $resp = @json_decode(array_get($resp, 'data'), true);

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => array_get($resp, 'data'),
        ];

        return $ret;
    }

    public function syncUserInfo(array $userInfo)
    {
        $openId = '';
        if (!empty(array_get($userInfo, 'open_id'))) {
            $openId = array_get($userInfo, 'open_id');
        } else if (!empty(array_get($userInfo, 'qy_user_id'))) {
            $openId = array_get($userInfo, 'qy_user_id');
        } else {
            throw new UcException("同步数据格式不正确", USER_INFO_PARAM_NOT_FOUND);
        }

        $nonce = str_random(self::LENGTH_NONCE);
        $uri = sprintf('%s%s/%s?nonce=%s', self::URI_BASE, self::URI_SYNC_USER_INFO, $openId, $nonce);
        $iconUrl = empty(array_get($userInfo, 'icon_url')) ? '' : array_get($userInfo, 'icon_url');
        $iconUrl = self::fitDetailUrl($iconUrl);
        $data = [
            'bio' => array_get($userInfo, 'description'),
            'iconUrl' => $iconUrl,
            'quaCertNo' => array_get($userInfo, 'cert_no'),
            'realName' => array_get($userInfo, 'name'),
        ];
        $headers = [
            'uc-app-key' => $this->siteKey,
            'uc-access' => hash_hmac($this->algo, $nonce.$this->siteSecret, $this->siteKey),
        ];
        $resp = $this->postUc($uri, $data, $headers);

        $this->checkUcResponse($resp);
        $resp = @json_decode(array_get($resp, 'data'), true);
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => array_get($resp, 'data'),
            'msg' => array_get($resp, 'msg'),
        ];

        return $ret;
    }

    private function fitDetailUrl(string $url)
    {
        if (empty($url)) {
            return '';
        }
        if (strpos($url, 'http') === 0) {
            return $url;
        } elseif (strpos($url, '//') === 0) {
            return config('app.cdn.base_protocol').":$url";
        } elseif (stripos($url, '/files/') === 0) {
            return self::fitDetailUrl(substr_replace($url, config('app.cdn.base_url'), 0, 6));
        } else {
            return $url;
        }
    }

    public function sendMessageToUc(array $formData, string $channel = 'default')
    {
        Log::info('send message to uc params: ', [$formData]);
        $this->siteKey = config("uc.guard.$channel.siteKey");
        $this->siteSecret = config("uc.guard.$channel.siteSecret");

        $nonce = str_random(self::LENGTH_NONCE);
        $uri = sprintf("%s?nonce=%s", self::URI_MESSAGE_SEND, $nonce);
        
        $data = [
            'appCode' => (int)array_get($formData, 'appCode'),
            'boxCode' => array_get($formData, 'boxCode'),
            'boxIconUrl' => array_get($formData, 'boxIconUrl'),
            'boxTitle' => array_get($formData, 'boxTitle'),
            'title' => array_get($formData, 'title'),
            'opTitle' => array_get($formData, 'opTitle'),
            'content' => array_get($formData, 'content'),
            'msgKind' => array_get($formData, 'msgKind'),
            'toAll' => (int)array_get($formData, 'toAll'),
            'traceId' => array_get($formData, 'traceId'),
            'traceType' => array_get($formData, 'traceType'),
            'userIds' => array_get($formData, 'userIds'),
            'sender' => array_get($formData, 'sender'),
            'senderUserId' => array_get($formData, 'senderUserId'),
         ];

        $headers = [
            'uc-app-key' => $this->siteKey,
            'uc-access' => hash_hmac($this->algo, $nonce.$this->siteSecret, $this->siteKey),
        ];
        $resp = $this->postUc($uri, $data, $headers);

        $this->checkUcResponse($resp);
        $resp = @json_decode(array_get($resp, 'data'), true);
        

        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => array_get($resp, 'data'),
        ];

        return $ret;
    }
}