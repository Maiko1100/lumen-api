<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Book extends Model

{

    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'author',
        'year'
    ];
}
