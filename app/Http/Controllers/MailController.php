<?php
namespace App\Http\Controllers;

use App\Person;
use App\User;
use stdClass;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Mail;
use App\Utils\Enums\ProgressState;
use Log;

class MailController extends Controller
{

    public static function sendMail($meeting)
    {
        $emailData = [
            'name' => isset($meeting->firstName)? $meeting->firstName : "",
            'email' => $meeting->email,
            'service' => isset($meeting->service)?$meeting->service : "",
            'comments' =>  isset($meeting->comments)?$meeting->comments: "",
            'startDate' => isset($meeting->startDate)?$meeting->startDate:"",
            'startTime' => isset($meeting->startTime)?$meeting->startTime:"",
            'endTime' => isset($meeting->endTime)?$meeting->endTime:"",
            'type' => isset($meeting->type)?$meeting->type:"",
            'socialName' => isset($meeting->socialName)?$meeting->socialName:"",
            'template' => $meeting->template,
            'subject' => $meeting->subject,
            'resetLink' => isset($meeting->resetLink)?$meeting->resetLink:"",
            'year' => isset($meeting->year)?$meeting->year:"",
            'activateLink' => isset($meeting->activateLink)?$meeting->activateLink:"",
        ];

        Mail::send($emailData['template'], $emailData, function ($message) use ($emailData) {
            $message->to($emailData['email'], '')->subject($emailData['subject']);
            $message->from('info@kcps.nl', 'Info || TTMTax');
        });
    }

    public static function sendStatusMail($userYear)
    {
        $user = User::where('person_id','=',$userYear->person_id)
            ->join('person', 'user.person_id', 'person.id')
            ->first();

        $meeting = new StdClass();
        $meeting->email = $user->email;
        $meeting->firstName = $user->email;
        $meeting->year = $userYear->year_id;
        $meeting->firstName = $user->first_name;

        switch ($userYear->status) {
            case ProgressState::questionnaireStartedPaid:

                break;
            case ProgressState::questionnaireNotApproved:
                $meeting->template="mails.statusMails.questionaireEditingRequired";
                $meeting->subject="Missing information questionnaire ". $userYear->year_id;
                self::sendMail($meeting);
                return 'mail send';
                break;
            case ProgressState::questionnaireSubmittedNotPaid:
                $meeting->template="mails.statusMails.questionnaireSubmittedNotPaid";
                $meeting->subject="next step -payment";
                return 'mail send';
                break;
            case ProgressState::questionnaireApproved:
                $meeting->template="mails.statusMails.questionaireApproved";
                $meeting->subject="Questionnaire approved - no action required";
                self::sendMail($meeting);
                return 'mail send';
                break;
            case ProgressState::reportUploaded:
                $meeting->template="mails.statusMails.questionaireReportReady";
                $meeting->subject="Tax return ready for review - Dutch income tax return ". $userYear->year_id. " prepared, please review";
                self::sendMail($meeting);
                return 'mail send';
                break;
            case ProgressState::preliminaryTaxUploaded:
                $meeting->template="mails.statusMails.taxReturnFiled";
                $meeting->subject="TTMTax Questionaire";
                self::sendMail($meeting);
                return 'mail send';
                break;
            case ProgressState::taxReturnFiled:
                $meeting->template="mails.statusMails.taxReturnFiled";
                $meeting->subject="Tax return filed - your Dutch income tax return filed with the Dutch tax authorities";
                self::sendMail($meeting);
                return 'mail send';
                break;
            case ProgressState::finalTaxAssessmentUploaded:
                $meeting->template="mails.statusMails.finalTaxAssesmentUploaded";
                $meeting->subject="Tax assessment " . $userYear->year_id;
                self::sendMail($meeting);
                return 'mail send';
                break;
            default:
        }

    }
}