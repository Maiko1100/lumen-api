<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserYear extends Model 
{

    protected $table = 'user_year';

    public function getUserFiles()
    {
        return $this->hasMany('App\UserFile');
    }

    public function getEmployeeFiles()
    {
        return $this->hasMany('App\EmployeeFile');
    }

}