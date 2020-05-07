<?php

namespace Matrix\Models;

use Exception;
use Log;
use DB;

class ContentGuard extends BaseModel
{
    //
    protected $fillable = ['service_code', 'uri', 'param1', 'param2', 'param3'];
    protected $blankRow = [
        'service_code' => '',
        'uri' => '',
        'param1' => null,
        'param2' => null,
        'param3' => null,
    ];

    public function getOneInfo(string $uri, string $courseSystemCode, string $courseCode)
    {
        $condition = [
            'uri' => $uri,
            'param1' => $courseSystemCode,
            'param2' => $courseCode,
        ];
        $contentGuardInfo = self::where($condition)->get();

        return empty($contentGuardInfo) ? [] : $contentGuardInfo->toArray();
    }

    public function getContentGuardByUriAndCode(string $uri, array $serviceKeyList = [])
    {
        if (empty($serviceKeyList)) {
            return [];
        }
        $contentGuardList = self::where('uri', $uri)->whereIn('service_code', $serviceKeyList)->get();

        return empty($contentGuardList) ? [] : $contentGuardList->toArray();
    }

    public function search(string $uri = '', array $serviceKeyList = [], string $param1 = '', string $param2 = '', string $param3 = '')
    {
        $condition = [];
        if (!empty($uri)) {
            $condition[] = ['uri', '=', $uri];
        }
        if (!empty($param1)) {
            $condition[] = ['param1', '=', $param1];
        }
        if (!empty($param2)) {
            $condition[] = ['param2', '=', $param2];
        }
        if (!empty($param3)) {
            $condition[] = ['param3', '=', $param3];
        }

        if (empty($condition) && empty($serviceKeyList)) { // all of condition empty
            $contentGuardList = self::get();
        } elseif (empty($condition) && !empty($serviceKeyList)) { // uri and params empty, but serviceKeyList not
            $contentGuardList = self::whereIn('service_code', $serviceKeyList)->get();
        } elseif (!empty($condition) && empty($serviceKeyList)) { // serviceKeyList is empty but uri and params not
            $contentGuardList = self::where($condition)->get();
        } else { // both the serviceKeyList and uri and params not empty
            $contentGuardList = self::where($condition)
                ->whereIn('service_code', $serviceKeyList)->get();
        }

        return empty($contentGuardList) ? [] : $contentGuardList->toArray();
    }

    public function updateContentGuard(array $condition, array $contentGuardData)
    {
        try {
            DB::beginTransaction();
            $updateRowNum = self::where($condition)->update($contentGuardData);

            $ret = [
                'code' => $updateRowNum > 0 ? SYS_STATUS_OK
                        : SYS_STATUS_CONTENT_GUARD_NOT_EXISTS,
                'data' => [
                    'update_row_num' => $updateRowNum,
                ],
            ];
            DB::commit();
        } catch(Exception $e) {
            Log::error(SYS_STATUS_ERROR_UNKNOW, [$e]);
            DB::rollBack();
            $ret = [ 'code' => SYS_STATUS_ERROR_UNKNOW ];
        }

        return $ret;
    }

    public function createContentGuard(array $newData)
    {
        try {
            DB::beginTransaction();
            $blankContentGuardList = self::where($this->blankRow)->get();
            $updateRowNum = 0;
            if (!empty($blankContentGuardList)) {
                foreach ($blankContentGuardList as $blankContentGuard) {
                    $updateRowNum = self::where('id', $blankContentGuard->id)
                                                           ->update($newData);
                    if ($updateRowNum > 0) {
                        break;
                    }
                }
            }

            $contentGuard = $updateRowNum <= 0 ? self::create($newData)
                                     : self::find($blankContentGuard->id);

            $contentGuardCnt = self::where($newData)->count();
            if ($contentGuardCnt == 1) {
                DB::commit();
                return $contentGuard->toArray();
            } else {
                DB::rollBack();
            }
        } catch (Exception $e) {
            Log::error(SYS_STATUS_ERROR_UNKNOW, [$e]);
            DB::rollBack();
        }
        return [];
    }

    public function getContentGuardList(string $uri)
    {
        $contentGuardList = self::where('uri', $uri)->get();
        return empty($contentGuardList) ? [] : $contentGuardList->toArray();
    }

    public function getContentGuardInfo($param1, $param2, $uri)
    {
        $contentGuardInfo = self::where(['param1' => $param1, 'param2' => $param2, 'uri' => $uri])->first();
        return empty($contentGuardInfo) ? [] : $contentGuardInfo->toArray();
    }
}
