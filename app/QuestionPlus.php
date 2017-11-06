<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QuestionPlus extends Model
{

    protected $table = 'question_plus';
    public $timestamps = false;

    public function user_questions() {
        return $this->hasMany('App\UserQuestion', 'question_plus_id', 'id');
    }

}