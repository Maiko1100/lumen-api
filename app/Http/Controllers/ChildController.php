<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use App\Child as Child;
use App\Person as Person;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class ChildController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {

    }

    public function saveChild(Request $request) {

        $user = JWTAuth::parseToken()->authenticate();

        $field = $request->input('field');
        $answer = $request->input('answer');
        $id = $request->input('personId');

        if(empty($id)){
            $person = new Person();
            $person->$field = $answer;
            $person->save();

            $child = new Child();
            $child->person_id = $person->id;
            $child->user_id = $user->person_id;
            $child->save();

            return response($child->person_id);
        }else{
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