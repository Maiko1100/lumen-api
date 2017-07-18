<?php 

namespace App\Http\Controllers;
use App\Question as Question;
use App\User as User;
use App\UserData as UserData;
use Illuminate\Http\JsonResponse;
class QuestionController extends Controller 
{

  /**
   * Display a listing of the resource.
   *
   * @return Response
   */
  public function index()
  {

    $useryears = UserData::find(1)->getChilds()->toSql();

//    $bindings = User::find(1)->getPartner()->getBindings();


//
//        foreach ($useryears as $useryear){
//
//            $useryear->getUserFiles()->get();
//
//            }


      return new JsonResponse([
                                'bindings' => $useryears
                        ]);

  }


  
}

?>