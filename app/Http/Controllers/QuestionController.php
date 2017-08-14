<?php 

namespace App\Http\Controllers;
use App\UserQuestion as UserQuestion;
use App\User as User;
use App\Question as Question;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\CategoryController;
use App\Category as Category;
use phpDocumentor\Reflection\Types\Null_;
use Illuminate\Http\Request;

class QuestionController extends Controller 
{

  /**
   * Display a listing of the resource.
   *
   * @return Response
   */
  public function getQuestions(Request $request)
  {
      $year = $request->input('year');
    $categoryController = new CategoryController();
    $categories = $categoryController->getCategoriesByYear($year);

    $questionaire = [];

    foreach ($categories as $category){

        $questions = $category->getQuestions()->get();

        $q = array();

        foreach ($questions as $question) {
            if(empty($question->parent)) {

                $this->getChildren($question);

                unset($question['answer_option']);
                unset($question['year_id']);
                unset($question['parent']);
                unset($question['is_static']);
                unset($question['has_childs']);

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
            $question['answer_options'] = $question->getOptions()->pluck('text')->toArray();
        } else {
            $question['answer_options'] = null;
        }

        if ($question->has_childs) {

            $children = [];

            forEach($question->getChilds()->get() as $child) {
                array_push($children, $child);
                $this->getChildren($child);

            }

            $question['children'] = $children;

        } else {
            unset($question['answer_option']);
            unset($question['year_id']);
            unset($question['parent']);
            unset($question['is_static']);
            unset($question['has_childs']);

            $question['children'] = null;

            return $question;
        }
    }

  
}

?>