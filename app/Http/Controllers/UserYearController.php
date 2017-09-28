<?php 

namespace App\Http\Controllers;

use App\UserYear;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserYearController extends Controller
{

    public function getUserYears() {
        $user = JWTAuth::parseToken()->authenticate();
        $userYears = UserYear::where("person_id", "=", $user->person_id)->get();

        $userYearsArray = [];

        foreach ($userYears as $userYear) {
            $userYearsArray[$userYear->year_id] = $userYear->status;
        }

        return new JsonResponse($userYearsArray);
    }

    public function getUserYear(Request $request) {
        $user = JWTAuth::parseToken()->authenticate();
        $userYear = UserYear::where("person_id", "=", $user->person_id)
            ->where("year_id", "=", $request->input('year'))
            ->get();

        $userYearArray = $userYear->toArray();

        $output = NULL;
        if(array_key_exists(0, $userYearArray)) {
            $output = $userYearArray[0];
        }

        return new JsonResponse($output);
    }

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
      $user = JWTAuth::parseToken()->authenticate();

      $userYear = new UserYear();
      $userYear->person_id = $user->person_id;
      $userYear->year_id = $request->input('year');
      $userYear->package = $request->input('package');
      $userYear->status = 0;

      return new Response(var_export($userYear->save()));
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