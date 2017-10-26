<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GeneralQuestion extends Model
{
    protected $table = 'general_question';

    public function questions()
    {
        return $this->morphMany('App\Question', 'questionable');
    }
}