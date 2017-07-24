<?php 

namespace App\Http\Controllers;
use App\UserQuestion as UserQuestion;
use App\User as User;
use App\Question as Question;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\CategoryController;
use App\Category as Category;

class QuestionController extends Controller 
{

  /**
   * Display a listing of the resource.
   *
   * @return Response
   */
  public function getQuestions()
  {
    $categoryController = new CategoryController();
    $categories = $categoryController->getCategories();

    $questionaire = [];


    foreach ($categories as $category){


        $questions = $category->getQuestions()->get();

        foreach ($questions as $question){

            $q = [];

            if($question->answer_option == 1){

                $question_options = $question->getOptions()->get();

                array_push($q, $question_options);

                $question['answer_options'] = $q;

            }

            $category['questions'] = $questions;

        }



        array_push($questionaire,$category);
    }







      return new JsonResponse($questionaire);

  }


  
}

?>