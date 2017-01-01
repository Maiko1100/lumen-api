<?php
namespace App\Http\Controllers;

use Carbon\Carbon;
use Dingo\Api\Contract\Http\Request;
use App\Event;
use Illuminate\Http\JsonResponse;
use App\Appointment;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Mail;
use Log;

class gCalendarController extends Controller
{
    public function getMeetings()
    {
        $optParams = ['q' => 'Meeting'];
        $events = Event::get(Carbon::now(), Carbon::now()->addMonth(3), $optParams);
        return JsonResponse::create($events);
    }

    public function updateMeeting(Request $request)
    {
        if($request->header('Authorization')!= "Bearer null") {
            $user = JWTAuth::parseToken()->authenticate();
        }else{
            $user = null;
        }
        $meeting = json_decode($request->input('formValues'))->formvalues;
        $appointmentStartDate = $meeting->startDate;
        $startDate = date_format(date_create($meeting->startDate),"d F Y");
        $startTime = date_format(date_create($meeting->startDate),"H:i");
        $endTime = date_format(date_create($meeting->endDate),"H:i");

        $meeting->template = 'mails.appointment.appointmentMade';
        $meeting->subject = 'TTMTax appointment';
        $meeting->startDate = $startDate;
        $meeting->startTime = $startTime;
        $meeting->endTime = $endTime;
        MailController::sendMail($meeting);

        $meeting2 = new \stdClass();
        $meeting2->template = 'mails.appointment.appointmentThijs';
        $meeting2->subject = 'Appointment made';
        $meeting2->email = 'info@ttmtax.nl';
        $meeting2->startDate = $startDate;
        $meeting2->startTime = $startTime;
        $meeting2->endTime = $endTime;
        MailController::sendMail($meeting2);

        $event = Event::find($meeting->eventId);
        $event->name = 'Tax Advice meeting with ' . $meeting->firstName ." " . $meeting->lastName ;
        $event->colorId = 11;
        $event->description = "Name: ".$meeting->firstName ." ". $meeting->lastName. " || Service: ".$meeting->service . " || Phonenumber: ". $meeting->phoneNumber." || ". "Socialname: ". $meeting->socialName." || Type " .$meeting->type." || Comments: " . $meeting->comments ;
        $event->save();

        $appointment = new Appointment();
        $appointment->startDate = $appointmentStartDate;
        $appointment->endDate = $meeting->endDate;
        if($user!=null) {
            $appointment->person_id =$user->person_id;
        }
        $appointment->save();

        return self::getMeetings();
    }



}
