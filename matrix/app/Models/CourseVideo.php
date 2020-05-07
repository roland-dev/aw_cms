<?php

namespace Matrix\Models;

use Illuminate\Database\QueryException;

class CourseVideo extends BaseModel
{
    protected $fillable = [
        'picture_path',
        'is_display',
        'access',
        'watch',
        'end',
        'course_code',
        'video_signin_id',
        'sort_no',
        'tag',
        'demo_url',
        'ad_guide',
        'active',
    ];

    public function insert($thumbnailPreviewPath, $isDisplay, $videoSigninId, $pvUv, $courseCode, $sortNo, $tag, $demoUrl, $adGuide)
    {
        $getPvUvCount =  array_get($pvUv, 'get_pvuv');
        $playPvUvCount =  array_get($pvUv, 'play_pvuv');
        $finishPvUvCount =  array_get($pvUv, 'finish_pvuv');

        //$picturePath[] = $imagePath;
        //$picturePath[] = $thumbnailPath;
        //$picturePath[] = $thumbnailPreviewPath;
        //$path = json_encode($picturePath);
  
        $verifyData = [
            'video_signin_id' => $videoSigninId,
        ];

        $createData = [
            'picture_path' => $thumbnailPreviewPath,
            'is_display' => $isDisplay,
            'access' => $getPvUvCount,
            'watch' => $playPvUvCount,
            'end' => $finishPvUvCount,
            'video_signin_id' => $videoSigninId,
            'course_code' => $courseCode,
            'sort_no' => $sortNo,
            'active' => 1,
            'tag' => $tag,
            'demo_url' => $demoUrl,
            'ad_guide' => $adGuide,
        ];
        try{
            $insertRes = self::updateOrCreate($verifyData, $createData);
        }catch (QueryException $e) {
            $insertRes = NULL;
            \Log::error($e->getMessage());
        }
        return $insertRes;
    }

    public function updateRecord($courseVideoId, $thumbnailPreviewPath, $isDisplay, $videoSigninId, $pvUv, $tag, $demoUrl, $adGuide)
    {
        $getPvUvCount =  array_get($pvUv, 'get_pvuv');
        $playPvUvCount =  array_get($pvUv, 'play_pvuv');
        $finishPvUvCount =  array_get($pvUv, 'finish_pvuv');

        //$picturePath[] = $imagePath;
        //$picturePath[] = $thumbnailPath;
        //$picturePath[] = $thumbnailPreviewPath;
        //$path = json_encode($picturePath);
   
        $updateData = [
            'id' => $courseVideoId,
            //'picture_path' => $path,
            'picture_path' => $thumbnailPreviewPath,
            'is_display' => $isDisplay,
            'access' => $getPvUvCount,
            'watch' => $playPvUvCount,
            'end' => $finishPvUvCount,
            'video_signin_id' => $videoSigninId,
            'tag' => $tag,
            'demo_url' => $demoUrl,
            'ad_guide' => $adGuide,
        ];
        try{
            $updateRes = self::where('active', 1)->where('id', $courseVideoId)->update($updateData);
        }catch (QueryException $e) {
            \Log::error($e->getMessage());
        }
        return $updateRes;
    }

    public function updateRecordByCode($newCode, $oldCode)
    {
        try{
            $updateRes = self::where('course_code', $oldCode)->update(['course_code' => $newCode]);
        }catch (QueryException $e) {
            \Log::error($e->getMessage());
        }
        return $updateRes;
    }

    public function destory($courseVideoId)
    {
        try{
            $courseVideoDel = self::where('id', $courseVideoId)->update(['active' => 0]);
        }catch (QueryException $e) {
            $coruseVideoDel = 0;
            $e->getMessage();
        }
        return $courseVideoDel; 
    }

    public function getCourseVideoInfo($courseVideoId)
    {
        //$courseVideoInfo = self::where('active', 1)->where('video_signin_id', $courseVideoId)->first();
        $courseVideoInfo = self::where('active', 1)->find($courseVideoId);
        return empty($courseVideoInfo) ? [] : $courseVideoInfo->toArray(); 
    }

    public function getCourseVideoInfoVideoSigninId($videoSigninId)
    {
        $courseVideoInfo = self::where('active', 1)->where('video_signin_id', $videoSigninId)->first();
        return empty($courseVideoInfo) ? [] : $courseVideoInfo->toArray();
    }


    public function getCourseVideoList($courseCode)
    {
        $courseVideoList = self::where('active', 1)->where('course_code', $courseCode)->orderBy('updated_at', 'desc')->get();
        return empty($courseVideoList) ? [] : $courseVideoList->toArray(); 
    }
   
    public function show($condition)
    {
        $repData = self::whereIn('id', $condition)->where('active', 1)->get();
        return empty($repData) ? [] : $repData->toArray();
    }

    public function getCourseVideoListByCode($courseCode)
    {
        $courseVideoList = self::where('course_code', $courseCode)->where('active', 1)->get();
        return empty($courseVideoList) ? [] : $courseVideoList->toArray();
    }

    public function getList()
    {
        $courseVideoList = self::where('active', 1)->orderBy('sort_no', 'desc')->orderBy('created_at', 'desc')->get();
        return empty($courseVideoList) ? [] : $courseVideoList->toArray();
    }

    public function removeCourseVideoByCode($courseCodeList)
    {
        try{
            $removeList = self::whereIn('course_code', $courseCodeList)->update(['active' => 0]); 
        }catch (QueryException $e) {
            \Log::error($e->getMessage());
        }
        return $removeList;
    }

    //public function removeCourseVideoBySingleCode($courseCode)
    //{
    //    try{
    //        $removeRes = self::where('course_code', $courseCode)->update(['active' => 0]); 
    //    }catch (QueryException $e) {
    //        \Log::error($e->getMessage());
    //    }
    //    return $removeRes;

    //}

    public function checkCourseCodeUnique($courseCode)
    {
        $checkResp = self::where('code', $courseCode)->where('active', 1)->get();
        return empty($checkResp) ? [] : $checkResp->toArray();
    }

    public function getVideoSigninIdList($courseCodeList)
    {
        $courseVideoList = self::whereIn('course_code', $courseCodeList)->where('active', 1)->get();
        return empty($courseVideoList) ? [] : $courseVideoList->toArray();
    }

    public function updateOrder($sequence, $courseVideoId)
    {
        $updateData = [
            'sort_no' => $sequence,
            'id' => $courseVideoId,
        ];

        try{
            $updateRes = self::where('active', 1)->where('id', $courseVideoId)->update($updateData);
        }catch (QueryException $e) {
            $updateRes = NULL;
            \Log::error($e->getMessage());
        }
        return $updateRes;
    }

    public function getCourseVideoInfoByVideoSignId($videoSigninId)
    {
        $courseVideoInfo = self::where('video_sign_id', $videoSigninId)->get(); 
        return empty($courseVideoInfo) ? [] : $courseVideoInfo->toArray();
    }
}
