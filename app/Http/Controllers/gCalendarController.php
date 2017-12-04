<?php
namespace App\Http\Controllers;

use Carbon\Carbon;
use Dingo\Api\Contract\Http\Request;
use App\Event;
use Illuminate\Http\JsonResponse;

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

        $meeting = json_decode($request->input('formValues'))->formvalues;

        $event = Event::find($meeting->eventId);
        $event->name = 'Tax Advice meeting with ' . $meeting->firstName ." " . $meeting->lastName ;
        $event->colorId = 11;
        $event->description = "Name: ".$meeting->firstName ." ". $meeting->lastName. " || Service: ".$meeting->service . " || Phonenumber: ". $meeting->phoneNumber." || ". "Socialname: ". $meeting->socialName." || Type " .$meeting->type." || Comments: " . $meeting->comments ;
        $event->save();
        return self::getMeetings();
    }

}
