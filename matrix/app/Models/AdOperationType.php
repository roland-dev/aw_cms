<?php

namespace Matrix\Models;


class AdOperationType extends BaseModel
{
    //
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'name'
    ];

    public function getOperationType(string $adOperationCode)
    {
        $adOperationType = self::where('code', $adOperationCode)->first();
        return empty($adOperationType) ? [] : $adOperationType->toArray();
    }


    public function getOperationTypes() {
        $resultData = [
            "code" => "",
            "data" => [],
            "msg" => ""
        ];

        try {
            $operationTypes = AdOperationType::where('disabled', 0)->get();
            $data = [];
            $i = 0;
            foreach ($operationTypes as $operationType) {
                $data[$i] = [
                    "code" => $operationType->code,
                    "name" => $operationType->name
                ];
                $i++;
            }
            $resultData['data'] = $data;
        } catch (\Exception $e) {
            $e->getMessage();
            $resultData['code'] = SYS_STATUS_ERROR_UNKNOW;
            $resultData['msg'] = "查询失败，服务器错误";
            return $resultData;
        }
        $resultData['code'] = SYS_STATUS_OK;
        return $resultData;
    }
}
