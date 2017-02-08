<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class house extends Model
{
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'year',
        'author',
        'price',
    ];

    public function images()
    {
        return $this->hasMany('App\HouseImage', 'houseId');
    }
}
