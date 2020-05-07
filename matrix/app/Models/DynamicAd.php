<?php
namespace Matrix\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class DynamicAd extends BaseModel
{
  const SOURCE_TYPES = [
    [
      'code' => 'added',
      'name' => '手动添加'
    ],
    [
      'code' => 'feed',
      'name' => '内容精选'
    ],
    [
      'code' => 'talkshow_notice',
      'name' => '节目预告'
    ],
    [
      'code' => 'talkshow',
      'name' => '节目信息'
    ]
  ];
  const URI_DYNAMIC_AD_ACCESS = '/api/v2/propaganda/dynamic/ad/{dynamicAdId}';
  const ACTIVE_DEFAULT = 1;
  const SIGN_DEFAULT = 0;

  use SoftDeletes;
  protected $dates = ['deleted_at'];
  protected $fillable = [
    'title',
    'content_url',
    'jump_type',
    'jump_params',
    'start_at',
    'end_at',
    'active',
    'sign',
    'source_type',
    'source_id',
    'last_modify_user_id'
  ];

  public function getSourceTypes()
  {
    return self::SOURCE_TYPES;
  }
}
