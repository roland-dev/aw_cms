<?php
namespace Matrix\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class ColumnGroup extends BaseModel
{
  use SoftDeletes;
  protected $dates = ['deteled_at'];
  protected $fillable = ['code', 'name', 'descript'];
}