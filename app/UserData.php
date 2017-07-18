<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserData extends Model 
{

    protected $table = 'user_data';
    public $timestamps = false;

    protected $primaryKey = 'person_id'; // or null

    public $incrementing = false;

    public function getChilds()
    {
        return $this->hasOne('App\Child','person_id','question_id');
    }

}