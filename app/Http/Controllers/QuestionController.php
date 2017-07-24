<?php 

namespace App\Http\Controllers;
use App\UserQuestion as UserQuestion;
use App\Question as Question;
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

    $Question = Question::find(1)->get()->first();
    $category = $Question->getCategory()->first();
//    $bindings = User::find(1)->getPartner()->getBindings();


//
//        foreach ($useryears as $useryear){
//
//            $useryear->getUserFiles()->get();
//
//            }


      return new JsonResponse([
                                'bindings' => $category
                        ]);

  }


  
}

?>