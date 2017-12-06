<?php

namespace App\Http\Controllers;

use App\Question;
use App\Group;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\UserYear;
use Illuminate\Support\Facades\DB;
use stdClass;
use Illuminate\Http\JsonResponse;

class QuestionController extends Controller
{
    public function getQuestionsByGroup (Request $request = null, $year = null)
    {
        $out = array();
        if ($year != null) {

        } else {
            $year = $request->input('year');
        }

        $groups = Group::join('category', 'group.category_id', 'category.id')
            ->where('category.year_id', '=', $year)
            ->select('group.*')
            ->get();

        foreach ($groups as $group) {
            $q = array();

            $questions = $group->getQuestions()->orderBy('sort', 'asc')->get();
            foreach ($questions as $question) {
                unset($question->answer_option);
                unset($question->parent);
                unset($question->group_id);
                unset($question->condition);
                unset($question->type);
                unset($question->validation_type);
                unset($question->has_childs);
                unset($question->tip_text);

                array_push($q, $question);
            }

            array_push($out, $questions);
        }
        return new JsonResponse($out);
    }

    public function setQuestionsGroupSort(Request $request){
        $year = $request->input('year');

        $questionGroups = $request->input('questions');

        foreach ($questionGroups as $questionGroup){

            foreach ($questionGroup as $index => $question){
                Question::where('id',"=",$question['id'])->update(
                    [
                        'sort' => $index
                    ]);
            }
        }
        return self::getQuestionsByGroup(null,2017);
    }

    public function getQuestions(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $userYearEmpty = empty($request->input('user_year'));

        if ($userYearEmpty) {
            $year = $request->input('year');

            $userYear = UserYear::where("person_id", "=", $user->person_id)
                ->where("year_id", "=", $year)
                ->first();
        } else {
            if ($user->role == 2 || $user->role == 3) {
                $userYear = UserYear::where("id", "=", $request->input('user_year'))
                    ->first();

                $year = $userYear->year_id;
            } else {
                return new Response("unauthorized");
            }
        }

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
                    ->leftjoin('user_question', function ($join) use ($userYear) {
                        $join->on('question.id', '=', 'user_question.question_id');
                        $join->on('user_question.user_year_id', "=", DB::raw($userYear->id));
                    })
                    ->leftjoin('user_file', 'user_question.id', 'user_file.user_question_id')
                    ->leftjoin('feedback', 'user_question.id', 'feedback.user_question_id')
                    ->groupBy('question.id')
                    ->select('question.id', 'question.text', 'question.group_id', 'question.condition', 'question.type', 'question.answer_option', 'question.parent', 'question.has_childs', 'question.question_genre_id', 'question.tip_text', 'user_question.question_answer as answer', DB::raw("group_concat(`user_file`.`name` SEPARATOR '|;|') as `file_names`"), 'user_question.approved', 'feedback.text as feedback', 'feedback.admin_note')
                    ->orderBy('question.sort', 'asc')
                    ->get();

                $q = array();

                foreach ($questions as $question) {
                    $question->category_id = $category->id;
                    if (strpos($question->file_names, '|;|') !== false) {
                        $question->file_names = explode('|;|', $question->file_names);
                    } else if ($question->file_names === null) {
                        $question->file_names = [];
                    } else {
                        $question->file_names = [$question->file_names];
                    }

                    if (empty($question->parent)) {
                        $this->getChildren($question, $userYear, $userYearEmpty, false, $category->id);

                        if ($userYearEmpty) {
                            unset($question->admin_note);
                        }

                        array_push($q, $question);
                    }
                }

//                unset($group->category_id);
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

    function getChildren($question, $userYear, $userYearEmpty, $plusChild, $categoryId)
    {
        if ($question->answer_option == 1) {
            $question['answer_options'] = $question->getOptions()->pluck('text')->toArray();
        } else {
            $question['answer_options'] = null;
        }

        if ($question->has_childs) {
            $children = [];
            if ($question->type === 8) {
                $userQuestions = Question::from(DB::raw("(select
                    `id`,
                    `type`,
                    group_concat(`d`.`qpid` SEPARATOR '|;|') as `qpids`,
                    group_concat(`d`.`answers` SEPARATOR '|;|') as `answers`,
                    group_concat(IFNULL(`d`.`file_names`, '') SEPARATOR '|;|') as `file_names`,
                    group_concat(`d`.`approveds` SEPARATOR '|;|') as `approveds`,
                    group_concat(`d`.`feedbacks` SEPARATOR '|;|') as `feedbacks`,
                    group_concat(`d`.`admin_notes` SEPARATOR '|;|') as `admin_notes`
                    from (
                        select
                        `questions`.`id`,
                        `questions`.`type`,
                        `question_plus`.`id` as `qpid`,
                        group_concat(IFNULL(`user_question`.`question_answer`, '') SEPARATOR '~-~') as `answers`,
                        group_concat(`user_file`.`name` SEPARATOR '~-~') as `file_names`,
                        group_concat(IFNULL(`user_question`.`approved`, 0) SEPARATOR '~-~') as `approveds`,
                        group_concat(IFNULL(`feedback`.`text`, '') SEPARATOR '~-~') as `feedbacks`,
                        group_concat(IFNULL(`feedback`.`admin_note`, '') SEPARATOR '~-~') as `admin_notes`
                        from (select * from `question`) `questions`
                        cross join (select @pv := " . $question->id . ") `initialisation`
                        left join `question_plus`
                        on `question_plus`.`question_id` = " . $question->id . "
                        left join `user_question`
                        on `question_plus`.`id` = `user_question`.`question_plus_id`
                        and `user_question`.`question_id` = `questions`.`id`
                        and `user_question`.`user_year_id` = " . $userYear->id . "
                        left join `user_file`
                        on `user_question`.`id` = `user_file`.`user_question_id`
                        left join `feedback` on `user_question`.`id` = `feedback`.`user_question_id`
                        where find_in_set(`parent`, @pv) > 0 and @pv := concat(@pv, ',', `questions`.`id`)
                        group by `questions`.`id`, `question_plus`.`id`
                    ) `d`
                    group by `d`.`id`) `a`"))
                ->get();

//                echo $userQuestions;
//                exit;

                $answers = null;
                foreach($userQuestions as $userQuestion) {
                    $userQuestion->category_id = $categoryId;
                    if (strpos($userQuestion['qpids'], '|;|') !== false) {
                        $userQuestion['qpids'] = array_map('intval', explode('|;|', $userQuestion['qpids']));
                        $userQuestion['answers'] = explode('|;|', $userQuestion['answers']);
                        $userQuestion['file_names'] = array_map(function($val){return $val === '' ? null : $val;}, explode('|;|', $userQuestion['file_names']));
                        $userQuestion['approveds'] = array_map('intval', explode('|;|', $userQuestion['approveds']));
                        $userQuestion['feedbacks'] = explode('|;|', $userQuestion['feedbacks']);
                        $userQuestion['admin_notes'] = explode('|;|', $userQuestion['admin_notes']);
                    } else if ($userQuestion['qpids'] === null) {
                        $userQuestion['qpids'] = [];
                        $userQuestion['answers'] = [];
                        $userQuestion['file_names'] = [];
                        $userQuestion['approveds'] = [];
                        $userQuestion['feedbacks'] = [];
                        $userQuestion['admin_notes'] = [];
                    } else {
                        $userQuestion['qpids'] = [$userQuestion['qpids']];
                        $userQuestion['answers'] = [$userQuestion['answers']];
                        $userQuestion['file_names'] = [$userQuestion['file_names'] === '' ? null : $userQuestion['file_names']];
                        $userQuestion['approveds'] = [(int)$userQuestion['approveds']];
                        $userQuestion['feedbacks'] = [$userQuestion['feedbacks']];
                        $userQuestion['admin_notes'] = [$userQuestion['admin_notes']];
                    }
                    for ($i = 0; $i < count($userQuestion['qpids']); $i++) {
                        $answers[$userQuestion['qpids'][$i]][$userQuestion['id']] = array(
                            "answer" => $userQuestion['answers'][$i],
                            "type" => $userQuestion['type'][$i],
                            "file_names" => $userQuestion['file_names'][$i] == [] ? [] : explode('~-~', $userQuestion['file_names'][$i]),
                            "approved" => $userQuestion['approveds'][$i],
                            "feedback" => $userQuestion['feedbacks'][$i] === '' ? null : $userQuestion['feedbacks'][$i],
                            "admin_note" => $userQuestion['admin_notes'][$i] === '' ? null : $userQuestion['admin_notes'][$i],
                        );

                        if (!$userYearEmpty) {
                            $answers[$userQuestion['qpids'][$i]][$userQuestion['id']]["admin_note"] = $userQuestion['admin_notes'][$i];
                        }
                    }
                }

                unset($question['answer']);

                $childs = $question->getChilds()
                    ->leftjoin('question_plus', 'question.parent', 'question_plus.question_id')
                    ->leftjoin('user_question', function($join) use ($userYear) {
                        $join->on('question_plus.id', '=', 'user_question.question_plus_id');
                        $join->on('question.id', "=", 'user_question.question_id');
                    })
                    ->leftjoin('feedback', 'user_question.id', 'feedback.user_question_id')
                    ->groupBy('question.id')
                    ->select('question.id', 'question.text', 'question.group_id', 'question.condition', 'question.type', 'question.answer_option', 'question.parent', 'question.has_childs', 'question.question_genre_id', DB::raw("group_concat(`question_plus`.`id` SEPARATOR '|;|') as `qpids`"), DB::raw("group_concat(IFNULL(`user_question`.`question_answer`, '') SEPARATOR '|;|') as `answers`"), DB::raw("group_concat(IFNULL(`user_question`.`approved`, 0) SEPARATOR '|;|') as `approveds`"), DB::raw("group_concat(IFNULL(`feedback`.`text`, '') SEPARATOR '|;|') as `feedbacks`"), DB::raw("group_concat(IFNULL(`feedback`.`admin_note`, '') SEPARATOR '|;|') as `admin_notes`"))
                    ->orderBy('question.sort', 'asc')
                    ->get();

                forEach ($childs as $child) {
                    $child->category_id = $categoryId;
                    $this->getChildren($child, $userYear, $userYearEmpty, true, $categoryId);

                    unset($child['qpids']);
                    unset($child['answers']);
                    unset($child['approveds']);
                    unset($child['feedbacks']);
                    unset($child['admin_notes']);

                    array_push($children, $child);
                }

                unset($question['approved']);
                unset($question['feedback']);
                unset($question['answer_option']);
                unset($question['answer_options']);
//                unset($question['parent']);
                unset($question['has_childs']);
                if($answers == null) {
                    $answers = new stdClass();
                }
                $question['answers'] = $answers;
            } else {
                $childs = $question->getChilds()
                    ->leftjoin('user_question', function($join) use ($userYear) {
                        $join->on('question.id', '=', 'user_question.question_id');
                        $join->on('user_question.user_year_id', "=", DB::raw($userYear->id));
                    })
                    ->leftjoin('user_file', 'user_question.id', 'user_file.user_question_id')
                    ->leftjoin('feedback', 'user_question.id', 'feedback.user_question_id')
                    ->groupBy('question.id')
                    ->select('question.id', 'question.text', 'question.group_id', 'question.condition', 'question.type', 'question.answer_option', 'question.parent', 'question.has_childs', 'question.question_genre_id', 'question.tip_text', 'user_question.question_answer as answer', DB::raw("group_concat(`user_file`.`name` SEPARATOR '|;|') as `file_names`"), 'user_question.approved', 'feedback.text as feedback', 'feedback.admin_note')
                    ->orderBy('question.id', 'asc')
                    ->get();

                forEach ($childs as $child) {
                    $child->category_id = $categoryId;
                    if (strpos($child->file_names, '|;|') !== false) {
                        $child->file_names = explode('|;|', $child->file_names);
                    }else if ($child->file_names === null) {
                        $child->file_names = [];
                    } else {
                        $child->file_names = [$child->file_names];
                    }

                    array_push($children, $child);
                    $this->getChildren($child, $userYear, $userYearEmpty, $plusChild, $categoryId);

                    if ($plusChild) {
                        unset($child['answer']);
                        unset($child['file_names']);
                        unset($child['approved']);
                        unset($child['feedback']);
                    }
                }

                unset($question['answer_option']);
//                unset($question['parent']);
                unset($question['has_childs']);
            }

            $question['children'] = $children;
        } else {
            unset($question['answer_option']);
//            unset($question['parent']);
            unset($question['has_childs']);

            $question['children'] = null;

            return $question;
        }
    }
}
