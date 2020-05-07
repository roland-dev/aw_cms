<?php

namespace Matrix\Models;

use Illuminate\Database\QueryException;

class VideoSignin extends BaseModel
{
    
    protected $fillable = [
        'video_key',
        'creator_user_id',
        'url',
        'category',
        'author',
        'title',
        'published_at',
        'description',
        'is_public',
        'active',
        'category_code',
    ];

    public function generateStr()
    {
        return md5(str_random(32));
    }

    public function select($categoryCodeList)
    {
        try{
            $videoSigninList = self::where('active', 1)->whereIn('category_code', $categoryCodeList)->orderBy('updated_at', 'desc')->get();
        } catch (QueryException $e) {
            $videoSigninList = NULL;
            $e->getMessage();
        }
        return empty($videoSigninList) ? [] : $videoSigninList->toArray();
    }

    public function insert($userId, $url, $categoryCode, $author, $title, $publishedAt, $description, $isPublicPlayer)
    {
       $time = strtotime($publishedAt);
	   $videoKey = $this->generateStr();

       $verifyData = [
           'video_key' => $videoKey,
       ];

       $createData = [
           'video_key'=> $videoKey,
           'creator_user_id'=> $userId,
           'url' => $url,
           //'category'=> $category,
           'category_code' => $categoryCode,
           'author' => $author,
           'title' => $title,
           'is_public' => $isPublicPlayer,
           'published_at'=> date('Ymd', $time),
           'description' => $description,
           'active' => 1,
       ];

       try{
           $videoSignin = self::updateOrCreate($verifyData, $createData);
       }catch(QueryException $e){
           $videoSignin = NULL;
           \Log::error($e->getMessage());
       }

       return $videoSignin;
    }

    public function updateRecord($userId,$videoId, $url, $categoryCode,  $author, $title, $publishedAt, $description)
    {
       $time = strtotime($publishedAt);
       $updateData = [
           'id' => $videoId,
           'creator_user_id'=> $userId,
           'url' => $url,
           //'category'=> $category,
           'category_code'=> $categoryCode,
           'author' => $author,
           'title' => $title,
           'published_at'=> date('Ymd', $time),
           'description' => $description
       ];
       try{
           self::where('active', 1)->where('id', $videoId)->update($updateData);
       }catch(QueryException $e){
           $e->getMessage();
       }
    }

    public function destory($videoId)
    {
       try{
           self::where('id', $videoId)->update(['active' => 0]);
       }catch(QueryException $e){
           $e->getMessage();
       }
    }

    public function getOneDetail($videoId)
    {
       $getOneDetail = self::where('active', 1)->find($videoId);
       return empty($getOneDetail) ? [] : $getOneDetail->toArray();
    }


    public function show($condition)
    {
        $repData = self::whereIn('id', $condition)->where('active', 1)->get();
        return empty($repData) ? [] : $repData->toArray();
    }

    public function search($categoryCode, $author, $title, $sTime, $eTime, $categoryCodeList)
    {
        $condition = [];
        if(!empty($categoryCode)){
            $condition[] = ['category_code', '=', $categoryCode];
        }

        if(!empty($author) && -1 != $author){
            $condition[] = ['author', '=', $author];
        }
        if(!empty($title)){
            $condition[] = ['title', 'like', '%'.$title.'%'];
        }
        if(!empty($sTime)){
            $sTime = date('Ymd', strtotime($sTime));
            $condition[] = ['published_at', '>=', $sTime];
        }
        if(!empty($eTime)){
            $eTime = date('Ymd', strtotime($eTime));
            $condition[] = ['published_at', '<=', $eTime];
        }
        $condition[] = ['active', '=', 1];
        $videoList = self::where($condition)->whereIn('category_code', $categoryCodeList)->orderBy('updated_at', 'desc')->get();
        return empty($videoList) ? [] : $videoList->toArray();
    }

    public function findByUrl(string $url)
    {
        $video = self::where('url', $url)->where('active', 1)->take(1)->first();
        return empty($video) ? [] : $video->toArray();
    }

    public function findByVideoKey($videoKey)
    {
        $video = self::where('video_key', $videoKey)->where('active', 1)->take(1)->first();
        return empty($video) ? [] : $video->toArray();
    }

    public function updateRecordByIdList($idList)
    {
        try{
            $updateRes = self::whereIn('id', $idList)->update(['active' => 0]);
        }catch(QueryException $e){
            $e->getMessage();
        }
        return $updateRes;
    }

    public function removeVideoSigninById($videoSigninIdList)
    {
        try{
            $removeRes = self::whereIn('id', $videoSigninIdList)->update(['active' => 0]);
        }catch(QueryException $e){
            $e->getMessage();
        }
        return $removeRes;
    }

    public function getVideoListByIdListAndUserId(array $videoIdList, int $userId)
    {
        $videoList = self::whereIn('id', $videoIdList)->where('author', $userId)->get();

        return empty($videoList) ? [] : $videoList->toArray();
    }
}
