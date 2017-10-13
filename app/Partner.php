<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Partner extends Model 
{

    protected $table = 'partner';
    public $timestamps = false;

    public function getPerson()
    {
        return $this->hasOne('App\Person', 'person_id');
    }

    public function getInfo()
    {
        return $this->hasOne('App\Person','id','person_id');
    }

}