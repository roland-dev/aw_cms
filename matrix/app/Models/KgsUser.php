<?php

namespace Matrix\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class KgsUser extends Eloquent
{
    protected $connection = 'mongodb';
    protected $collection = 'users';
}
