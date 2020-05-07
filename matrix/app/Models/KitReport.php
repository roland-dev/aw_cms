<?php
namespace Matrix\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class KitReport extends BaseModel
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

  const VALID_STATUS = [
    [
      'status' => 0,
      'name' => '失效',
    ],
    [
      'status' => 1,
      'name' => '有效',
    ],
  ];

  const PUBLISH_SUCCESS = 1; // 已发布
  const KIT_REPORT_FEED_MSG_TYPE = 'news';
  const KIT_REPORT_VALID = 1; // 有效
  const KIT_REPORT_INVALID = 0; // 失效
  const KIT_REPORT_TWITTER_REF_TYPE = 'kit_report';

  const PUBLISHED_UPDATE_PERMISSION_CODE = 'kit_report_published_update';

  use SoftDeletes;
  protected $dates = ['deleted_at'];
  protected $fillable = ['report_id', 'title', 'kit_code', 'start_at', 'end_at', 'cover_url', 'summary', 'format', 'content', 'url', 'creator_user_id', 'last_modify_user_id'];

  public function getPublishStatus()
  {
    return self::PUBLISH_STATUS;
  }

  public function getValidStatus()
  {
    return self::VALID_STATUS;
  }
}