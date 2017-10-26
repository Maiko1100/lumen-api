<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProfileQuestion extends Model
{
    protected $table = 'profile_question';

    public function questions()
    {
        return $this->morphMany('App\Question', 'questionable');
    }
}