<?php

namespace Matrix\Models;

use Exception;
use Log;
use DB;

class CourseSystem extends BaseModel
{
 
    protected $fillable = [
        'name',
        'code',
        'creator_user_id',
        'sort_no',
        'primary_category',
        'active',
    ];

    public function createRecord($name, $code, $userId, $sortNo, $categoryCode)
    {
        $createData = [
            'name' => $name,
            'code' => $code,
            'creator_user_id' => $userId,
            'sort_no' => $sortNo,
            'primary_category' => $categoryCode,
            'active' => 1,
        ];
 
        try {
            DB::beginTransaction();
            $updateRowNum = 0;
            $courseSystemRecord = self::where('code', $code)->get()->toArray();
            if(!empty($courseSystemRecord)){
                $active = array_get($courseSystemRecord[0], 'active');
                if(!empty($active)){
                    $ret = [ 'resp_code' => SYS_STATUS_COURSE_SYSTEM_EXISTS ];
                    return $ret;
                }
                $updateRowNum = self::where('id', $courseSystemRecord[0]['id'])->update($createData);
            }
            $courseSystemRecord = $updateRowNum <= 0 ? self::create($createData) : self::find($courseSystemRecord[0]['id']);
            $courseSystemCnt = self::where($createData)->count();
            if($courseSystemCnt == 1){ 
                DB::commit();
                return $courseSystemRecord->toArray();
            }else{
                DB::rollBack();
            }   
        }catch (Exception $e) {
            Log::error(SYS_STATUS_ERROR_UNKNOW, [$e]);
            DB::rollBack();
            $ret = [ 'resp_code' => SYS_STATUS_ERROR_UNKNOW ];
        }   

        return []; 
    }

    public function getRecordsBeforeModify($condition)
    {
        $repData = self::whereIn('id', $condition)->where('active', 1)->get(); 
        return empty($repData) ? [] : $repData->toArray();
    }

    public function updateRecord($courseSystemId, $name, $code, $userId, $categoryCode)
    {
        $updateData = [
            'name' => $name,
            'code' => $code,
            'creator_user_id' => $userId,    
            'primary_category' => $categoryCode,
        ];
 
        try{
            $updateRes = self::where('active', 1)->where('id', $courseSystemId)->update($updateData);
        }catch (QueryException $e) {
            \Log::error($e->getMessage()); 
        }
        return $updateRes; 
    }

    public function remove($courseSystemId)
    {
        try{
            $courseSystemInfo = self::find($courseSystemId);
            if(empty($courseSystemInfo)){
                return [];
            }
            $courseSystemInfo->active = 0;
            $courseSystemInfo->save(); 
        }catch (QueryException $e) {
            \Log::error($e->getMessage()); 
        }
        return $courseSystemInfo;
    }

    public function show()
    {
        $courseSystemList = self::where('active', 1)->orderBy('updated_at', 'desc')->get();
        return empty($courseSystemList) ? [] : $courseSystemList->toArray();
    }

    public function showList($categoryCode)
    {
        $courseSystemList = self::where('active', 1)->where('primary_category', $categoryCode)->orderBy('updated_at', 'desc')->get();
        return empty($courseSystemList) ? [] : $courseSystemList->toArray();
    }


    public function getOneInfo($courseSystemId)
    {
        $oneCourseSystemInfo = self::find($courseSystemId);
        return empty($oneCourseSystemInfo) ? [] : $oneCourseSystemInfo->toArray();
    }

    public function findRecordByCode($code)
    {
        $courseSystemInfo = self::where('code', $code)->get(); 
        return empty($courseSystemInfo) ? [] : $courseSystemInfo->toArray(); 
    }

    public function findRecordByCourseSystemId($courseSystemId)
    {
        $courseSystemInfo = self::where('id', $courseSystemId)->get();
        return empty($courseSystemInfo) ? [] : $courseSystemInfo->toArray(); 
    }

    public function checkCourseSystemCodeUnique(string $courseSystemCode)
    {
        $checkResp = self::where([['code', $courseSystemCode], ['active', 1]])->get();
        return empty($checkResp) ? [] : $checkResp->toArray();
    }

    public function updateOrder($sequence, $courseSystemId)
    {
        $updateData = [
            'sort_no' => $sequence,
            'id' => $courseSystemId,
        ];

        try{
            $updateRes = self::where('active', 1)->where('id', $courseSystemId)->update($updateData);
        }catch (QueryException $e) {
            $updateRes = NULL;
            \Log::error($e->getMessage());
        }
        return $updateRes;
    }
}
