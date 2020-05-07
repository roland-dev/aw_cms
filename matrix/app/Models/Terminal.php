<?php

namespace Matrix\Models;

class Terminal extends BaseModel
{
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'name'
    ];

    public function getTerminals()
    {
        $resultData = [
            "code" => "",
            "data" => [],
            "msg" => ""
        ];

        try {
            $adTerminals = Terminal::where('disabled', 0)->get();
            $data = [];
            $i = 0;
            foreach ($adTerminals as $adTerminal) {
                $data[$i] = [
                    "code" => $adTerminal->code,
                    "name" => $adTerminal->name
                ];
                $i++;
            }
            $resultData['data'] = $data;
        } catch(\Exception $e) {
            $e->getMessage();
            $resultData['code'] = SYS_STATUS_ERROR_UNKNOW;
            $resultData['msg'] = "查询失败，服务器错误";
        }

        $resultData['code'] = SYS_STATUS_OK;
        return $resultData;
    }
}
