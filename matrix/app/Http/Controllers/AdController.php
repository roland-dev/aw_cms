<?php

namespace Matrix\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Matrix\Contracts\AdManager;
use Matrix\Contracts\BossManager;
use Matrix\Contracts\ContentGuardContract;
use Matrix\Contracts\ImageManager;
use Matrix\Contracts\LogManager;
use Matrix\Contracts\UcManager;
use Matrix\Exceptions\MatrixException;
use Log;
use Exception;

class AdController extends Controller
{
    const SOURCETYPE = 'ad';
    const ADD = 'add';
    const UPDATE = 'update';
    const DELETE = 'delete';

    const SUCCESS = 'success';
    const URI_AD_ACCESS = '/api/v2/propaganda/ad/{adId}';

    const APP_BANNER = "app_banner";
    const PC_BANNER = "pc_banner";

    private $propagandaPageckage;

    private $adManager;
    private $imageManager;
    private $logManager;
    private $contentGuardContract;
    private $request;
    private $bossManager;
    

    public function __construct(Request $request, AdManager $adManager, LogManager $logManager, ImageManager $imageManager, ContentGuardContract $contentGuardContract, BossManager $bossManager)
    {
        $this->adManager = $adManager;
        $this->request = $request;
        $this->logManager = $logManager;
        $this->imageManager = $imageManager;
        $this->contentGuardContract = $contentGuardContract;
        $this->bossManager = $bossManager;
        $this->propagandaPageckage = config('packagetype.propaganda_pageckage');
    }

    public function create()
    {
        $originalData = '';
        $reqData = $this->request->validate([
            'media_code' => 'required|string|max:32',
            'operation_code' => 'required|string|max:32',
            'operation_id' => 'nullable|int',
            'location_code' => 'required|string|max:32',
            'terminal_codes' => 'required|array',
            'img_src' => 'required|string|max:300',
            'title' => 'required|string|max:255',
            'url_link' => 'nullable|url|max:300',
            'jump_type' => 'nullable|string|max:32',
            'jump_params' => 'nullable|string',
            'show_time' => 'required|array',
            'sort_num' => 'nullable|int',
            'disabled' => 'required|int',
            'permission_codes' => 'required|array',
            'need_popup' => 'nullable|int',
            'popup_poster_url' => 'nullable|max:300'
        ]);

        $adData = [
            "location_code" => array_get($reqData, 'location_code'),
            "media_code" => array_get($reqData, 'media_code'),
            "operation_code" => array_get($reqData, 'operation_code'),
            "operation_id" => array_get($reqData, 'operation_id'),
            "title" => array_get($reqData, 'title'),
            "img_src" => array_get($reqData, 'img_src'),
            "url_link" => (string)array_get($reqData, 'url_link'),
            "jump_type" => array_get($reqData, 'jump_type'),
            "jump_params" => array_get($reqData, 'jump_params'),
            "start_at" => array_get($reqData, 'show_time')[0],
            "end_at" => array_get($reqData, 'show_time')[1],
            "sort_num" => array_get($reqData, 'sort_num', 0),
            "disabled" => array_get($reqData, 'disabled'),
            "updated_user_id" => Auth::id(),
            "creator_id" => Auth::id(),
            "need_popup" => array_get($reqData, 'need_popup'),
            "popup_poster_url" => array_get($reqData, 'popup_poster_url', '')
        ];
        $terminalCodes = array_get($reqData, 'terminal_codes');
        $permissionCodes = array_get($reqData, 'permission_codes');

        $this->logManager->createOperationLog(self::SOURCETYPE, Auth::id(), $originalData, self::ADD);
        $addAdData = $this->adManager->createAd($adData, $terminalCodes);
        $this->checkServiceResult($addAdData, 'AdService');

        foreach (self::packageContentGuards($permissionCodes, self::URI_AD_ACCESS,[array_get($addAdData, 'data.id')]) as $item) {
            $addContentGuardData = $this->contentGuardContract->grant($item);
            if (array_get($addContentGuardData, 'code') === SYS_STATUS_ERROR_UNKNOW) {
                $addContentGuardData['msg'] = "权限更新失败";
                return $addContentGuardData;
            }
        }

        $ret = [
            'code' => SYS_STATUS_OK,
            'msg' => "添加成功"
        ];

        return $ret;
    }

    public function update()
    {
        $reqData = $this->request->validate([
            'ad_id' => 'required|int',
            'media_code' => 'required|string|max:32',
            'operation_code' => 'required|string|max:32',
            'location_code' => 'required|string|max:32',
            'terminal_codes' => 'required|array',
            'img_src' => 'required|string|max:300',
            'title' => 'required|string|max:255',
            'url_link' => 'nullable|url|max:300',
            'jump_type' => 'nullable|string',
            'jump_params' => 'nullable|string',
            'show_time' => 'required|array',
            'sort_num' => 'nullable|int',
            'disabled' => 'required|int',
            'permission_codes' => 'required|array',
            'need_popup' => 'nullable|int',
            'popup_poster_url' => 'nullable|max:300'
        ]);

        $adData = [
            "location_code" => array_get($reqData, 'location_code'),
            "media_code" => array_get($reqData, 'media_code'),
            "operation_code" => array_get($reqData, 'operation_code'),
            "title" => array_get($reqData, 'title'),
            "img_src" => array_get($reqData, 'img_src'),
            "url_link" => (string)array_get($reqData, 'url_link'),
            "jump_type" => array_get($reqData, 'jump_type'),
            "jump_params" => array_get($reqData, 'jump_params'),
            "start_at" => array_get($reqData, 'show_time')[0],
            "end_at" => array_get($reqData, 'show_time')[1],
            "sort_num" => array_get($reqData, 'sort_num', 0),
            "disabled" => array_get($reqData, 'disabled'),
            "updated_user_id" => Auth::id(),
            "need_popup" => array_get($reqData, 'need_popup'),
            "popup_poster_url" => array_get($reqData, 'popup_poster_url', '')
        ];
        $adId = array_get($reqData, 'ad_id');
        $terminalCodes = array_get($reqData, 'terminal_codes');
        $permissionCodes = array_get($reqData, 'permission_codes');

        $repDate = $this->adManager->detail($adId);
        $originalData = json_encode(array_get($repDate, 'data'));
        $this->logManager->createOperationLog(self::SOURCETYPE, Auth::id(), $originalData, self::UPDATE);

        $updateAdData = $this->adManager->updateAd($adId, $adData, $terminalCodes);
        $this->checkServiceResult($updateAdData, 'AdService');

        $condition = [
            'uri' => self::URI_AD_ACCESS,
            'param1' => $adId
        ];

        $delContentGuardData = $this->contentGuardContract->revoke($condition);
        if (array_get($delContentGuardData, 'code') === SYS_STATUS_ERROR_UNKNOW) {
            $delContentGuardData['msg'] = "权限更新失败";
            return $delContentGuardData;
        }

        foreach (self::packageContentGuards($permissionCodes, self::URI_AD_ACCESS, [(int)$adId]) as $item) {
            $addContentGuardData = $this->contentGuardContract->grant($item);
            if (array_get($addContentGuardData, 'code') === SYS_STATUS_ERROR_UNKNOW) {
                $addContentGuardData['msg'] = "权限更新失败";
                return $addContentGuardData;
            }
        }

        $ret = [
            'code' => SYS_STATUS_OK,
            'msg' => "更新成功"
        ];

        return $ret;
    }

    private function packageContentGuards(array $packages, string $uri, array $params)
    {
        $result = [];

        foreach ($packages as $package) {
            $data = [
                'service_code' => $package,
                'uri' => $uri,
            ];
            $i = 1;
            foreach ($params as $param) {
                $data['param' . $i] = $param;
                $i++;
            }
            array_push($result, $data);
        }
        return $result;
    }


    public function destory($adId)
    {

        $repDate = $this->adManager->detail($adId);
        $originalData = json_encode(array_get($repDate, 'data'));
        $this->logManager->createOperationLog(self::SOURCETYPE, Auth::id(), $originalData, self::DELETE);

        $delAdData = $this->adManager->destoryAd($adId);
        $this->checkServiceResult($delAdData, 'AdService');

        $condition = [
            'uri' => self::URI_AD_ACCESS,
            'param1' => $adId
        ];

        $delContentGuardData = $this->contentGuardContract->revoke($condition);
        if (array_get($delContentGuardData, 'code') === SYS_STATUS_ERROR_UNKNOW) {
            $delContentGuardData['msg'] = "权限更新失败";
            return $delContentGuardData;
        }


        $ret = [
            'code' => SYS_STATUS_OK,
            'msg' => "删除成功"
        ];

        return $ret;
    }

    public function search()
    {
        $reqData = $this->request->validate([
            'page_no' => 'nullable|integer',
            'page_size' => 'nullable|integer',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date',
            'location_code' => 'nullable|string|max:32',
            'terminal_code' => 'nullable|string|max:32',
            'operation_code' => 'nullable|string|max:32'
        ]);

        try {
            $pageNo = array_get($reqData, 'page_no', 1);
            $pageSize = array_get($reqData, 'page_size', 10);

            $cond = [
                'start_at' => (string)array_get($reqData, 'start_time'),
                'end_at' => (string)array_get($reqData, 'end_time'),
                'location_code' => (string)array_get($reqData, 'location_code'),
                'terminal_code' => (string)array_get($reqData, 'terminal_code'),
                'operation_code' => (string)array_get($reqData, 'operation_code')
            ];

            $adList = $this->adManager->getAdList($pageNo, $pageSize, $cond);
            $adCnt = $this->adManager->getAdCnt($cond);

            $ret = [
                'code' => SYS_STATUS_OK,
                'msg' => 'success',
                'data' => [
                    'ad_list' => $adList,
                    'ad_cnt' => $adCnt,
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

    public function detail($adId, BossManager $bossManager)
    {
        $adDetail = $this->adManager->detail($adId);
        $this->checkServiceResult($adDetail, 'AdService');

        // 单个广告 permission_codes
        $permissionCodes = array_get($adDetail, 'data.permission_codes');
        
        // 可见人群 tree boss
        $packageData = $this->bossManager->getPackages();
        $this->checkServiceResult($packageData, 'BossService');
        $packageTree = $this->formatDataOfPackages(array_get($packageData, 'data'));

        $permissionArray = $this->getPermissionCodesType($permissionCodes, $packageTree);
        $adDetail['data']['permission_array'] = $permissionArray;

        return $adDetail;
    }

    private function formatDataOfPackages(array $arr)
    {
        $resultData = [];
        $packageList = $this->sortArr($arr, 'code', SORT_ASC, SORT_STRING);
        $otherPackageList = []; // 其他类型 例如： 免费版以及一些没有product_id的套餐
        foreach ($packageList as $packageItem) {
            $packageItem['granted'] = false;
            // 判断是否具有product_id
            if (empty(array_get($packageItem, 'product_id'))) {
                array_push($otherPackageList, $packageItem);
                continue;
            }
            // 判断当前package的product是否存在
            if (!in_array(array_get($packageItem, 'product_id'), array_column($resultData, 'product_id'))) {
                $productData = [];
                $productData['name'] = array_get($packageItem, 'product_name');
                $productData['product_id'] = array_get($packageItem, 'product_id');
                $productData['granted'] = false;
                $productData['child'] = [];
                foreach ($this->propagandaPageckage as $item) {
                    array_push($productData['child'], []);
                }
                array_push($resultData, $productData);
            }
            $productIndex = 0; // 当前套餐所属product索引
            foreach ($resultData as $k => $productItem) {
                if (array_get($productItem, 'product_id') === array_get($packageItem, 'product_id')) {
                    $productIndex = $k;
                }
            }
            
            foreach ($this->propagandaPageckage as $i => $orderName) {
                if (substr(strrchr(array_get($packageItem, 'code'), '_'), 1) === $orderName) {
                    $productChild = array_get($resultData[$productIndex], 'child');
                    if (empty($productChild[$i])) {
                        $productChild[$i] = [];
                    }
                    array_push($productChild[$i], $packageItem);
                    $resultData[$productIndex]['child'] = $productChild;
                }
            }
        }

        foreach ($resultData as $resultDataIndex => $resultDataItem) {
            foreach ($resultDataItem['child'] as $childIndex => $childItem) {
                if (sizeof($childItem) === 0) {
                    array_splice($resultData[$resultDataIndex]['child'], $childIndex, 1);
                }
            }
        }

        if ( !empty($otherPackageList) ) {
            $otherPackage = [
                'name' => '其他',
                'child' => [
                    $otherPackageList
                ],
                'granted' => false
            ];
            array_push($resultData, $otherPackage);
        } 

        return $resultData;
    }

    private function sortArr($arrays, $sort_key, $sort_order=SORT_ASC, $sort_type=SORT_NUMERIC)
    {
        if(is_array($arrays)){
            foreach ($arrays as $array){
                if(is_array($array)){
                    $key_arrays[] = $array[$sort_key];
                }else{
                    return false;
                }
            }
        }else{
            return false;
        }
        array_multisort( $key_arrays, $sort_order, $sort_type, $arrays);
        return $arrays;
    }

    private function getPermissionCodesType(array $permissionCodes, array $packageTree)
    {
        if (sizeof($permissionCodes) < 1) {
            return $packageTree;
        }
        $resultData = $packageTree;
        $k = 0;
        foreach ($packageTree as $item) {
            if (array_get($item, 'child')) {
                $j = 0;
                foreach ($item['child'] as $childItem) {
                    $i = 0;
                    foreach ($childItem as $packageCode) {
                        if (in_array($packageCode['code'], $permissionCodes)) {
                            $packageCode['granted'] = true;
                            $resultData[$k]['child'][$j][$i] = $packageCode;
                        }
                        $i ++;
                    }
                    $j ++;
                }
                $k ++;
            }
        }
        return $resultData;
    }

    public function getPackages()
    {
        $ret = [
            'code' => '',
            'data' => ''
        ];
        $ret['code'] = SYS_STATUS_OK;
        $packageData = array_get($this->bossManager->getPackages(), 'data');
        $ret['data'] = $this->formatDataOfPackages($packageData);
        return $ret;
    }

    public function getAdLocations()
    {
        return $this->adManager->getAdLocations();
    }

    public function getAdTerminals()
    {
        return $this->adManager->getTerminals();
    }

    public function getMediaTypes()
    {
        return $this->adManager->getMediaTypes();
    }

    public function getOperationTypes()
    {
        return $this->adManager->getOperationTypes();
    }


    public function uploadImage()
    {
        $reqData = $this->request->validate([
            'image' => 'required|image|mimes:jpeg,png,bmp,jpg,gif,svg',
        ]);

        return $this->imageManager->upload($this->request->file('image'),'propaganda');
    }

    public function getAdTerminalsOfLocationCode($locationCode)
    {
        $ret = [];
        try {
            $termianls = $this->adManager->getTerminalsOfLocationCode($locationCode);
            
            $ret = [
                'code' => SYS_STATUS_OK,
                'data' => $termianls,
                'msg' => ''
            ];
        } catch (MatrixException $e) {
            Log::error("获取展示终端列表错误：{$e->getMessage()}", [$e]);
            $ret = [
                'code' => $e->getCode(),
                'msg' => $e->getMessage()
            ];
        } catch (Exception $e) {
            Log::error("获取展示终端列表错误：{$e->getMessage()}", [$e]);
            $ret = [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => '未知错误'
            ];
        }

        return $ret;
    }
}
