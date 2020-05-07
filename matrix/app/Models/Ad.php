<?php

namespace Matrix\Models;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\SoftDeletes;

use Exception;
use Illuminate\Support\Facades\DB;

class Ad extends BaseModel
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'location_code',
        'media_code',
        'title',
        'img_src',
        'url_link',
        'start_at',
        'end_at',
        'sort_num',
        'disabled',
        'operation_code',
        'operation_id',
        'need_popup',
        'popup_poster_url',
        'jump_type',
        'jump_params',
        'updated_user_id',
        'creator_id'
    ];

    const CONTENT_GUARD_FOREIGN_KEY = 'param1';
    const URI_AD_ACCESS = '/api/v2/propaganda/ad/{adId}';

    const AD_MEDIA_TYPE = [
        [
            "code" => "image",
            "name" => "图片"
        ]
    ];

    protected $showImgFilePathMapping;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->showImgFilePathMapping = [
            '/files' => config('cdn.cdn_url'),
        ];
    }

    private function filterUrlLink($urlLink)
    {
        if (substr($urlLink, 0, strlen(config('front.forum_h5_url'))) === config('front.forum_h5_url')) {
            $result = substr($urlLink, strlen(config('front.forum_h5_url')));
        } else {
            $result = $urlLink;
        }
        return $result;
    }

    private function prefixUrlLink($urlLink)
    {
        if (empty($urlLink)) {
            $result = $urlLink;
        } else if (substr($urlLink, 0, strlen('http')) === 'http') {
            $result = $urlLink;
        } else {
            $result = config('front.forum_h5_url') . $urlLink;
        }
        return $result;
    }

    private function prefixImgSrc($imgSrc)
    {
        $result = "";
        foreach ($this->showImgFilePathMapping as $key => $value) {
            if (substr($imgSrc, 0, strlen($key)) === $key) {
                $result = $value . substr($imgSrc, strlen($key));
                return $result;
            }
        }
        return $result;
    }

    public function createAd(array $adData)
    {
        $adData['url_link'] = self::filterUrlLink(array_get($adData, 'url_link'));
        $adData['start_at'] = date('Y-m-d H:i:s', strtotime(array_get($adData, 'start_at')));
        $adData['end_at'] = date('Y-m-d H:i:s', strtotime(array_get($adData, 'end_at')));
        if (empty(array_get($adData, 'jump_type'))) {
            unset($adData['jump_type']);
        }
        if (empty(array_get($adData, 'jump_params'))) {
            unset($adData['jump_params']);
        }
    
        try {
            $adObj = self::create($adData);
        } catch (Exception $e) {
            $e->getMessage();
            $adObj = NULL;
        }
        
        return empty($adObj) ? [] : $adObj->toArray();
    }

    public function updateAd(int $adId, array $adData)
    {
        $adData['url_link'] = self::filterUrlLink(array_get($adData, 'url_link'));
        $adData['start_at'] = date('Y-m-d H:i:s', strtotime(array_get($adData, 'start_at')));
        $adData['end_at'] = date('Y-m-d H:i:s', strtotime(array_get($adData, 'end_at')));

        try {
            $adObj = self::where('id', $adId)->update($adData);
        } catch (Exception $e) {
            $e->getMessage();
            return NULL;
        }
        return $adObj;
    }

    public function detail(int $adId)
    {
        $condition = [
            self::CONTENT_GUARD_FOREIGN_KEY => $adId,
            'uri' => self::URI_AD_ACCESS
        ];
        try {
            $adObj = self::select('id', 'location_code','media_code', 'operation_code', 'operation_id', 'title', 'img_src', 'url_link', 'jump_type', 'jump_params', 'start_at', 'end_at', 'disabled', 'sort_num', 'need_popup', 'popup_poster_url')
                ->findOrFail($adId);
            $packages = DB::table('content_guards')
                    ->where($condition)
                    ->pluck('service_code')
                    ->toArray();
        } catch (ModelNotFoundException $e) {
            $e->getMessage();
            return [];
        } catch (Exception $e) {
            $e->getMessage();
            return [];
        }

        $adObj->img_url = self::prefixImgSrc($adObj->img_src);
        $adObj->url_link = self::prefixUrlLink($adObj->url_link);
        $adObj->popup_poster_link = self::prefixImgSrc($adObj->popup_poster_url);
        $adObj->permission_codes = $packages;

        return empty($adObj) ? [] : $adObj->toArray();
    }

    public function destoryAd(int $adId)
    {
        $result = false;

        try {
            $result = self::where('id', $adId)->delete();
        } catch (Exception $e) {
            $e->getMessage();
        }

        return $result;
    }


    /**
     * 判断 $ad_media_type 是否正确
     */
    public function checkMediaType(string $ad_media_type)
    {
        $result = false;
        foreach (self::AD_MEDIA_TYPE as $mediaCode) {
            if ($mediaCode['code'] === $ad_media_type) {
                $result = true;
            }
        }
        return $result;
    }


    public function getMediaType(string $ad_media_code)
    {
        $result = "";
        foreach (self::AD_MEDIA_TYPE as $mediaCode) {
            if ($mediaCode['code'] === $ad_media_code) {
                $result = $mediaCode['name'];
            }
        }
        return $result;
    }


    public function getMediaTypes()
    {
        $resultData = [
            "code" => "",
            "data" => [],
            "msg" => ""
        ];

        $resultData['code'] = SYS_STATUS_OK;
        $resultData['data'] = self::AD_MEDIA_TYPE;

        return $resultData;
    }

    public function getAdsData(array $adLocation, array $adIds)
    {
        $resultData = [
            "code" => '',
            "data" => []
        ];

        $nowDate = date('Y-m-d H:i:s', time());
        $locationCode = array_get($adLocation, 'code');
        $locationNum = array_get($adLocation, 'num');

        try {
            $ads = self::select('id', 'title', 'start_at', 'end_at', 'jump_params', 'jump_type', 'img_src', 'url_link', 'need_popup', 'popup_poster_url')
                    ->whereIn('id', $adIds)
                    ->where('end_at', '>', $nowDate)
                    ->where('start_at', '<', $nowDate)
                    ->where('disabled', 0)
                    ->where('location_code', $locationCode)
                    ->orderBy('sort_num', 'desc')
                    ->orderBy('end_at', 'asc')
                    ->limit( $locationNum )
                    ->get();
        } catch (Exception $e) {
            $e->getMessage();
            $resultData['code'] = SYS_STATUS_ERROR_UNKNOW;
            $resultData['msg'] = "广告列表获取失败，服务器错误";
            return $resultData;
        }

        $resultData['code'] = SYS_STATUS_OK;
        if (sizeof($ads) > 0) {
            $i = 0;
            $data = [];
            foreach ($ads as $ad) {
                $data[$i] = [
                    config('param_mapping.ad.id') =>  (string)$ad->id,
                    config('param_mapping.ad.title') => $ad->title,
                    config('param_mapping.ad.start_at') => $ad->start_at,
                    config('param_mapping.ad.end_at') => $ad->end_at,
                    config('param_mapping.ad.jump_type') => $ad->jump_type,
                    config('param_mapping.ad.img_src') => self::prefixImgSrc($ad->img_src),
                    config('param_mapping.ad.url_link') => self::prefixUrlLink($ad->url_link),
                    config('param_mapping.ad.need_popup') => $ad->need_popup,
                    config('param_mapping.ad.popup_img_src')=> self::prefixImgSrc($ad->popup_poster_url),
                    config('param_mapping.ad.source_url') => self::prefixUrlLink($ad->url_link)
                ];
                if (!empty($ad->jump_params)) {
                    $data[$i][config('param_mapping.ad.jump_params')] =  json_decode($ad->jump_params, true);
                }
                $i++;
            }
            $resultData['data'] = $data;
        }

        return $resultData;
    }

    public function getDefaultAdDataByLocationCode(array $adLocation)
    {
        $resultData = [
            "code" => '',
            "data" => []
        ];

        $defaultAdId = array_get($adLocation, 'default_ad_id');

        try {
            $ads = self::select('id', 'title', 'start_at', 'end_at', 'jump_params', 'jump_type', 'img_src', 'url_link', 'need_popup', 'popup_poster_url')
                    ->where('id', $defaultAdId)
                    ->get();
        } catch (Exception $e) {
            $e->getMessage();
            $resultData['code'] = SYS_STATUS_ERROR_UNKNOW;
            $resultData['msg'] = "查询失败，服务器错误";
            return $resultData;
        }

        $resultData['code'] = SYS_STATUS_OK;
        $resultData['total'] = sizeof($ads);
        if (sizeof($ads) > 0) {
            $data = [];
            $i = 0;
            foreach ($ads as $ad) {
                $data[$i] = [
                    config('param_mapping.ad.id') =>  (string)$ad->id,
                    config('param_mapping.ad.title') => $ad->title,
                    config('param_mapping.ad.start_at') => $ad->start_at,
                    config('param_mapping.ad.end_at') => $ad->end_at,
                    config('param_mapping.ad.jump_type') => $ad->jump_type,
                    config('param_mapping.ad.img_src') => self::prefixImgSrc($ad->img_src),
                    config('param_mapping.ad.url_link') => self::prefixUrlLink($ad->url_link),
                    config('param_mapping.ad.need_popup') => $ad->need_popup,
                    config('param_mapping.ad.popup_img_src')=> self::prefixImgSrc($ad->popup_poster_url),
                    config('param_mapping.ad.source_url') => self::prefixUrlLink($ad->url_link)
                ];
                if (!empty($ad->jump_params)) {
                    $data[$i][config('param_mapping.ad.jump_params')] =  json_decode($ad->jump_params, true);
                }
                $i++;
            }
            $resultData['data'] = $data;
        }
        return $resultData;
    }

    public function getAdAccessIdDatasBylocationCodes(array $adLocations, array $adIdsOfPermission)
    {
        $resultData = [
            "code" => '',
            "data" => []
        ];

        $nowDate = date('Y-m-d H:i:s', time());

        try {
            $adsOfLocationCodes = [];
            foreach($adLocations as $adLocation) {
                $locationCode = array_get($adLocation, 'code');
                $locationNum = array_get($adLocation, 'num');

                $ads = self::select('id', 'title', 'start_at', 'end_at', 'jump_params', 'jump_type', 'img_src', 'url_link', 'need_popup', 'popup_poster_url')
                    ->whereIn('id', $adIdsOfPermission)
                    ->where('end_at', '>', $nowDate)
                    ->where('start_at', '<', $nowDate)
                    ->where('disabled', 0)
                    ->where('location_code', $locationCode)
                    ->orderBy('sort_num', 'desc')
                    ->orderBy('end_at', 'asc')
                    ->limit( $locationNum )
                    ->get();
                array_push($adsOfLocationCodes, $ads);
            }
        } catch (Exception $e) {
            $e->getMessage();
            $resultData['code'] = SYS_STATUS_ERROR_UNKNOW;
            $resultData['msg'] = "广告列表获取失败，服务器错误";
            return $resultData;
        }

        $resultData['code'] = SYS_STATUS_OK;
        foreach ($adsOfLocationCodes as $ads) {
            $i = 0;
            $data = [];
            foreach ($ads as $ad) {
                $data[$i] = [
                    config('param_mapping.ad.id') =>  (string)$ad->id,
                    config('param_mapping.ad.title') => $ad->title,
                    config('param_mapping.ad.start_at') => $ad->start_at,
                    config('param_mapping.ad.end_at') => $ad->end_at,
                    config('param_mapping.ad.jump_type') => $ad->jump_type,
                    config('param_mapping.ad.img_src') => self::prefixImgSrc($ad->img_src),
                    config('param_mapping.ad.url_link') => self::prefixUrlLink($ad->url_link),
                    config('param_mapping.ad.need_popup') => $ad->need_popup,
                    config('param_mapping.ad.popup_img_src')=> self::prefixImgSrc($ad->popup_poster_url),
                    config('param_mapping.ad.source_url') => self::prefixUrlLink($ad->url_link)
                ];
                if (!empty($ad->jump_params)) {
                    $data[$i][config('param_mapping.ad.jump_params')] =  json_decode($ad->jump_params, true);
                }
                $i++;
            }
            array_push($resultData['data'], $data);
        }
        return $resultData;
    }

    /**
     * bool 是否选中正在展示或者尚未展示的广告
     */
    public function getAdListDataOfOperationId(int $operationId, string $operationCode, bool $bool)
    {
        $resultData = [
            "code" => "",
            "data" => [],
            "msg" => ""
        ];

        $nowDate = date('Y-m-d H:i:s', time());

        $data = [];
        try {
            $select = self::where([['operation_id', $operationId],['operation_code', $operationCode]]);
            if ($bool) {
                $select = $select->where('end_at', '>', $nowDate);
            }
            $ads = $select->get();
            foreach ($ads as &$ad) {
                $packages = DB::table('content_guards')
                        ->where([[self::CONTENT_GUARD_FOREIGN_KEY, $ad->id], ['uri', self::URI_AD_ACCESS]])
                        ->pluck('service_code')
                        ->toArray();
                $ad->permission_codes = $packages;
            }
        } catch (Exception $e) {
            $e->getMessage();
            $resultData['code'] = SYS_STATUS_ERROR_UNKNOW;
            $resultData['msg'] = "查询失败，服务器错误";
            return $resultData;
        }
        $resultData['code'] = SYS_STATUS_OK;
        $resultData['data'] = $ads->toArray();

        return $resultData;
    }

    public function destoryAdOfOtherModules(int $operationId, string $operationCode)
    {
        $resultData = [
            "code" => "",
            "msg" => "",
        ];

        try {
            self::where([['operation_id', $operationId],['operation_code', $operationCode]])->delete();
        } catch (Exception $e) {
            $e->getMessage();
            $resultData['code'] = SYS_STATUS_ERROR_UNKNOW;
            $resultData['msg'] = "广告删除失败，服务器错误";
            return $resultData;
        }

        $resultData['code'] = SYS_STATUS_OK;
        $resultData['msg'] = "删除成功";
        return $resultData;
    }
}