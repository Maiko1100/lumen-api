<?php

namespace App\Http\Controllers;

use App\Feedback;
use App\UserQuestion;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
class FeedbackController extends Controller
{
    public function saveQuestionFeedBack(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if ($user->role == 2 || $user->role == 3) {
            $userQuestion = UserQuestion::where('user_year_id', '=', $request->input('userYear'))
                ->where('question_id', '=', $request->input('id'))
                ->first();

            $feedback = Feedback::where('user_question_id', '=', $userQuestion->id)
                ->first();
            if(!$feedback) {
                $feedback = new Feedback();
                $feedback->person_id = $user->person_id;
                $feedback->user_question_id = $userQuestion->id;
            }
            $feedback->text = $request->input('feedback');
            $feedback->save();

            return 'success';
        } else {
            return 'unauthorized';
        }
    }

    public function saveTaxRulingQuestionFeedBack(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if ($user->role == 2 || $user->role == 3) {
            $userQuestion = UserQuestion::whereNull('user_year_id')
                ->where('person_id', '=', $request->input('person_id'))
                ->where('question_id','=', $request->input('id'))
                ->first();
                
            $feedback = Feedback::where('user_question_id', '=', $userQuestion->id)
                ->first();
            if(!$feedback) {
                $feedback = new Feedback();
                $feedback->person_id = $user->person_id;
                $feedback->user_question_id = $userQuestion->id;
            }
            $feedback->text = $request->input('feedback');
            $feedback->save();

            return 'success';
        } else {
            return 'unauthorized';
        }
    }

}

?>