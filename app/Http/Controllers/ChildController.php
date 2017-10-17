<?php

namespace App\Http\Controllers;

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

    public function saveChild() {

        $user = JWTAuth::parseToken()->authenticate();

        $field = $request->input('field');
        $answer = $request->input('answer');
        $id = $request->input('person_id');

        if(){


        }
    }

}

?>