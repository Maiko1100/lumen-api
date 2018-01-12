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
            'name' => isset($meeting->firstName ,$meeting->lastName)? $meeting->firstName  . " " . $meeting->lastName : "",
            'email' => $meeting->email,
            'service' => isset($meeting->service)?$meeting->service : "",
            'comments' =>  isset($meeting->comments)?$meeting->comments: "",
            'startDate' => isset($meeting->startDate)?$meeting->startDate:"",
            'startTime' => isset($meeting->startTime)?$meeting->startTime:"",
            'endTime' => isset($meeting->endTime)?$meeting->endTime:"",
            'template' => $meeting->template,
            'subject' => $meeting->subject,
            'resetLink' => isset($meeting->resetLink)?$meeting->resetLink:"",
            'activateLink' => isset($meeting->activateLink)?$meeting->activateLink:"",
        ];

        Mail::send($emailData['template'], $emailData, function ($message) use ($emailData) {
            $message->to($emailData['email'], '')->subject($emailData['subject']);
            $message->from('info@kcps.nl', 'Info || TTMTax');
        });
    }

    public static function sendStatusMail($userYear)
    {
        $user = User::where('person_id','=',$userYear->person_id)->first();

        $meeting = new StdClass();
        $meeting->email = $user->email;
        $meeting->firstName = $user->email;

        switch ($userYear->status) {
            case ProgressState::questionnaireStartedPaid:

                break;
            case ProgressState::questionnaireNotApproved:
                $meeting->template="mails.statusMails.questionaireEditingRequired";
                $meeting->subject="TTMTax Questionaire";
                self::sendMail($meeting);
                return 'mail send';
                break;
            case ProgressState::questionnaireSubmittedNotPaid:
                return 'geen mail';
                break;
            case ProgressState::questionnaireApproved:
                $meeting->template="mails.statusMails.questionaireReviewed";
                $meeting->subject="TTMTax Questionaire";
                self::sendMail($meeting);
                return 'mail send';
                break;
            case ProgressState::reportUploaded:
                $meeting->template="mails.statusMails.questionaireReportReady";
                $meeting->subject="TTMTax Questionaire";
                self::sendMail($meeting);
                return 'mail send';
                break;
            case ProgressState::taxReturnUploaded:
                $meeting->template="mails.statusMails.taxReturnUploaded";
                $meeting->subject="TTMTax Questionaire";
                self::sendMail($meeting);
                return 'mail send';
                break;
            case ProgressState::finalTaxAssesmentUploaded:
                $meeting->template="mails.statusMails.finalTaxAssesmentUploaded";
                $meeting->subject="TTMTax Questionaire";
                self::sendMail($meeting);
                return 'mail send';
                break;
            default:
        }

    }
}