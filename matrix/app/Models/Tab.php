<?php

namespace Matrix\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Tab extends BaseModel
{
  use SoftDeletes;
  protected $dates = ['deleted_at'];

  protected $fillable = ['code', 'name', 'sort', 'active'];
}