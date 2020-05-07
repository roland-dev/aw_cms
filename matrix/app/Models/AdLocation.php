<?php

namespace Matrix\Models;

use Illuminate\Database\Eloquent\ModelNotFoundException;

use Exception;

class AdLocation extends BaseModel
{
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'name',
        'num',
        'size',
        'file_size',
        'popup_img_size',
        'popup_img_file_size'
    ];


    public function getAdLocation(string $locationCode)
    {
        $adLocationObj = self::where('code', $locationCode)->first();
        return empty($adLocationObj) ? [] : $adLocationObj->toArray();
    }

    public function getAdLocations()
    {
        $resultData = [
            "code" => "",
            "data" => [],
            "msg" => ""
        ];

        try {
            $adLocations = self::select('code', 'name', 'size', 'file_size', 'popup_img_size', 'popup_img_file_size')
                ->where('disabled', 0)
                ->get()
                ->toArray();
        } catch (Exception $e) {
            $e->getMessage();
            $resultData['code'] = SYS_STATUS_ERROR_UNKNOW;
            $resultData['msg'] = "查询失败，服务器错误";
            return $resultData;
        }

        $resultData['data'] = $adLocations;
        $resultData['code'] = SYS_STATUS_OK;
        return $resultData;
    }
}
