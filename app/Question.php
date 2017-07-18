<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Question extends Model 
{

    protected $table = 'question';
    public $timestamps = false;

    public function getYear()
    {
        return $this->belongsTo('App\Year','year_id','id');
    }

    public function getOptions()
    {
        return $this->belongsToMany('App\QuestionOption','question_has_question_option','question_id','question_option_id');
    }

    public function getParent()
    {
        return $this->hasOne('App\Question','id','parent');
    }


}