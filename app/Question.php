<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{

    protected $table = 'question';
    public $timestamps = false;

    public function getYear()
    {
        return $this->belongsTo('App\Year', 'year_id', 'id');
    }

    public function getOptions()
    {
        return $this->belongsToMany('App\QuestionOption', 'question_has_question_option', 'question_id', 'question_option_id');
    }

    public function getChilds()
    {
        return $this->hasMany('App\Question', 'parent', 'id');
    }

    public function getCategory()
    {
        return $this->hasOne('App\Category', 'id', 'category');
    }

    public function getGenre()
    {
        return $this->hasOne('App\QuestionGenre', 'id', 'question_genre_id');
    }

    public function question_pluses(){
        return $this->hasMany('App\QuestionPlus', 'question_id', 'id');
    }

    public function question_plus_user_questions() {
        return $this->hasManyThrough('App\UserQuestion', 'App\QuestionPlus', 'question_id', 'question_plus_id');
    }

    public function questionable()
    {
        return $this->morphTo();
    }

}