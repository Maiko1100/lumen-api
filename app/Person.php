<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Person extends Model 
{

    protected $table = 'person';
    public $timestamps = false;

    public function getPersonalAnswer()
    {
        return $this->belongsTo('App\UserData');
    }

    public function getChild()
    {
        return $this->hasMany('App\Person');
    }

}