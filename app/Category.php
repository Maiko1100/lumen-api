<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{

    protected $table = 'category';
    public $timestamps = false;

    public function getGroups()
    {
        return $this->hasmany('App\Group', 'category_id', 'id');
    }

}