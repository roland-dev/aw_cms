<?php

namespace Matrix\Models;

use Jenssegers\Mongodb\Eloquent\SoftDeletes;

class AdLocationTerminal extends BaseModel
{
  use SoftDeletes;
  protected $datas = ['deleted_at'];
  protected $fillable = [
    'location_code',
    'terminal_code'
  ];
}