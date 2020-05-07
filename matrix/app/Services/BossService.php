<?php
/**
 * Created by PhpStorm.  * User: Administrator
 * Date: 2018/5/22
 * Time: 17:13
 */

namespace Matrix\Services;


use Matrix\Contracts\BossManager;
use Matrix\Exceptions\BossException;
use Illuminate\Support\Facades\Cache;

class BossService extends HttpService implements BossManager
{
    const URI_BASE = '/api';
    const URI_SERVICES = '/service/list';
    const URI_PUSH_TO_QYWX = '/qy/sendMsg';
    const URL_PUSH_ARTICLE_TO_QYWX = '/qy/sendMsgByPlan';

    const URI_KGS_DELETE = '/kangaoshou/Delete';

    const BOSS_SYS_STATUS_OK = 1000;

    const MODULE_CATEGORY_KEY_VALUE = 'coursesystem.category_key_value.';


    const SERVICE_LIST_CACHE_KEY = 'service_list';
    const PACKAGE_LIST_CACHE_KEY = 'package_list';
    const PACKAGE_LIST_OF_SERVICE_CODE_CACHE_KEY = 'package_list_of_service_code';

    const SERVICE_CACHE_TIME = 60;
    const PACKAGE_CACHE_TIME = 60;
    const PACKAGE_OF_SERVICE_CODE_CACHE_TIME = 60;

    protected $bossUrl;
    protected $bossToken;

    public function __construct()
    {
        $this->bossUrl = config('boss.url');
        $this->bossToken = config('boss.api.token');

        parent::__construct($this->bossUrl);
    }

    public function checkBossResponse(array $resp)
    {
        $respCode = array_get($resp, 'code', SYS_STATUS_ERROR_UNKNOW);
        if ((int)$respCode !== self::BOSS_SYS_STATUS_OK) {
            throw new BossException("Response Code: $respCode .", CMS_INVOCATION_BOSS_STATUS_ERROR);
        }
    }

    public function getPackages()
    {
        $ret = [
            'code' => '',
            'data' => [],
            'msg' => ''
        ];

        $packageList = self::getPackageList(true);

        $ret['code'] = SYS_STATUS_OK;
        $ret['data'] = $packageList;

        return $ret;
    }

    private function getPackageList(bool $includeUserPackage = false)
    {
        $packageList = Cache::get(self::PACKAGE_LIST_CACHE_KEY);
        if (NULL === $packageList) {
            $resp = self::getPackageListOfBossApi();
            $customerPackageList = array_get($resp, 'package');
            if ($includeUserPackage) {
                $userPackageList = array_get($resp, 'staff');
                $packageList = array_merge($customerPackageList, $userPackageList);
            } else {
                $packageList = $customerPackageList;
            }

            if ( empty($packageList) ) {
                $packageList = [];
            }

            Cache::put(self::PACKAGE_LIST_CACHE_KEY, $packageList, self::PACKAGE_CACHE_TIME);
        } 
        return $packageList;
    }

    public function getPackagesOfServiceCode()
    {
        $packageListOfServiceCode = Cache::get(self::PACKAGE_LIST_OF_SERVICE_CODE_CACHE_KEY);
        if  (NULL === $packageListOfServiceCode) {
            $resp = self::getPackageListOfBossApi();
            $packageList = array_get($resp, 'package');
            $packageListOfServiceCode = [];
            foreach ($packageList as $package) {
                $packageCode = (string)array_get($package, 'code');
                $suffix = substr(strrchr($packageCode, '_'), 1);
                if (in_array($suffix, config('packagetype.propaganda_pageckage'))) {
                    $serviceCodeOfPackage = array_get($package, 'services');
                    foreach ($serviceCodeOfPackage as $service) {
                        if (array_key_exists((string)$service, $packageListOfServiceCode)) {
                            array_push($packageListOfServiceCode[$service], $packageCode);
                        } else {
                            $packageListOfServiceCode[$service] = [$packageCode];
                        }
                    }
                }
            }
            Cache::put(self::PACKAGE_LIST_OF_SERVICE_CODE_CACHE_KEY, $packageListOfServiceCode, self::PACKAGE_OF_SERVICE_CODE_CACHE_TIME);
        }

        return $packageListOfServiceCode;
    }

    private function getPackageListOfBossApi()
    {
        $uri = sprintf('%s%s', self::URI_BASE, self::URI_SERVICES);
        $data = [
            'token' => $this->bossToken
        ];
        $resp = $this->postJson($uri, $data);
        $this->checkBossResponse($resp);
        return $resp;
    }

    public function getServices(string $moduleName)
    {
        $ret = [
            'code' => '',
            'data' => [],
            'msg' => ''
        ];

        $serviceList = self::getServiceListOfBossApi();

        $ret['code'] = SYS_STATUS_OK;
        $ret['data'] = $serviceList;
        
        return $ret;
    }


    private function getServiceListOfBossApi()
    {
        $serviceList = Cache::get(self::SERVICE_LIST_CACHE_KEY);
        if (NULL === $serviceList) {
            $uri = sprintf('%s%s', self::URI_BASE, self::URI_SERVICES);
            $data = [
                'token' => $this->bossToken
            ];
            $resp = $this->postJson($uri, $data);
            $this->checkBossResponse($resp);
            $serviceList = array_get($resp, 'data');
            Cache::put(self::SERVICE_LIST_CACHE_KEY, $serviceList, 60);
        }
        return $serviceList;
    }

    public  function getCategoryList(string $moduleName)
    {
        $configKeyValue = self::MODULE_CATEGORY_KEY_VALUE.$moduleName;
        $moduleCategoryKeyValue = config($configKeyValue);
        $moduleCategoryKeyValue = json_decode($moduleCategoryKeyValue, true);
        $ret = [
            'code' => SYS_STATUS_OK,
            'data' => $moduleCategoryKeyValue,
        ];
        return $ret;
    }

    public function kgsMsgDelete(string $kgsId, string $uname)
    {
        $uri = sprintf('%s%s', self::URI_BASE, self::URI_KGS_DELETE);
        $data = [
            'token' => $this->bossToken,
            'uname' => $uname,
            'did' => $kgsId,
        ];
        $resp = $this->postJson($uri, $data);
        return $resp;
    }

    public function pushQywx(string $serviceCode, string $msgType, array $msgData)
    {
        $uri = sprintf('%s%s', self::URI_BASE, self::URI_PUSH_TO_QYWX);
        $data = [
            'token' => $this->bossToken,
            'service_code' => $serviceCode,
            'msg_type' => $msgType,
            'msg_data' => json_encode($msgData)
        ];
        $resp = $this->postJson($uri, $data);

        $this->checkBossResponse($resp);

        return $resp;
    }

    /**
    *推送文章信息至企业微信接口
    *@param $msgData array 推送消息信息数组
    *@return array
    */
    public function pushArticleToQywx(array $msgData)
    {
        $uri = sprintf('%s%s', self::URI_BASE, self::URL_PUSH_ARTICLE_TO_QYWX);
        $data = [
            'token' => $this->bossToken,
            'msg_data' => json_encode($msgData)
        ];
        $resp = $this->postJson($uri, $data);

        //$this->checkBossResponse($resp);

        return $resp;
    }
}
