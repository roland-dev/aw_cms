<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/25
 * Time: 14:34
 */

namespace Matrix\Services;


use Matrix\Contracts\AdManager;
use Matrix\Models\Ad;
use Matrix\Models\AdTerminal;
use Matrix\Models\AdLocation;
use Matrix\Models\AdOperationType;
use Matrix\Models\ContentGuard;
use Matrix\Models\Forum;
use Matrix\Models\Terminal;
use Matrix\Models\User;
use Illuminate\Support\Facades\DB;
use Matrix\Exceptions\MatrixException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Matrix\Models\AdLocationTerminal;

class AdService extends BaseService implements AdManager
{
    private $ad;
    private $adTerminal;
    private $user;
    private $adLocation;
    private $terminal;
    private $operationType;
    private $forum;
    private $contentGuard;

    const URI_AD_ACCESS = '/api/v2/propaganda/ad/{adId}';
    const CONTENT_GUARD_FOREIGN_KEY = 'param1';
    const CONTENT_GUARD_SERVICE_CODE = 'service_code';

    const IMAGE_BASE64_CACHE_FORMAT = 'image_base64_cache_%s';

    public function __construct(Ad $ad, AdTerminal $adTerminal, User $user, AdLocation $adLocation, Terminal $terminal, AdOperationType $operationType, Forum $forum, ContentGuard $contentGuard)
    {
        $this->ad = $ad;
        $this->adTerminal = $adTerminal;
        $this->user = $user;
        $this->adLocation = $adLocation;
        $this->terminal = $terminal;
        $this->operationType = $operationType;
        $this->forum = $forum;
        $this->contentGuard = $contentGuard;
    }

    public function createAd(array $adData, array $terminalCodes)
    {
        $adLocation = $this->adLocation->getAdLocation(array_get($adData, 'location_code'));
        if (empty($adLocation)) {
            return [
                'code' => AD_LOCATION_CODE_NOT_FOUND,
                'msg' => "请求参数 location_code 错误"
            ];
        }

        $adOperationType = $this->operationType->getOperationType(array_get($adData, 'operation_code'));
        if (empty($adOperationType)) {
            return [
                'code' => AD_OPERATION_CODE_NOF_FOUND,
                'msg' => "请求参数 operation_code 错误"
            ];
        }
        $retCheckMediaType = $this->ad->checkMediaType(array_get($adData, 'media_code'));
        if ( !$retCheckMediaType ) {
            return [
                'code' => AD_MEDIA_CODE_NOT_FOUND,
                'msg' => "请求参数 media_code 错误"
            ];
        }

        DB::beginTransaction();
        $ad = $this->ad->createAd($adData, $terminalCodes);
        if (empty($ad)) {
            DB::rollback();
            return [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => "服务器错误"
            ];
        }

        $adId = array_get($ad, 'id');
        foreach ($terminalCodes as $terminalCode) {
            $adTerminalData = [
                'ad_id' => $adId,
                'terminal_code' => $terminalCode
            ];
            $adTerminal = $this->adTerminal->createAdTerminal($adTerminalData);
            if (empty($adTerminal)) {
                DB::rollback();
                return [
                    'code' => SYS_STATUS_ERROR_UNKNOW,
                    'msg' => "服务器错误"
                ];
            }
        }
        DB::commit();
        return [
            'code' => SYS_STATUS_OK,
            'data' => $ad,
            'msg' => "添加成功"
        ];
    }

    public function updateAd(int $adId, array $adData, array $terminalCodes)
    {
        $adLocation = $this->adLocation->getAdLocation(array_get($adData, 'location_code'));
        if (empty($adLocation)) {
            return [
                'code' => AD_LOCATION_CODE_NOT_FOUND,
                'msg' => "请求参数 location_code 错误"
            ];
        }

        $adOperationType = $this->operationType->getOperationType(array_get($adData, 'operation_code'));
        if (empty($adOperationType)) {
            return [
                'code' => AD_OPERATION_CODE_NOT_FOUND,
                'msg' => "请求参数 operation_code 错误"
            ];
        }

        $retCheckMediaType = $this->ad->checkMediaType(array_get($adData, 'media_code'));

        if ( !$retCheckMediaType ) {
            return [
                'code' => AD_MEDIA_CODE_NOT_FOUND,
                'msg' => "请求参数 media_code 错误"
            ];
        }


        $ad = $this->ad->updateAd($adId, $adData);
        if (empty($ad)) {
            return [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => "服务器错误"
            ];
        }

        $result = $this->adTerminal->delAdTerminal($adId);
        if (!$result ) {
            return [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => "服务器错误"
            ];
        }
        foreach ($terminalCodes as $terminalCode) {
            $adTerminalData = [
                'ad_id' => $adId,
                'terminal_code' => $terminalCode
            ];
            $adTerminal = $this->adTerminal->createAdTerminal($adTerminalData);
            if (empty($adTerminal)) {
                return [
                    'code' => SYS_STATUS_ERROR_UNKNOW,
                    'msg' => "服务器错误"
                ];
            }
        }

        return [
            'code' => SYS_STATUS_OK,
            'msg' => "更新成功"
        ];
    }

    public function detail(int $adId)
    {
        $ad = $this->ad->detail($adId);
        if (empty($ad)) {
            return [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => "查询失败，服务器错误"
            ];
        }

        $ad['terminal_codes'] = $this->adTerminal->getAdTerminals($adId);

        return [
            'code' => SYS_STATUS_OK,
            'data' => $ad,
            'msg' => ''
        ];
    }

    public function destoryAd(int $adId)
    {
        $result = $this->ad->destoryAd($adId);
        if ( !$result ) {
            return [
                'code' => SYS_STATUS_ERROR_UNKNOW,
                'msg' => "服务器错误"
            ];
        }

        return [
            'code' => SYS_STATUS_OK,
            'msg' => "删除成功"
        ];
    }

    public function getAdLocations()
    {
        return $this->adLocation->getAdLocations();
    }

    public function getTerminals()
    {
        return $this->terminal->getTerminals();
    }

    public function getTerminalsOfLocationCode(string $locationCode)
    {
        try {
            AdLocation::where('code', $locationCode)->where('disabled', 0)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new MatrixException("请求广告位Code不存在", AD_LOCATION_CODE_NOT_FOUND);
        }

        $terminalCodes = AdLocationTerminal::where('location_code', $locationCode)->pluck('terminal_code')->toArray();
        $terminals = Terminal::whereIn('code', $terminalCodes)->get()->toArray();

        return $terminals;
    }

    public function getMediaTypes()
    {
        return $this->ad->getMediaTypes();
    }

    public function getOperationTypes()
    {
        return $this->operationType->getOperationTypes();
    }

    public function getAdsData(string $locationCode, array $adIdsOfPermission, string $terminalCode, int $isDefaultAd = 1)
    {
        $adLocation = $this->adLocation->getAdLocation($locationCode);

        $adIdsOfTerminalCode = $this->adTerminal->getAdIdsOfTerminalCode($terminalCode);
        $adIds = array_intersect($adIdsOfPermission, $adIdsOfTerminalCode);
        $adsData = $this->ad->getAdsData($adLocation, $adIds);
        if (array_get($adsData, 'code') === SYS_STATUS_OK) {
            $ads = array_get($adsData, 'data');
        } else {
            return $adsData;
        }

        if (sizeof($ads) > 0) {
            return $adsData;
        } else {
            if ( 0 == $isDefaultAd) { // 是否吐出默认广告
                return $adsData;
            } else {
                $defaultAdData = $this->ad->getDefaultAdDataByLocationCode($adLocation);
                return $defaultAdData;
            }
        }
    }

    public function getAdAccessIdDatasBylocationCodes(array $locationCodes, array $adIdsOfPermission, string $terminalCode, int $isDefaultAd = 1)
    {
        $adIdsOfTerminalCode = $this->adTerminal->getAdIdsOfTerminalCode($terminalCode);
        $adIds = array_intersect($adIdsOfPermission, $adIdsOfTerminalCode);

        $adLocations = [];
        foreach ($locationCodes as $locationCode) {
            $adLocation = $this->adLocation->getAdLocation($locationCode);
            array_push($adLocations, $adLocation);
        }

        $adsData = $this->ad->getAdAccessIdDatasBylocationCodes($adLocations, $adIds);
        if (array_get($adsData, 'code') === SYS_STATUS_OK) {
            $ads = array_get($adsData, 'data');
        } else {
            return $adsData;
        }
        

        foreach ($ads as $i => $item) {
            if (sizeof($item) === 0 && 1 == $isDefaultAd) {
                $item = $this->ad->getDefaultAdDataByLocationCode($adLocations[$i]);
                $ads[$i] = array_get($item, 'data');
            }
        }
        $adsData['data'] = $ads;

        return $adsData;
    }

    public function getAdList(int $pageNo, int $pageSize, array $credentials)
    {
        $cond = [];
        $ad = Ad::select('id', 'title', 'operation_code', 'location_code', 'media_code', 'start_at', 'end_at', 'disabled', 'sort_num', 'updated_user_id', 'updated_at');

        foreach ($credentials as $k => $v) {
            if (in_array($k, ['location_code', 'operation_code']) && $v !== "" && $v !== null) {
                $cond[] = [$k, '=', $v];
            }
        }

        $startAt = array_get($credentials, 'start_at');
        if (!empty($startAt)) {
            $cond[] = ['start_at', '>=', $startAt];
        }

        $endAt = array_get($credentials, 'end_at');
        if (!empty($endAt)) {
            $cond[] = ['end_at', '<=', $endAt];
        }

        $terminalCode = array_get($credentials, 'terminal_code');
        if (!empty($terminalCode)) {
            $adIds = AdTerminal::where('terminal_code', $terminalCode)->pluck('ad_id')->toArray();
            $ad->whereIn('id', $adIds);
        }

        $adList = $ad->where($cond)
            ->orderBy('created_at', 'desc')
            ->skip($pageSize * ($pageNo - 1))
            ->take($pageSize)
            ->get()
            ->toArray();

        $mediaTypes = $this->ad->getMediaTypes();
        $mediaTypes = array_get($mediaTypes, 'data');
        $mediaTypes = array_column($mediaTypes, NULL, 'code');
        $mediaCodes = array_column($mediaTypes, 'code');

        $operationTypes = $this->operationType->getOperationTypes();
        $operationTypes = array_get($operationTypes, 'data');
        $operationTypes = array_column($operationTypes, NULL, 'code');
        $operationCodes = array_column($operationTypes, 'code');

        $locations = $this->adLocation->getAdLocations();
        $locations = array_get($locations, 'data');
        $locations = array_column($locations, NULL, 'code');
        $locationCodes = array_column($locations, 'code');

        $userIdList = array_column($adList, 'updated_user_id');
        $userList = $this->user->getUserListByUserIdList($userIdList);
        $userList = array_column($userList, NULL, 'id');
        $userIdList = array_column($userList, 'id');

        foreach ($adList as &$ad) {
            if (in_array(array_get($ad, 'media_code'), $mediaCodes)) {
                $ad['media_type'] = $mediaTypes[$ad['media_code']]['name'];
            }

            if (in_array(array_get($ad, 'operation_code'), $operationCodes)) {
                $ad['operation_type'] = $operationTypes[$ad['operation_code']]['name'];
            }

            if (in_array(array_get($ad, 'location_code'), $locationCodes)) {
                $ad['location_type'] = $locations[$ad['location_code']]['name'];
            }

            if (in_array(array_get($ad, 'updated_user_id'), $userIdList)) {
                $ad['updated_user_name'] = $userList[$ad['updated_user_id']]['name'];
            }
        }

        return $adList;
    }

    public function getAdCnt(array $credentials)
    {
        $cond = [];
        $ad = Ad::select();

        foreach ($credentials as $k => $v) {
            if (in_array($k, ['location_code', 'operation_code']) && $v !== "" && $v !== null) {
                $cond[] = [$k, '=', $v];
            }
        }

        $startAt = array_get($credentials, 'start_at');
        if (!empty($startAt)) {
            $cond[] = ['start_at', '>=', $startAt];
        }

        $endAt = array_get($credentials, 'end_at');
        if (!empty($endAt)) {
            $cond[] = ['end_at', '<=', $endAt];
        }

        $terminalCode = array_get($credentials, 'terminal_code');
        if (!empty($terminalCode)) {
            $adIds = AdTerminal::where('terminal_code', $terminalCode)->pluck('ad_id')->toArray();
            $ad->whereIn('id', $adIds);
        }

        $adCnt = $ad->where($cond)->count();

        return $adCnt;
    }

    public function getAdListBySpecialLocationCodes(array $locationCodes, array $adIdsOfPermission, string $terminalCode, int $expiresTime, int $isDefaultAd = 1)
    {
        $adIds = AdTerminal::where('terminal_code', $terminalCode)->whereIn('ad_id', $adIdsOfPermission)->pluck('ad_id')->toArray();

        $allAdLocationData = $this->adLocation->getAdLocations();
        $allAdLocation = array_get($allAdLocationData, 'data');
        $allAdLocation = array_column($allAdLocation, NULL, 'code');

        $adLocations = [];
        foreach ($locationCodes as $locationCode) {
            if (!isset($allAdLocation[$locationCode])) {
                throw new MatrixException("请求广告位Code不存在", AD_LOCATION_CODE_NOT_FOUND);
            }
            array_push($adLocations, $allAdLocation[$locationCode]);
        }

        

        $adsData = $this->ad->getAdAccessIdDatasBylocationCodes($adLocations, $adIds);
        if (array_get($adsData, 'code') === SYS_STATUS_OK) {
            $ads = array_get($adsData, 'data');
        } else {
            throw new MatrixException(array_get($adsData, 'msg'), array_get($adsData, 'code'));
        }

        foreach ($ads as $i => $item) {
            if (sizeof($item) === 0 && 1 == $isDefaultAd) {
                $item = $this->ad->getDefaultAdDataByLocationCode($adLocations[$i]);
                $ads[$i] = array_get($item, 'data');
            }
        }

        if ($expiresTime <= time()) {
            foreach ($ads as &$adsOfLocationCode) {
                foreach ($adsOfLocationCode as &$ad) {
                    $ad['poster_base64'] = self::getImageBase64($ad['poster_url']);
                }
            }
        }

        $adsData['data'] = $ads;

        return $adsData;
    }

    private function getImageBase64 (string $imgUrl)
    {
        $imgBase64 = '';

        $imgFileName = substr($imgUrl, strrpos($imgUrl, '/') + 1);

        $cacheKey = sprintf(self::IMAGE_BASE64_CACHE_FORMAT, $imgFileName);
        $imgBase64 = Cache::get($cacheKey);

        if (NULL === $imgBase64) {
            if (substr($imgUrl, 0, strlen('http')) !== 'http') {
                $imgUrl = "http:" . $imgUrl;
            }
    
            $file = fopen($imgUrl, "r");
            if ($file) {
                $imgChar = '';
                while (!feof($file)) {
                    $imgChar = $imgChar . fgets($file);
                }
                fclose($file);
    
                $imgBase64 = base64_encode($imgChar);
                Cache::put($cacheKey, $imgBase64, 1440);
            }
        }

        return $imgBase64;
    }
}