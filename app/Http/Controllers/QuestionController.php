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
use Psy\Util\Json;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\UserYear;
use App\Child;
use Intervention\Image\Facades\Image as Image;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
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
                    ->select('question.id', 'question.text', 'question.group_id', 'question.condition', 'question.type', 'question.answer_option', 'question.parent', 'question.has_childs', 'question.question_genre_id', 'user_question.question_answer as answer', DB::raw("group_concat(`user_file`.`name` SEPARATOR '|;|') as `file_names`"), 'user_question.approved', 'feedback.text as feedback')
                    ->orderBy('question.id', 'asc')
                    ->get();

//                    echo json_encode($questions);
//                    exit;

                $q = array();

                foreach ($questions as $question) {
                    if (strpos($question->file_names, '|;|') !== false) {
                        $question->file_names = explode('|;|', $question->file_names);
                    }
                    if ($question->file_names === null) {
                        $question->file_names = [];
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
//            "partner" => $partner,
//            "kids" => $kids
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
                    ->select('question.id', 'question.text', 'question.group_id', 'question.condition', 'question.type', 'question.answer_option', 'question.parent', 'question.has_childs', 'question.question_genre_id', DB::raw("group_concat(`user_question`.`question_plus_id` SEPARATOR '|;|') as `qpids`"), DB::raw("group_concat(`user_question`.`question_answer` SEPARATOR '|;|') as `answers`"))
                    ->orderBy('question.id', 'asc')
                    ->get();

                forEach ($childs as $child) {
                    if (strpos($child->qpids, '|;|') !== false) {
                        $child->qpids = array_map('intval', explode('|;|', $child->qpids));
                        $child->answers = explode('|;|', $child->answers);
                    }else if ($child->qpids === null) {
                        $child->qpids = [];
                        $child->answers = [];
                    } else {
                        $child->qpids = [$child->qpids];
                        $child->answers = [$child->answers];
                    }

                    for ($i = 0; $i < count($child->qpids); $i++) {
                        $answers[$child->qpids[$i]][$child->id] = $child->answers[$i];
                    }

                    unset($child['qpids']);
                    unset($child['answers']);

                    array_push($children, $child);
                    $this->getChildren($child, $userYear);

                    unset($question['answer_option']);
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
                    ->select('question.id', 'question.text', 'question.group_id', 'question.condition', 'question.type', 'question.answer_option', 'question.parent', 'question.has_childs', 'question.question_genre_id', 'user_question.question_answer as answer', DB::raw("group_concat(`user_file`.`name` SEPARATOR '|;|') as `file_names`"), 'user_question.approved', 'feedback.text as feedback')
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

    public function saveQuestion(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $year = $request->input('year');
        $questionId = $request->input('id');
        $answer = $request->input('answer');
        $qpid = $request->input('qpid');
        $userYear = UserYear::where("person_id", "=", $user->person_id)
            ->where("year_id", "=", $year)->first();

        $isProfile = Question::where("id", "=", $questionId)
            ->first()
            ->getGenre()
            ->first()
            ->isProfile == 1;

        if (isset($qpid)) {

            if (isset($isProfile)) {
                $existingQuestion = $this->checkPlusQuestion($questionId, $qpid, $userYear);
            } else {
                $existingQuestion = $this->checkPlusQuestion($questionId, $qpid);
            }

            if (isset($existingQuestion)) {
                $existingQuestion->question_answer = $answer;
                $existingQuestion->save();
            } else {
                $userQuestion = new UserQuestion();
                $userQuestion->person_id = $user->person_id;
                $userQuestion->user_year_id = $userYear->id;
                $userQuestion->question_id = $questionId;
                $userQuestion->question_answer = $answer;
                $userQuestion->question_plus_id = $qpid;

                $userQuestion->save();
            }
        } else {
            $existingQuestion = $this->checkQuestion($userYear, $questionId);
            if (isset($existingQuestion)) {
                $existingQuestion->question_answer = $answer;
                $existingQuestion->save();
            } else {
                $userQuestion = new UserQuestion();
                $userQuestion->person_id = $user->person_id;
                $userQuestion->user_year_id = $userYear->id;
                $userQuestion->question_id = $questionId;
                $userQuestion->question_answer = $answer;

                $userQuestion->save();
            }
        }

        return $year;

    }

    public function checkQuestion($userYear, $questionId)
    {
        return UserQuestion::where("user_year_id", "=", $userYear->id)->where("question_id", "=", $questionId)->first();
    }

    public function checkPlusQuestion ($questionId, $qpid, $userYear = NULL) {
        if (isset($userYear)) {
            return UserQuestion::where("question_id", "=", $questionId)
                ->where("question_plus_id", "=", $qpid)
                ->where("user_year_id", "=", $userYear->id)
                ->first();
        } else {
            return UserQuestion::where("question_id", "=", $questionId)
                ->where("question_plus_id", "=", $qpid)
                ->where("user_year_id", "IS", "NULL")
                ->first();
        }
    }

}

?>