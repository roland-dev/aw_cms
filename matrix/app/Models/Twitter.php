<?php

namespace Matrix\Models;

class Twitter extends BaseModel
{
    //
    protected $fillable = ['content', 'category_code', 'teacher_id', 'operator_user_id', 'room_id', 'image_url', 'feed', 'source_id',
                              'ref_type', 'ref_id', 'ref_title', 'ref_thumb', 'ref_summary', 'ref_category_code', 'created_at', 'updated_at'];

    public function getTwitterList(array $categoryCodeList)
    {
        $twitterList = self::whereIn('category_code', $categoryCodeList)->orderBy('created_at', 'desc')->get();
        return $twitterList->toArray();
    }

    public function getPageTwitterList(array $categoryCodeList, int $twitterId, int $pageSize, array $operatorUserIdList = [], bool $hasReferContent = true, int $month = 0)
    {
        $model = self::whereIn('category_code', $categoryCodeList);
        if ($twitterId !== 0) {
            $model = $model->where('id', '<', $twitterId);
        }
        if (!empty($operatorUserIdList)) {
            $model = $model->whereIn('operator_user_id', $operatorUserIdList);
        }
        if (!$hasReferContent) {
            $model = $model->where('ref_id', '');
        }
        // 添加 日期限制 -- 最近三个月数据 2019/09/04
        if ($month > 0) {
            $date = date("Y-m-d 00:00:00", strtotime("-". $month ." month"));
            $model = $model->where('created_at', '>=', $date);
        }

        $twitterList = $model->orderBy('created_at', 'desc')->take($pageSize)->get();

        return $twitterList->toArray();
    }

    public function getUnfeedTwitterList(array $categoryCodeList)
    {
        $twitterList = self::whereIn('category_code', $categoryCodeList)->where('feed', 0)->get();

        return empty($twitterList) ? [] : $twitterList->toArray();
    }

    public function setTwitterFeed(array $twitterIdList)
    {
        self::whereIn('id', $twitterIdList)->update([
            'feed' => 1,
        ]);
    }

    public function removeRecord(int $twitterId)
    {
        self::destroy($twitterId);
    }

    public function getTwitterInfo(int $twitterId)
    {
        $twitterInfo =  self::find($twitterId);
        return empty($twitterInfo) ? [] : $twitterInfo->toArray();
    }

    public function getTwitterInfoBySourceId(string $sourceId)
    {
        $twitterInfo =  self::where('source_id', $sourceId)->first();
        return empty($twitterInfo) ? [] : $twitterInfo->toArray();
    }

    public function getTwitterListByRoomId(string $roomId, $startTime, $endTime, $hasReferContent)
    {
        $model = self::where('room_id', $roomId);

        if (!$hasReferContent) {
            $model = $model->where('ref_id', '');
        }

        if (!empty($startTime)) {
            $model = $model->where('created_at', '>=', $startTime);
        }

        if (!empty($endTime)) {
            $model = $model->where('updated_at', '<=', $endTime);
        }

        $twitterList = $model->orderBy('created_at', 'desc')->get();

        return $twitterList->toArray();
    }

}
