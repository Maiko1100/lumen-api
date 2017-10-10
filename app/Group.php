<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{

    protected $table = 'group';
    public $timestamps = false;

    public function getQuestions()
    {
        return $this->hasmany('App\Question', 'group_id', 'id');
    }

}