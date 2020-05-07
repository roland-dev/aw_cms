<?php
namespace Matrix\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class DynamicAdTerminal extends BaseModel
{
  use SoftDeletes;
  protected $datas = ['deleted_at'];
  protected $fillable = [
    'dynamic_ad_id',
    'terminal_code'
  ];

  public function createDynamicAdTerminal(int $dynamicAdId, array $terminalCodes)
  {
    $dynamicAdTerminalsData = [];
    foreach ($terminalCodes as $terminalCode) {
      $dynamicAdTerminal = [
        'dynamic_ad_id' => $dynamicAdId,
        'terminal_code' => $terminalCode
      ];
      $dynamicAdTerminal = self::create($dynamicAdTerminal);
      array_push($dynamicAdTerminalsData, $dynamicAdTerminal->toArray());
    }

    return empty($dynamicAdTerminalsData) ? [] : $dynamicAdTerminalsData;
  }
}
