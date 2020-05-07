<?php

namespace Matrix\Models;

use DB;

class PrivateMessage extends BaseModel
{
    //
    const DIRECTION_UP = 0;
    const DIRECTION_DOWN = 1;

    protected $fillable = ['direction', 'teacher_id', 'open_id', 'content', 'read'];

    public function getPrivateMessageList(array $condition)
    {
        $privateMessageList = self::where($condition)->get();
        return empty($privateMessageList) ? [] : $privateMessageList->toArray();
    }

    public function getOpenIdListByTeacherId(int $teacherId)
    {
        $openIdList = self::select('open_id', DB::raw('max(created_at) as time'))->where('teacher_id', $teacherId)->groupBy('open_id')->orderBy('time', 'desc')->pluck('open_id');

        return empty($openIdList) ? [] : $openIdList->toArray();
    }

    public function readPrivateMessage(int $privateMessageId, int $direction, $userKey)
    {
        $model = self::where('id', $privateMessageId)->where('direction', $direction);
        switch ($direction) {
            case self::DIRECTION_UP:
                $model = $model->whereIn('teacher_id', $userKey);
                break;
            case self::DIRECTION_DOWN:
                $model = $model->where('open_id', $userKey);
                break;
            default:
                abort(403);
        }
        $privateMessage = $model->take(1)->firstOrFail();
        $privateMessage->read = 1;
        $privateMessage->save();

        return $privateMessage->toArray();
    }
}
