<?php

namespace App\Http\Controllers;

use App\Partner;
use App\User2;
use Laravel\Lumen\Routing\Controller as BaseController;

use Illuminate\Http\JsonResponse;

class Controller extends BaseController
{
    public function test() {

        $partner =  Partner::where('person_id', 2)->first();
        $person = $partner->getPerson();

        return new JsonResponse(['partner' => $person]);
    }
}
