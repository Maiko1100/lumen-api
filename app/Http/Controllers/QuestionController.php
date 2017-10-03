<?php

namespace App\Http\Controllers;

use App\UserQuestion as UserQuestion;
use App\User as User;
use App\Question as Question;
use App\UserFile as UserFile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use App\Http\Controllers\CategoryController;
use App\Category as Category;
use phpDocumentor\Reflection\Types\Null_;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\UserYear;
use Intervention\Image\Facades\Image as Image;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class QuestionController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function getQuestions(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $year = $request->input('year');
        $userYear = UserYear::where("person_id", "=", $user->person_id)
            ->where("year_id", "=", $year)
            ->first();

        $categoryController = new CategoryController();
        $categories = $categoryController->getCategoriesByYear($year);

        $questionaire = [];

        foreach ($categories as $category) {

            $questions = $category->getQuestions()
                ->leftjoin('user_question', 'question.id', 'user_question.question_id')
                ->leftjoin('feedback', 'user_question.id', 'feedback.user_question_id')
                ->leftjoin('user_file', 'question.id', 'user_file.question_id')
                ->where('user_question.user_year_id', "=", $userYear->id)
                ->orWhere('user_file.user_year_id', "=", $userYear->id)
                ->groupBy('question.id')
                ->select('question.id', 'question.text', 'question.category', 'question.condition', 'question.type', 'question.answer_option', 'question.parent', 'question.has_childs', 'user_question.question_answer as answer', DB::raw("group_concat(`user_file`.`name` SEPARATOR '|;|') as `file_names`"), 'user_question.approved', 'feedback.text as feedback')
                ->orderBy('question.id', 'asc')
                ->get();

            $q = array();

            foreach ($questions as $question) {
                if (strpos($question->file_names, '|;|') !== false) {
                    $question->file_names = explode('|;|', $question->file_names);
                }
                if (empty($question->parent)) {

                    $this->getChildren($question, $userYear);

                    array_push($q, $question);
                }
            }

            $category['questions'] = $q;
            array_push($questionaire, $category);
        }

        return new Response($questionaire);

    }

    function getChildren($question, $userYear)
    {
        if ($question->answer_option == 1) {
            $question['answer_options'] = $question->getOptions()->pluck('text')->toArray();
        } else {
            $question['answer_options'] = null;
        }

        if ($question->has_childs) {

            $children = [];

            $childs = $question->getChilds()
                ->leftjoin('user_question', 'question.id', 'user_question.question_id')
                ->leftjoin('feedback', 'user_question.id', 'feedback.user_question_id')
                ->leftjoin('user_file', 'question.id', 'user_file.question_id')
                ->where('user_question.user_year_id', "=", $userYear->id)
                ->orWhere('user_file.user_year_id', "=", $userYear->id)
                ->groupBy('question.id')
                ->select('question.id', 'question.text', 'question.category', 'question.condition', 'question.type', 'question.answer_option', 'question.parent', 'question.has_childs', 'user_question.question_answer as answer', DB::raw("group_concat(`user_file`.`name` SEPARATOR '|;|') as `file_names`"), 'user_question.approved', 'feedback.text as feedback')
                ->orderBy('question.id', 'asc')
                ->get();

            forEach ($childs as $child) {
                if (strpos($child->file_names, '|;|') !== false) {
                    $child->file_names = explode('|;|', $question->file_names);
                }
                array_push($children, $child);
                $this->getChildren($child, $userYear);

                unset($question['answer_option']);
                unset($question['parent']);
                unset($question['has_childs']);
            }

            $question['children'] = $children;

        } else {
            unset($question['answer_option']);
            unset($question['parent']);
            unset($question['has_childs']);

            $question['children'] = null;

            return $question;
        }
    }

    public function saveFileQuestion(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $year = $request->input('year');
        $questionId = $request->input('id');
        $userYear = UserYear::where("person_id", "=", $user->person_id)
            ->where("year_id", "=", $year)->first();

        $file = $request->file('file');

        Storage::putFileAs('/' . $user->person_id, $file, $file->getClientOriginalName());
        exit;

//        $userFile = new UserFile();
//        $userFile->user_year_id = $userYear->id;
//        $userFile->question_id = $questionId;
//        $userFile->name = $file->getClientOriginalName();
//        $userFile->type = 0;
//
//        $userFile->save();

        return $year;

    }


    public function saveQuestion(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $year = $request->input('year');
        $questionId = $request->input('id');
        $answer = $request->input('answer');
        $userYear = UserYear::where("person_id", "=", $user->person_id)
            ->where("year_id", "=", $year)->first();

        $existingQuestion = $this->checkQuestion($userYear, $questionId);

        if (isset($existingQuestion)) {
            $existingQuestion->question_answer = $answer;
            $existingQuestion->save();
        } else {
            $userQuestion = new UserQuestion();
            $userQuestion->user_year_id = $userYear->id;
            $userQuestion->question_id = $questionId;
            $userQuestion->question_answer = $answer;

            $userQuestion->save();
        }

        return $year;

    }

    public function checkQuestion($userYear, $questionId)
    {
        return UserQuestion::where("user_year_id", "=", $userYear->id)->where("question_id", "=", $questionId)->first();
    }

}

?>