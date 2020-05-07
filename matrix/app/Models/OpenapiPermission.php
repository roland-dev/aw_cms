<?php

namespace Matrix\Models;

class OpenapiPermission extends BaseModel
{
    //
    protected $fillable = ['code', 'name', 'uri', 'group_code', 'group_name', 'remark', 'active'];
}
