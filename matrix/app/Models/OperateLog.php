<?php

namespace Matrix\Models;

class OperateLog extends BaseModel
{
    //
    protected $fillable = ['operator_user_id', 'operate_code', 'operate_title', 'content_type', 'content_id', 'message', 'ip'];
}
