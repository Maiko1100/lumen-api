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
        $userYear = UserYear::where("person_id", "=", $user->person_id)
            ->where("year_id", "=", $year)->first();

        $isProfile = Question::where("id", "=", $questionId)
                ->first()
                ->getGenre()
                ->first()
                ->isProfile == 1;

        if (isset($qpid)) {

            if (isset($isProfile)) {
                $existingQuestion = $this->checkPlus($questionId, $qpid, $userYear);
            } else {
                $existingQuestion = $this->checkPlus($questionId, $qpid);
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

        return new Response($year);

    }

    private function check($userYear, $questionId)
    {
        return UserQuestion::where("user_year_id", "=", $userYear->id)->where("question_id", "=", $questionId)->first();
    }

    private function checkPlus($questionId, $qpid, $userYear = NULL)
    {
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