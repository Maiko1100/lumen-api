<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Child extends Model 
{

    protected $table = 'child';
    public $timestamps = false;

    public function getInfo()
    {
        return $this->hasOne('App\Person','id','person_id');
    }
}
