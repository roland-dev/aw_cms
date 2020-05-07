<?php

namespace Matrix\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class KgsRoom extends Eloquent
{
    protected $connection = 'mongodb';
    protected $collection = 'rooms';
}
