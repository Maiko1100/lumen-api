<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserQuestion extends Model 
{

    protected $table = 'user_question';
    public $timestamps = false;

    public function getFeedback()
    {
        return $this->hasMany('App\Feedback');
    }

}