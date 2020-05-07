<?php

namespace Matrix\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class KgsHistory extends Eloquent
{
    protected $connection = 'mongodb';
    protected $collection = 'histories';
}
