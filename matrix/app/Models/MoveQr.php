<?php

namespace Matrix\Models;

class MoveQr extends BaseModel
{
    //
    protected $fillable = [
        'code', 'move_qr_group_code', 'title', 'filename', 'remark', 'sort',
    ];
}
