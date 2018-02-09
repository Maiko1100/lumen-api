<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\UserYear as UserYear;
use App\Question as Question;
use App\UserQuestion as UserQuestion;

class UserQuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Response
     *
     */
    public function save(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $year = $request->input('year');
        $questionId = $request->input('id');
        $answer = $request->input('answer');
        $qpid = $request->input('qpid');

        $uq = null;
        if (isset($year)) {
            $userYear = UserYear::where("person_id", "=", $user->person_id)
                ->where("year_id", "=", $year)->first();

            $isProfile = Question::where("id", "=", $questionId)
                    ->first()
                    ->getGenre()
                    ->first()
                    ->isProfile == 1;

            $profileQuestionId = null;

            if ($isProfile) {
                $quest = Question::where('id', '=', $questionId)
                    ->select('profile_question_id')
                    ->first();

                $profileQuestionId = $quest->profile_question_id;

                $existingProfileQuestion = $this->checkProfile($profileQuestionId, $qpid, $user);

                if (isset($existingProfileQuestion)) {
                    $existingProfileQuestion->question_answer = $answer;
                    $existingProfileQuestion->save();
                } else {
                    $profileUserQuestion = new UserQuestion();
                    $profileUserQuestion->person_id = $user->person_id;
                    $profileUserQuestion->question_id = $questionId;
                    $profileUserQuestion->question_answer = $answer;
                    $profileUserQuestion->question_plus_id = $qpid;
                    $profileUserQuestion->profile_question_id = $profileQuestionId;

                    $profileUserQuestion->save();
                }
            }

            if (isset($qpid)) {
                $existingQuestion = $this->checkPlus($questionId, $qpid, $userYear, $profileQuestionId);

                if (isset($existingQuestion)) {
                    $existingQuestion->question_answer = $answer;
                    $existingQuestion->save();

                    $uq = $existingQuestion;
                } else {
                    $userQuestion = new UserQuestion();
                    $userQuestion->person_id = $user->person_id;
                    $userQuestion->user_year_id = $userYear->id;
                    $userQuestion->question_id = $questionId;
                    $userQuestion->question_answer = $answer;
                    $userQuestion->question_plus_id = $qpid;
                    $userQuestion->profile_question_id = $profileQuestionId;

                    $userQuestion->save();

                    $uq = $userQuestion;
                }
            } else {
                $existingQuestion = $this->check($userYear, $questionId, $profileQuestionId);
                if (isset($existingQuestion)) {
                    $existingQuestion->question_answer = $answer;
                    $existingQuestion->save();

                    $uq = $existingQuestion;
                } else {
                    $userQuestion = new UserQuestion();
                    $userQuestion->person_id = $user->person_id;
                    $userQuestion->user_year_id = $userYear->id;
                    $userQuestion->question_id = $questionId;
                    $userQuestion->question_answer = $answer;
                    $userQuestion->profile_question_id = $profileQuestionId;

                    $userQuestion->save();

                    $uq = $userQuestion;
                }
            }
        } else {
            $userYears = UserYear::where('status', '<', 3)
                ->where('person_id', '=', $user->person_id)
                ->get();

            $quest = Question::where('id', '=', $questionId)
                ->select('profile_question_id')
                ->first();

            $profileQuestionId = $quest->profile_question_id;

            if (isset($qpid)) {
                $existingQuestion = $this->checkPlus($questionId, $qpid, NULL, $profileQuestionId);

                if (isset($existingQuestion)) {
                    $existingQuestion->question_answer = $answer;
                    $existingQuestion->save();

                    $uq = $existingQuestion;
                } else {
                    $userQuestion = new UserQuestion();
                    $userQuestion->person_id = $user->person_id;
                    $userQuestion->question_id = $questionId;
                    $userQuestion->question_answer = $answer;
                    $userQuestion->question_plus_id = $qpid;
                    $userQuestion->profile_question_id = $profileQuestionId;

                    $userQuestion->save();

                    $uq = $userQuestion;
                }

                foreach($userYears as $userYear) {
                    $existingQuestion = $this->checkPlus($questionId, $qpid, $userYear, $profileQuestionId);

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
                }
            } else {
                $existingQuestion = $this->check(NULL, $questionId, $user->person_id);
                if (isset($existingQuestion)) {
                    $existingQuestion->question_answer = $answer;
                    $existingQuestion->save();

                    $uq = $existingQuestion;
                } else {
                    $userQuestion = new UserQuestion();
                    $userQuestion->person_id = $user->person_id;
                    $userQuestion->question_id = $questionId;
                    $userQuestion->question_answer = $answer;
                    $userQuestion->profile_question_id = $profileQuestionId;

                    $userQuestion->save();

                    $uq = $userQuestion;
                }

                foreach($userYears as $userYear) {
                    $existingQuestion = $this->check($userYear, $questionId);
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
            }
        }

        return $uq->id;

    }

    private function check($userYear, $questionId, $personId = null)
    {

        if (isset($userYear)) {
            return UserQuestion::where("question_id", "=", $questionId)
                ->where("user_year_id", "=", $userYear->id)
                ->first();
        } else {
            return UserQuestion::where("question_id", "=", $questionId)
                ->whereNull("user_year_id")
                ->where('person_id', '=', $personId)
                ->first();
        }

    }

    private function checkPlus($questionId, $qpid, $userYear, $personId = null)
    {
        if (isset($userYear)) {
            return UserQuestion::where("question_id", "=", $questionId)
                ->where("question_plus_id", "=", $qpid)
                ->where("user_year_id", "=", $userYear->id)
                ->first();
        } else {
            return UserQuestion::where("question_id", "=", $questionId)
                ->where("question_plus_id", "=", $qpid)
                ->whereNull("user_year_id")
                ->where('person_id', '=', $personId)
                ->first();
        }
    }

    private function checkProfile($profileId, $qpid, $user)
    {
        if(isset($qpid)) {
            return UserQuestion::where("profile_question_id", "=", $profileId)
                ->where("question_plus_id", "=", $qpid)
                ->where("person_id", "=", $user->person_id)
                ->whereNull("user_year_id")
                ->first();
        } else {
            return UserQuestion::where("profile_question_id", "=", $profileId)
                ->where("person_id", "=", $user->person_id)
                ->whereNull("user_year_id")
                ->first();
        }
    }

    public function getProfileData(Request $request) {
        $userId = null;
        if ($request->has('user_id')) {
            $userId = $request->input('user_id');
        }
        if($request->has('user_year_id')) {
            $userYear = UserYear::where('id', '=', $request->input('user_year_id'))
                ->select('person_id')
                ->first();
            $userId = $userYear->person_id;
        }
        if (empty($userId)) {
            return "Bad Parameters";
        }

        return UserQuestion::join('question', 'user_question.question_id', 'question.id')
            ->where('user_question.person_id', '=', $userId)
            ->whereNull('user_question.user_year_id')
            ->orderBy('user_question.profile_question_id')
            ->select('question.text', 'user_question.question_answer')
            ->get();
    }

}

?>