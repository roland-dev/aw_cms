<?php

namespace Matrix\Models;

class Discuss extends BaseModel
{
    //
    const STATUS_NEW = 10;
    const STATUS_APPROVED = 20;
    const STATUS_REJECTED = 30;

    protected $fillable = ['live_room_code', 'talkshow_code', 'open_id', 'customer_name', 'icon_url', 'content', 'reply_to_open_id', 'reply_to_name', 'status', 'examine_user_id', 'examine_at'];
}
