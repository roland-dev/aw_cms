<?php

namespace Matrix\Models;

use Exception;
use Log;
use DB;

class Course extends BaseModel
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'full_text_description',
        'background_picture',
        'course_system_code',
        'creator_user_id',
        'sort_no',
        'active',
    ];


    public function createCourse($name, $code, $description, $courseSystemCode, $userId, $backgroundPic, $sortNo, $fullTextDescription)
    {
        $createData = [
            'name' => $name,
            'code' => $code,
            'description' => $description,
            'full_text_description' => $fullTextDescription,
            'background_picture' => $backgroundPic,
            'course_system_code' => $courseSystemCode,
            'creator_user_id' => $userId,
            'sort_no' => $sortNo,
            'active' => 1,
        ];

        try {
            DB::beginTransaction(); 
            $updateRowNum = 0;
            $courseRecord = self::where('code', $code)->get()->toArray();
            if(!empty($courseRecord)){ 
                $active = array_get($courseRecord[0], 'active'); 
                if(!empty($active)){
                    $ret = [ 'resp_code' => SYS_STATUS_COURSE_EXISTS ];
                    return $ret;
                }
                $updateRowNum = self::where('id', $courseRecord[0]['id'])->update($createData); 
            }
            $courseRecord = $updateRowNum <= 0 ? self::create($createData) : self::find($courseRecord[0]['id']);
            $courseCnt = self::where($createData)->count();
            if($courseCnt == 1){
                DB::commit();
                return $courseRecord->toArray();
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

    public function updateRecord($courseId, $name, $code, $description, $courseSystemCode, $userId, $backgroundPic, $fullTextDescription)
    {
        $updateData = [
            'name' => $name,
            'code' => $code,
            'description' => $description,
            'full_text_description' => $fullTextDescription,
            'background_picture' => $backgroundPic,
            'course_system_code' => $courseSystemCode,
            'creator_user_id' => $userId,
        ];

        try{
            DB::beginTransaction();
            $updateRowNum = self::where('id', $courseId)->update($updateData);
            $ret = [
                'code' => $updateRowNum > 0 ? SYS_STATUS_OK : SYS_STATUS_COURSE_NOT_EXISTS,
            ];
            DB::commit();
        }catch (Exception $e) {
            \Log::error(SYS_STATUS_ERROR_UNKNOW, [$e]);
            DB::rollBack();
            $ret = [ 'code' => SYS_STATUS_ERROR_UNKNOW];
        }
        return $ret;
    }

    public function getRecordsBeforeModify($condition)
    {
        $repData = self::whereIn('id', $condition)->where('active', 1)->get(); 
        return empty($repData) ? [] : $repData->toArray();
    }

    public function remove($courseId)
    {
        try{
            $courseInfo = self::find($courseId);     
            if(empty($courseInfo)){
                return []; 
            }
            $courseInfo->active = 0;
            $courseInfo->save();
        }catch (QueryException $e) {
            \Log::error($e->getMessage());
        }     
        return $courseInfo;
    }

    public function removeCourseByCode($courseSystemCode)
    {
        try{
            $updateData = self::where('course_system_code', $courseSystemCode)->update(['active' => 0]);
        }catch (Exception $e) {
            \Log::error($e->getMessage());
        }
        return $updateData;
    }

    public function show($courseCode)
    {
        if(empty($courseCode)){
            $courseList = self::where('active', 1)->orderBy('updated_at', 'desc')->get();
        }else{
            $courseList = self::whereIn('code', $courseCode)->where('active', 1)->orderBy('updated_at', 'desc')->get();
            //$courseList = self::where([['active', 1], ['code', $courseCode]])->orderBy('updated_at', 'desc')->get();
        }
        return empty($courseList) ? [] : $courseList->toArray();
    }

    public function search($courseName, $courseSystemCode)
    {
        if(!empty($courseName)){
            $condition[] = ['name', 'like', '%'.$courseName.'%'];
        }
        if(!empty($courseSystemCode) && -1 != $courseSystemCode){
            $condition[] = ['course_system_code', '=', $courseSystemCode];
        }
        $condition[] = ['active', '=', 1];

        $courseList = self::where($condition)->orderBy('updated_at', 'desc')->get();
        return empty($courseList) ? [] : $courseList->toArray();
    }

    public function getOneInfo($courseId)
    {
        $oneCourseInfo = self::find($courseId);
        return empty($oneCourseInfo) ? [] : $oneCourseInfo->toArray();
    }
  
    public function getCourseListByCourseSystemCode($courseSystemCode)
    {
        $courseList = self::where('course_system_code', $courseSystemCode)->where('active', 1)->get();
        return empty($courseList) ? [] : $courseList->toArray();
    }

    public function checkCourseCodeUnique($courseCode)
    {
        $checkResp = self::where('code', $courseCode)->where('active', 1)->get();
        return empty($checkResp) ? [] : $checkResp->toArray();
    }

    public function findRecordByCode($code)
    {
        $courseInfo = self::where('code', $code)->get();
        return empty($courseInfo) ? [] : $courseInfo->toArray();
    }

    public function findRecordByCourseId($courseId)
    {
        $courseInfo = self::where('id', $courseId)->get();
        return empty($courseInfo) ? [] : $courseInfo->toArray();
    }

    public function updateRecordByCode($newCode, $oldCode)
    {
        try{
            $updateRes = self::where('course_system_code', $oldCode)->update(['course_system_code' => $newCode]);
        }catch (Exception $e) {
            $updateRes = 0;
            Log::error(SYS_STATUS_ERROR_UNKNOW, [$e]);
        }
        return $updateRes;
    }

    public function updateOrder($sequence, $courseId)
    {
        $updateData = [
            'sort_no' => $sequence,
            'id' => $courseId,
        ];
        try{
            $updateRes = self::where('active', 1)->where('id', $courseId)->update($updateData);
        } catch (QueryException $e) {
            $updateRes = NULL;
            Log::error(SYS_STATUS_ERROR_UNKNOW, [$e]);
        }
        return $updateRes;
    }

    public function checkCourse($courseSystemCode, $courseCode)
    {
        $checkCourse = self::where(['code' => $courseCode, 'course_system_code' => $courseSystemCode, 'active' => 1])->get();
        return empty($checkCourse) ? [] : $checkCourse->toArray();
    }

    public function getCourseCodeListByCourseSystemCode($courseSystemCode)
    {
        $courseCodeList = self::where(['course_system_code' => $courseSystemCode, 'active' => 1])->get();
        return empty($courseCodeList) ? [] : $courseCodeList->toArray();
    }

    public function getCourseListByCodeList(array $codeList)
    {
        $courseList = self::whereIn('code', $codeList)->where('active', 1)->get();

        return empty($courseList) ? [] : $courseList->toArray();
    }

    public function getCourseListByCodeListOrderSort(array $codeList)
    {
        $courseList = self::whereIn('code', $codeList)->where('active', 1)->orderBy('sort_no', 'desc')->orderBy('created_at', 'desc')->get();

        return empty($courseList) ? [] : $courseList->toArray();
    }
}
