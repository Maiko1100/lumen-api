<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HouseImage extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'url', 'sort'
    ];

    /**
     * Get the ibike related to the image
     */
    public function images()
    {
        return $this->hasOne('App\House', 'houseId', 'houseImageId');
    }
}
