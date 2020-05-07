<?php
namespace Matrix\Models;

use Illuminate\Support\Facades\DB;
use Jenssegers\Mongodb\Eloquent\SoftDeletes;

class StockReport extends BaseModel
{
  
  const PUBLISH_STATUS = [
    [
      'status' => 0,
      'name' => '未发布'
    ],
    [
      'status' => 1,
      'name' => '已发布',
    ],
  ];
  
  const PUBLISH_SUCCESS = 1; // 已发布
  const STOCK_REPORT_CATEGORY_KEY = 'cyzb';
  const STOCK_REPORT_FEED_MSG_TYPE = 'news';
  const STOCK_REPORT_ACCESS_LEVEL = 'cyzb';
  const STOCK_REPORT_TWITTER_REF_TYPE = 'stock_report'; // 转发到解盘时 ref_type 值

  const PUBLISHED_UPDATE_PERMISSION_CODE = 'stock_report_published_update';

  use SoftDeletes;
  protected $dates = ['deleted_at'];
  protected $fillable = ['report_id', 'category_id', 'stock_code', 'stock_name', 'report_format', 'report_short_title', 'report_title', 'report_content', 'report_url', 'report_date', 'author_teacher_id', 'creator', 'last_modify_user_id', 'report_summary'];

  public function getPublishStatus()
  {
    return self::PUBLISH_STATUS;
  }
}