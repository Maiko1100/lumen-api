<?php 

namespace App\Http\Controllers;
use App\UserQuestion as UserQuestion;
use App\User as User;
use App\Question as Question;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\CategoryController;
use App\Category as Category;
use phpDocumentor\Reflection\Types\Null_;

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

        $q = array();

        foreach ($questions as $question) {
            if(empty($question->parent)) {

                $this->getChildren($question);
                array_push($q, $question);
            }
        }

        $category['questions'] = $q;
        array_push($questionaire,$category);
    }

      return new JsonResponse($questionaire);

  }

    function getChildren($question) {

        if($question->answer_option == 1){
            $question['answer_options'] = $question->getOptions()->get();
        }

        if ($question->has_childs) {

            $children = [];

            forEach($question->getChilds()->get() as $child) {
                array_push($children, $child);
                $this->getChildren($child);

            }

            $question['children'] = $children;

        } else {
            return $question;
        }
    }

  
}

?>