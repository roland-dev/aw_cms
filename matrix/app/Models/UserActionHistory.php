<?php

namespace Matrix\Models;

class UserActionHistory extends BaseModel
{
    protected $connection = 'mysql_no_prefix';
    protected $table = 'user_action_history';

    protected $fillable = [
        'actor',
        'actor_type',
        'act_key',
        'act_id',
        'act_time',
    ];

    public function getPvList($videoKey)
    {
        $pvGetVideoList = self::where('act_key', 'get_video')->where('act_id', $videoKey)->get(); 
        $pvPlayVideoList = self::where('act_key', 'play_video')->where('act_id', $videoKey)->get();
        $pvFinishVideoList = self::where('act_key', 'finish_video')->where('act_id', $videoKey)->get();
        $getVideoList = empty($pvGetVideoList) ? [] : $pvGetVideoList->toArray();
        $playVideoList = empty($pvPlayVideoList) ? [] : $pvPlayVideoList->toArray();
        $finishVideoList = empty($pvFinishVideoList) ? [] : $pvFinishVideoList->toArray();

        $ret = [
            'get_video' => $getVideoList,
            'play_video' => $playVideoList,
            'finish_video' => $finishVideoList, 
        ];

        return $ret;
    }
}
