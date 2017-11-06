<?php

namespace App\Http\Controllers;

use App\Partner;
use Illuminate\Http\Response;
use App\Partner as Partner;
use App\Person as Person;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class PartnerController extends Controller
{

    public function savePartner(Request $request)
    {

        $user = JWTAuth::parseToken()->authenticate();
        $field = $request->input('field');
        $answer = $request->input('answer');
        $id = $request->input('personId');

        if (empty($id)) {
            $person = new Person();
            $person->$field = $answer;
            $person->save();

            $partner = new Partner();
            $partner->person_id = $person->id;
            $partner->user_id = $user->person_id;
            $partner->save();

            return response($partner->person_id);
        } else {
            Person::where("id", "=", $id)
                ->update(
                    [
                        $field => $answer
                    ]
                );
            return response($id);
        }
    }


}

?>