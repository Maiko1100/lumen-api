<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserYear extends Model 
{

    protected $table = 'user_year';
    public $timestamps = false;

    public function getUserFiles()
    {
        return $this->hasMany('App\UserFile');
    }

    public function getEmployeeFiles()
    {
        return $this->hasMany('App\EmployeeFile');
    }

    public function getYears() {
        return $this->belongsTo('App\User','person_id','id');
    }

}