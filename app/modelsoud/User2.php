<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User2 extends Model
{

    protected $table = 'user2';
    public $timestamps = true;

    public function getPerson()
    {
        return $this->hasOne('App\Person', 'person_id', 'employee_id', 'partner_id');
    }

    public function getPartner()
    {
        return $this->hasOne('App\Partner', 'person_id');
    }

}