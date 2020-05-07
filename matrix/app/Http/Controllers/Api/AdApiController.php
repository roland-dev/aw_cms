<?php

namespace Matrix\Http\Controllers\Api;

use Jenssegers\Agent\Agent;
use Matrix\Contracts\UcManager;
use Matrix\Contracts\ContentGuardContract;
use Matrix\Contracts\AdManager;
use Illuminate\Http\Request;
use Matrix\Exceptions\MatrixException;
use Exception;
use Log;

class AdApiController extends Controller
{   
    const SUCCESS = 'success';

    const BANNER = "banner";

    const IOS_TERMINAL = "ios";
    const ANDROID_TERMINAL = "android";
    const PC_TERMINAL = "pc";

    const LOCATIONCODEOFREQUESTPARAM = [
        [
            'locationCode' => 'banner',
            'requestParam' => [
                'app_new_banner'
            ]
        ],
        [
            'locationCode' => 'live_banner',
            'requestParam' => [
                'app_live_banner'
            ]
        ]
    ];

    private $request;
    private $adManager;
    private $agent;
    public function __construct(Request $request, AdManager $adManager, Agent $agent)
    {
        $this->request = $request;
        $this->adManager = $adManager;
        $this->agent = $agent;
    }

    /**
     * 广告v1接口调用
     */
    public function getAdListOfCompatible(UcManager $ucManager, ContentGuardContract $contentGuardContract)
    {

        $ret = [
            "code" => '',
            "msg" => ''
        ];

        $locationCode = self::BANNER;

        if (strpos(strtolower($this->agent->getUserAgent()), 'iphone')) {
            $terminalCode = self::IOS_TERMINAL;
        } else if($this->agent->isAndroidOS()) {
            $terminalCode = self::ANDROID_TERMINAL;
        } else {
            $terminalCode = self::PC_TERMINAL;
        }

        $jwt = $this->request->cookie('x-jwt');
        $token = $this->request->cookie('token');

        $userDetail = [];

        try {
            if (!empty($jwt)) {
                $userDetail = $ucManager->getUserDetail($jwt);
            }
    
            if (empty($jwt) && !empty($token) ) {
                $userDetail = $ucManager->getUserDetail($token);
            }
            if (empty(array_get($userDetail,'data.openId')) ) {
                $ret['code'] = CMS_API_COOKIE_PARAMETER_INVALID;
                $ret['data'] = 'Expired cookie';
                return $this->respAdapter($ret);
            }
    
            $customerProductCode = $ucManager->getCustomerProductCodeList(array_get($userDetail,'data.openId'));
            $adIdList = $contentGuardContract->getOnesAdAccessIdList(array_get($customerProductCode,'data.product_key_list'));
    
            if (self::PC_TERMINAL == $terminalCode) {
                $pcVersion = self::getPcVersion($this->agent->getUserAgent());
                if (version_compare($pcVersion, '2.0.10') <= 0) {
                    $adsData = $this->adManager->getAdsData($locationCode, array_get($adIdList,'data.ad_access_id_list'), $terminalCode);
                } else {
                    $adsData = $this->adManager->getAdsData($locationCode, array_get($adIdList,'data.ad_access_id_list'), $terminalCode, 0);
                }
            } else {
                $adsData = $this->adManager->getAdsData($locationCode, array_get($adIdList,'data.ad_access_id_list'), $terminalCode);
            }
    
            if (SYS_STATUS_OK !== array_get($adsData,'code')) {
                return $adsData;
            }
    
            $ret['msg'] = self::SUCCESS;
            $ret['data'] = array_get($adsData,'data');
            $ret['code'] = SYS_STATUS_OK;
            $ret['errors'] = null;
        } catch (MatrixException $e) {
            Log::error("获取广告列表失败：{$e->getMessage()}", [$e]);
            $ret = [
                'code' => $e->getCode(),
                'msg' => $e->getMessage()
            ];
        } catch (Exception $e) {
            Log::error("获取广告列表失败：{$e->getMessage()}", [$e]);
            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => '未知错误'
            ];
        }

        return $ret;
    }

    /**
     * 广告v2接口调用 -- 单参数
     */
    public function getAdList(UcManager $ucManager, ContentGuardContract $contentGuardContract, $locationCode)
    {
        $ret = [
            "code" => '',
            "msg" => ''
        ];

        $sessionId = $this->request->header('X-SessionId');
        if (empty($sessionId)) {
            $sessionId = $this->request->cookie('X-SessionId');
        }


        if (strpos(strtolower($this->agent->getUserAgent()), 'iphone')) {
            $terminalCode = self::IOS_TERMINAL;
        } else if($this->agent->isAndroidOS()) {
            $terminalCode = self::ANDROID_TERMINAL;
        } else {
            $terminalCode = self::PC_TERMINAL;
        }

        $locationCode = $this->getLocationCodeOfRequestParam($locationCode);
        try {
            // X-SessionId 为空 的情况
            if (!empty($sessionId)) {
                $customerProductCode = $ucManager->getCustomerProductCodeListBySessionId($sessionId);
                $productKeyList = array_get($customerProductCode, 'data.product_key_list');
            } else {
                $productKeyList = ['basic_free_service'];
            }
            
            $adIdList = $contentGuardContract->getOnesAdAccessIdList($productKeyList);
            $adsData = $this->adManager->getAdsData($locationCode, array_get($adIdList,'data.ad_access_id_list'), $terminalCode);
            if (SYS_STATUS_OK !== array_get($adsData,'code')) {
                return $adsData;
            }
            
            $ret['msg'] = self::SUCCESS;
            $ret['data'] = array_get($adsData,'data');
            $ret['code'] = SYS_STATUS_OK;
            $ret['errors'] = null;
        } catch (MatrixException $e) {
            Log::error("获取广告列表失败：{$e->getMessage()}", [$e]);
            $ret = [
                'code' => $e->getCode(),
                'msg' => $e->getMessage()
            ];
        } catch (Exception $e) {
            Log::error("获取广告列表失败：{$e->getMessage()}", [$e]);
            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => '未知错误'
            ];
        }

        return $ret;
    }

    /**
     * 广告v2接口调用 -- 多参数
     */
    public function getAdListByLocationCodes(UcManager $ucManager, ContentGuardContract $contentGuardContract)
    {
        $sessionId = $this->request->header('X-SessionId');
        if (empty($sessionId)) {
            $sessionId = $this->request->cookie('X-SessionId');
        }


        if (empty($sessionId)) {
            abort(401);
        }

        if (strpos(strtolower($this->agent->getUserAgent()), 'iphone')) {
            $terminalCode = self::IOS_TERMINAL;
        } else if($this->agent->isAndroidOS()) {
            $terminalCode = self::ANDROID_TERMINAL;
        } else {
            $terminalCode = self::PC_TERMINAL;
        }

        $reqData = $this->request->validate([
            'locationcode' => 'required|string'
        ]);
        
        $locationCodeStr = array_get($reqData, 'locationcode');

        $locationCodes = explode(",", $locationCodeStr);

        foreach ($locationCodes as &$locationCode) {
            $locationCode = $this->getLocationCodeOfRequestParam($locationCode);
        }
        $ret = [
            "code" => '',
            "msg" => ''
        ];
        try {
            $customerProductCode = $ucManager->getCustomerProductCodeListBySessionId($sessionId);
            $adIdList = $contentGuardContract->getOnesAdAccessIdList(array_get($customerProductCode,'data.product_key_list'));
            $adsData = $this->adManager->getAdAccessIdDatasBylocationCodes($locationCodes, array_get($adIdList,'data.ad_access_id_list'), $terminalCode);
            if (SYS_STATUS_OK !== array_get($adsData,'code')) {
                return $adsData;
            }

            $ret['msg'] = self::SUCCESS;
            $ret['data'] = array_get($adsData,'data');
            $ret['code'] = SYS_STATUS_OK;
            $ret['errors'] = null;
        } catch (MatrixException $e) {
            Log::error("获取广告列表失败：{$e->getMessage()}", [$e]);
            $ret = [
                'code' => $e->getCode(),
                'msg' => $e->getMessage()
            ];
        } catch (Exception $e) {
            Log::error("获取广告列表失败：{$e->getMessage()}", [$e]);
            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => '未知错误'
            ];
        }
        

        return $ret;
    }

    /**
     * 兼容locationCode参数
     */
    private function getLocationCodeOfRequestParam(string $RequestParam)
    {
        $result = $RequestParam;
        foreach (self::LOCATIONCODEOFREQUESTPARAM as $item) {
            if ( in_array($RequestParam, array_get($item, 'requestParam')) ) {
                $result = array_get($item, 'locationCode');
                break;
            }
        }
        return $result;
    }

    /**
     * 获取 PC 版本号
     */
    private function getPcVersion(string $userAgent)
    {
        $result = "";

        $pattern = "/[\s\/]+/";
        $keywords = preg_split($pattern, $userAgent);
        $result = $keywords[1];

        return $result;
    }
}