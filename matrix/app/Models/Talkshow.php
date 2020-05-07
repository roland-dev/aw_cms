<?php

namespace Matrix\Models;

class Talkshow extends BaseModel
{
    //
    const STATUS_NEW = 10; // 没有预告
    const STATUS_PREPARE = 20; // 蓝字预告
    const STATUS_GOING = 30; // 即将开始
    const STATUS_PLAY = 40; // 正在播出
    const STATUS_DONE = 50; // 直播结束
    const STATUS_REPLAY = 60; // 看回放

    const SYNC_TO_DYNAMIC_AD_TYPE = 'talkshow';
    const NOTICE_SYNC_TO_DYNAMIC_AD_TYPE = 'talkshow_notice';
    const DYNAMIC_AD_TERMINAL_TYPES = ['pc'];

    protected $fillable = ['code', 'status', 'video_vendor_code', 'title', 'teacher_id', 'start_time', 'end_time', 'banner_url', 'type', 'live_room_code', 'boardcast_content', 'play_url', 'last_modify_user_id', 'description'];
}
