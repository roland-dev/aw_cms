<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/4
 * Time: 15:35
 */

namespace Matrix\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Matrix\Contracts\BossManager;
use Matrix\Contracts\ContentGuardContract;
use Matrix\Contracts\ForumManager;
use Matrix\Contracts\ImageManager;
use Matrix\Contracts\LogManager;
use Matrix\Contracts\UcManager;
use Matrix\Exceptions\MatrixException;
use Exception;
use Log;

class ForumController extends Controller
{
    const SOURCETYPE = 'forum';
    const ADD = 'add';
    const UPDATE = 'update';
    const DELETE = 'delete';
    const URI_AD_ACCESS = '/api/v2/propaganda/ad/{adId}';
    const URI_FORUM_ACCESS = '/api/v2/propaganda/forum/{forumId}';

    private $forumManager;
    private $bossManager;
    private $logManager;
    private $contentGuardContract;
    private $request;

    private $propagandaPageckage;

    public function __construct(Request $request, LogManager $logManager, ForumManager $forumManager, BossManager $bossManager, ContentGuardContract $contentGuardContract)
    {
        $this->forumManager = $forumManager;
        $this->bossManager = $bossManager;
        $this->logManager = $logManager;
        $this->contentGuardContract = $contentGuardContract;
        $this->request = $request;
        $this->propagandaPageckage = config('packagetype.propaganda_pageckage');
    }

    public function create()
    {
        $originalData = '';
        $reqData = $this->request->validate([
            'theme' => 'required|string|max:255',
            'url_key' => 'required|string|max:50',
            'url_link' => 'required|string|max:500',
            'img_src' => 'required|string|max:255',
            'forum_at' => 'required|date',
            'visible_at' => 'required|date',
            'duration' => 'required|string|max:11',
            'teacher' => 'required|string|max:100',
            'abstract' => 'required|string|max:500',
            'permission_codes' => 'required|array'
        ]);
        
        $forumData = [
            "theme" => array_get($reqData, 'theme'),
            "img_src" => array_get($reqData, 'img_src'),
            "url_key" => array_get($reqData, 'url_key'),
            "url_link" => array_get($reqData, 'url_link'),
            "forum_at" => array_get($reqData, 'forum_at'),
            "visible_at" => array_get($reqData, 'visible_at'),
            "duration" => array_get($reqData, 'duration'),
            "teacher" => array_get($reqData, 'teacher'),
            "abstract" => array_get($reqData, 'abstract'),
            "creator_id" => Auth::id(),
            "updated_user_id" => Auth::id()
        ];
        $permissionCodes = array_get($reqData, 'permission_codes');

        $this->logManager->createOperationLog(self::SOURCETYPE, Auth::id(), $originalData, self::ADD);
        $addForumData = $this->forumManager->createForum($forumData);
        $this->checkServiceResult($addForumData, 'ForumService');

        foreach (self::packageContentGuards($permissionCodes, self::URI_FORUM_ACCESS, [array_get($addForumData, 'data.id')]) as $item) {
            $addContentGuardData = $this->contentGuardContract->grant($item);
            if (array_get($addContentGuardData, 'code') === SYS_STATUS_ERROR_UNKNOW) {
                $addContentGuardData['msg'] = "权限更新失败";
                return $addContentGuardData;
            }
        }
        $retData = [
            'code' => SYS_STATUS_OK,
            'msg' => "添加成功"
        ];

        return $retData;
    }

    public function update()
    {
        $reqData = $this->request->validate([
            'forum_id' => 'required|int',
            'theme' => 'required|string|max:255',
            'url_key' => 'required|string|max:50',
            'url_link' => 'required|string|max:500',
            'img_src' => 'required|string|max:255',
            'forum_at' => 'required|date',
            'visible_at' => 'required|date',
            'duration' => 'required|int',
            'teacher' => 'required|string|max:100',
            'abstract' => 'required|string|max:500',
            'permission_codes' => 'required|array'
        ]);

        $forumData = [
            "theme" => array_get($reqData, 'theme'),
            "img_src" => array_get($reqData, 'img_src'),
            "url_key" => array_get($reqData, 'url_key'),
            "url_link" => array_get($reqData, 'url_link'),
            "forum_at" => array_get($reqData, 'forum_at'),
            "visible_at" => array_get($reqData, 'visible_at'),
            "duration" => array_get($reqData, 'duration'),
            'teacher' => array_get($reqData, 'teacher'),
            "abstract" => array_get($reqData, 'abstract'),
            "creator_id" => Auth::id(),
            "updated_user_id" => Auth::id()
        ];

        $forumId = array_get($reqData, 'forum_id');
        $permissionCodes = array_get($reqData, 'permission_codes');

        $repDate = $this->forumManager->detail($forumId);
        $originalData = json_encode(array_get($repDate, 'data'));
        $this->logManager->createOperationLog(self::SOURCETYPE, Auth::id(), $originalData, self::UPDATE);

        $updateForumData = $this->forumManager->updateForum($forumId, $forumData);
        $this->checkServiceResult($updateForumData, 'ForumService');

        $condition = [
            'uri' => self::URI_FORUM_ACCESS,
            'param1' => $forumId
        ];

        $delContentGuardData = $this->contentGuardContract->revoke($condition);
        if (array_get($delContentGuardData, 'code') === SYS_STATUS_ERROR_UNKNOW) {
            $delContentGuardData['msg'] = "权限更新失败";
            return $delContentGuardData;
        }

        foreach (self::packageContentGuards($permissionCodes, self::URI_FORUM_ACCESS, [(int)$forumId]) as $item) {
            $addContentGuardData = $this->contentGuardContract->grant($item);
            if (array_get($addContentGuardData, 'code') === SYS_STATUS_ERROR_UNKNOW) {
                $addContentGuardData['msg'] = "权限更新失败";
                return $addContentGuardData;
            }
        }

        $retData = [
            'code' => SYS_STATUS_OK,
            'msg' => "更新成功"
        ];

        return $retData;
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

    public function destory($forumId)
    {
        $forumDate = $this->forumManager->detail($forumId);
        $originalData = json_encode(array_get($forumDate, 'data'));
        $this->logManager->createOperationLog(self::SOURCETYPE, Auth::id(), $originalData, self::DELETE);

        $adsDate = $this->forumManager->getAdListDataOfForumId($forumId);

        $originalData = json_encode(array_get($adsDate, 'data'));
        $this->logManager->createOperationLog(self::SOURCETYPE, Auth::id(), $originalData, self::DELETE);

        $delAdsData = $this->forumManager->destoryAdOfOtherModules($forumId);
        $this->checkServiceResult($delAdsData, 'ForumService');

        $ads = array_get($adsDate, 'data');


        if (!empty($ads)) {
            foreach ($ads as $ad) {
                $condition = [
                    'uri' => self::URI_AD_ACCESS,
                    'param1' => array($ad, 'ad_id')
                ];
                $delContentGuard = $this->contentGuardContract->revoke($condition);
                if (array_get($delContentGuard, 'code') === SYS_STATUS_ERROR_UNKNOW) {
                    $delContentGuard['msg'] = "权限更新失败";
                    return $delContentGuard;
                }
            }
        }

        $delForum = $this->forumManager->destoryForum($forumId);
        $this->checkServiceResult($delForum, 'ForumService');

        $conditionForumContentGuard = [
            'uri' => self::URI_FORUM_ACCESS,
            'param1' => $forumId
        ];

        $delContentGuardForum = $this->contentGuardContract->revoke($conditionForumContentGuard);
        if (array_get($delContentGuardForum, 'code') === SYS_STATUS_ERROR_UNKNOW) {
            $delContentGuardForum['msg'] = "权限更新失败";
            return $delContentGuardForum;
        }

        $retData = [
            'code' => SYS_STATUS_OK,
            'msg' => "删除成功"
        ];

        return $retData;
    }

    public function search()
    {
        $reqData = $this->request->validate([
            'page_no' => 'nullable|integer',
            'page_size' => 'nullable|integer',
            'theme' => 'nullable|string|max:255',
            'first_time' => 'nullable|date',
            'last_time' => 'nullable|date'
        ]);

        try {
            $pageNo = array_get($reqData, 'page_no', 1);
            $pageSize = array_get($reqData, 'page_size', 10);

            $cond = [
                'theme' => (string)array_get($reqData, 'theme'),
                'visible_at' => (string)array_get($reqData, 'first_time'),
                'forum_at' => (string)array_get($reqData, 'last_time')
            ];

            $forumList = $this->forumManager->getForumList($pageNo, $pageSize, $cond);
            $forumCnt = $this->forumManager->getForumCnt($cond);

            $ret = [
                'code' => SYS_STATUS_OK,
                'msg' => 'success',
                'data' => [
                    'forum_list' => $forumList,
                    'forum_cnt' => $forumCnt,
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

    public function detail($forumId)
    {
        $forumDetail = $this->forumManager->detail($forumId);
        $this->checkServiceResult($forumDetail, 'ForumService');

        $permissionCodes = array_get($forumDetail, 'data.permission_codes');
        
        $packageData = $this->bossManager->getPackages();
        $this->checkServiceResult($packageData, 'BossService');
        $packageTree = $this->formatDataOfPackages(array_get($packageData, 'data'));

        $permissionArray = $this->getPermissionCodesType($permissionCodes, $packageTree);

        $forumDetail['data']['permission_array'] = $permissionArray;
        return $forumDetail;
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

    private function getPermissionCodesType(array $permissionCodes, array $packagerTree)
    {
        if (sizeof($permissionCodes) < 1) {
            return $packagerTree;
        }
        $resultData = $packagerTree;
        $k = 0;
        foreach ($packagerTree as $item) {
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

    public function getAdListDataOfForumId($forumId)
    {
        return $this->forumManager->getAdListDataOfForumId($forumId, true);
    }

    public function getTeachers()
    {
        return $this->forumManager->getTeachers();
    }
}