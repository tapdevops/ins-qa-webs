<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    //
    protected $connection = 'tapdw';
    protected $table = 'TM_EMPLOYEE_HRIS';
}
