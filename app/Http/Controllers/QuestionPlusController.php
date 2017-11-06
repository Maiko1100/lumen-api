<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use App\QuestionPlus as QuestionPlus;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Request;

class QuestionPlusController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create(Request $request)
    {
        $questionPlus = new QuestionPlus();
        $questionPlus->person_id = JWTAuth::parseToken()->authenticate()->person_id;
        $questionPlus->question_id = $request->input('question_id');
        $questionPlus->save();

        return Response($questionPlus->id);
    }

    public function delete(Request $request)
    {
        $questionPlus = QuestionPlus::find($request->input('id'));
        $questionPlus->delete();

        return Response("true");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {

    }

}

?>