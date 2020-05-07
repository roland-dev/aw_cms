<?php

namespace Matrix\Models;

class TjWxSendLogDetail extends BaseModel
{
    protected $connection = 'mysql_no_prefix';
    protected $table = 'tj_wx_send_log_detail';
    protected $primaryKey = 'detail_id';

    protected $fillable = [
        'detail_id',
        'msg_log_id',
        'send_time',
        'msg_type',
        'category',
        'from',
        'from_name',
        'title',
        'digest',
        'play_time',
        'file_id',
        'file_size',
        'thumb_cdn_url',
        'thumb_local_url',
        'content_url',
        'content_local_data',
        'content_local_url',
        'to_all',
        'show_in_app',
        'source_type',
        'original_content',
        'send_author_name',
        'demo_url',
        'ad_guide',
    ];

    public function removeRecord($detailId)
    {
        self::where('detail_id', $detailId)->delete();
    }
}
