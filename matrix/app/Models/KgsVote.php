<?php

namespace Matrix\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class KgsVote extends Eloquent
{
    protected $connection = 'mongodb';
    protected $collection = 'votes';
}
