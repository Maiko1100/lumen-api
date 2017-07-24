<?php

namespace App\Http\Controllers;

use App\Partner;
use App\Question;
use Laravel\Lumen\Routing\Controller as BaseController;

use Illuminate\Http\JsonResponse;

class Controller extends BaseController
{
    public function test() {

        $question =  Question::get();

        return new JsonResponse(['partner' => $question]);
    }
}
