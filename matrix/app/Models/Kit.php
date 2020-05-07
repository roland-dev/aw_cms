<?php
namespace Matrix\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Kit extends BaseModel
{
  const BUY_TYPES = [
    [
      'status' => 1,
      'name' => 'APP内购'
    ],
    [
      'status' => 2,
      'name' => 'APP外购'
    ]
  ];

  const BUY_STATE = [
    [
      'status' => 0,
      'name' => '不可购买',
    ],
    [
      'status' => 1,
      'name' => '可购买',
    ]
  ];

  const GENERATE_CODE_PREFIX = 'jn_';
  const BUY_TYPER_APP_CAN_BUY = 1; // APP 内购
  const BUY_TYPER_APP_NOT_CAN_BUY = 2;  // APP 外购
  const BUY_STATE_CAN_BUY = 1; // 可购买
  const KIT_TAB_CODE = 'kits'; // 牛人老师主页 锦囊tab code

  use SoftDeletes;
  protected $dates = ['deleted_at'];
  protected $fillable = ['code', 'name', 'cover_url', 'descript', 'belong_user_id', 'buy_type', 'buy_state', 'service_key', 'sort_num', 'creator_user_id', 'last_modify_user_id'];

  public function getBuyTypes()
  {
    return self::BUY_TYPES;
  }

  public function getBuyStates()
  {
    return self::BUY_STATE;
  }
}