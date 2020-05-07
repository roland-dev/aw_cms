<?php
namespace Matrix\Models;

class StockReportCategory extends BaseModel
{
  const GENZONGBAOGAO = 2;

  protected $fillable = ['category_name', 'short_title_active', 'sort_no', 'visible'];
}