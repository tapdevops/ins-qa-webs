<?php

namespace App;
use Jenssegers\Mongodb\Eloquent\Model;

class Mongo_TM_ROAD extends Model
{
    protected $connection = 'mongodb_hectarstatment';
    protected $collection = 'TM_ROAD';
    protected $primaryKey = 'id';
}
