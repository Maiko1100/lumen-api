<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\UserYear;
use Illuminate\Support\Facades\DB;

class QuestionController extends Controller
{

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

            $groups = $category
                ->getGroups()
                ->get();

            $g = array();

            foreach ($groups as $group) {

                $questions = $group->getQuestions()
                    ->leftjoin('user_question', function($join) use ($userYear) {
                        $join->on('question.id', '=', 'user_question.question_id');
                        $join->on('user_question.user_year_id', "=", DB::raw($userYear->id));
                    })
                    ->leftjoin('feedback', 'user_question.id', 'feedback.user_question_id')
                    ->leftjoin('user_file', function($join) use ($userYear) {
                        $join->on('question.id', '=', 'user_file.question_id');
                        $join->on('user_file.user_year_id', "=", DB::raw($userYear->id));
                    })
                    ->groupBy('question.id')
                    ->select('question.id', 'question.text', 'question.group_id', 'question.condition', 'question.type', 'question.answer_option', 'question.parent', 'question.has_childs', 'question.question_genre_id', 'user_question.question_answer as answer', DB::raw("group_concat(`user_file`.`name` SEPARATOR '|;|') as `file_names`"), 'user_question.has_error', 'user_question.approved', 'feedback.text as feedback')
                    ->orderBy('question.id', 'asc')
                    ->get();

                $q = array();

                foreach ($questions as $question) {
                    if (strpos($question->file_names, '|;|') !== false) {
                        $question->file_names = explode('|;|', $question->file_names);
                    } else if ($question->file_names === null) {
                        $question->file_names = [];
                    } else {
                        $question->file_names = [$question->file_names];
                    }

                    if (empty($question->parent)) {
                        $this->getChildren($question, $userYear);

                        array_push($q, $question);
                    }
                }

                unset($group->category_id);
                $group['questions'] = $q;
                array_push($g, $group);
            }

            array_push(
                $questionaire, array(
                    'id' => $category->id,
                    'name' => $category->name,
                    'year_id' => $category->year_id,
                    'question_id' => $category->question_id,
                    'condition' => $category->condition,
                    'groups' => $g
                )
            );
        }

        return new Response(array(
            "categories" => $questionaire
        ));

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

            if ($question->type === 8) {
                $answers = [];
                $childs = $question->getChilds()
                    ->leftjoin('user_question', 'question.id', 'user_question.question_id')
                    ->groupBy('question.id')
                    ->select('question.id', 'question.text', 'question.group_id', 'question.condition', 'question.type', 'question.answer_option', 'question.parent', 'question.has_childs', 'question.question_genre_id', DB::raw("group_concat(`user_question`.`question_plus_id` SEPARATOR '|;|') as `qpids`"), DB::raw("group_concat(`user_question`.`question_answer` SEPARATOR '|;|') as `answers`"), DB::raw("group_concat(`user_question`.`has_error` SEPARATOR '|;|') as `has_errors`"), DB::raw("group_concat(`user_question`.`approved` SEPARATOR '|;|') as `approveds`"))
                    ->orderBy('question.id', 'asc')
                    ->get();

                forEach ($childs as $child) {
                    if (strpos($child->qpids, '|;|') !== false) {
                        $child->qpids = array_map('intval', explode('|;|', $child->qpids));
                        $child->answers = explode('|;|', $child->answers);
                        $child->has_errors = array_map('intval', explode('|;|', $child->has_errors));
                        $child->approveds = array_map('intval', explode('|;|', $child->approveds));
                    }else if ($child->qpids === null) {
                        $child->qpids = [];
                        $child->answers = [];
                        $child->has_errors = [];
                        $child->approveds = [];
                    } else {
                        $child->qpids = [$child->qpids];
                        $child->answers = [$child->answers];
                        $child->has_errors = [$child->has_errors];
                        $child->approveds = [$child->approveds];
                    }

                    for ($i = 0; $i < count($child->qpids); $i++) {
                        $answers[$child->qpids[$i]][$child->id] = array(
                            "answer" => $child->answers[$i],
                            "has_error" => $child->has_errors[$i],
                            "approved" => $child->approveds[$i]
                        );
                    }

                    unset($child['qpids']);
                    unset($child['answers']);

                    array_push($children, $child);
                    $this->getChildren($child, $userYear);

                    unset($question['has_error']);
                    unset($question['approved']);
                    unset($question['feedback']);
                    unset($question['answer_option']);
                    unset($question['answer_options']);
                    unset($question['parent']);
                    unset($question['has_childs']);
                }
                $question['answers'] = $answers;
            } else {
                $childs = $question->getChilds()
                    ->leftjoin('user_question', function($join) use ($userYear) {
                        $join->on('question.id', '=', 'user_question.question_id');
                        $join->on('user_question.user_year_id', "=", DB::raw($userYear->id));
                    })
                    ->leftjoin('feedback', 'user_question.id', 'feedback.user_question_id')
                    ->leftjoin('user_file', function($join) use ($userYear) {
                        $join->on('question.id', '=', 'user_file.question_id');
                        $join->on('user_file.user_year_id', "=", DB::raw($userYear->id));
                    })
                    ->groupBy('question.id')
                    ->select('question.id', 'question.text', 'question.group_id', 'question.condition', 'question.type', 'question.answer_option', 'question.parent', 'question.has_childs', 'question.question_genre_id', 'user_question.question_answer as answer', DB::raw("group_concat(`user_file`.`name` SEPARATOR '|;|') as `file_names`"), 'user_question.has_error', 'user_question.approved', 'feedback.text as feedback')
                    ->orderBy('question.id', 'asc')
                    ->get();

                forEach ($childs as $child) {
                    if (strpos($child->file_names, '|;|') !== false) {
                        $child->file_names = explode('|;|', $question->file_names);
                    }
                    if ($child->file_names === null) {
                        $child->file_names = [];
                    }

                    array_push($children, $child);
                    $this->getChildren($child, $userYear);

                    unset($question['answer_option']);
                    unset($question['parent']);
                    unset($question['has_childs']);
                }
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
}

?>