<?php

namespace App\Http\Controllers;

use App\Feedback;
use App\Question;
use App\UserQuestion;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\UserYear;
use Illuminate\Support\Facades\DB;

class FeedbackController extends Controller
{

    public function saveQuestionFeedBack(Request $request)
    {

        $user = JWTAuth::parseToken()->authenticate();

        if ($user->role == 2 || $user->role == 3) {
            $userQuestion = UserQuestion::where('user_year_id' ,'=' ,$request->input('userYear'))->where('question_id','=',$request->input('id'))->first();

            $feedback = new Feedback();

            $feedback->person_id = $user->person_id;
            $feedback->text = $request->input('feedback');
            $feedback->user_question_id = $userQuestion->id;
            $feedback->save();
        }
        return 'succes';
    }

}

?>