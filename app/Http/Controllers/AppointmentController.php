<?php 

namespace App\Http\Controllers;
use App\Appointment as Appointment;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\JsonResponse;

class AppointmentController extends Controller
{
  public function getUserAppointment() {
      $user = JWTAuth::parseToken()->authenticate();
      $userAppointments = Appointment::where('person_id', '=', $user->person_id)->get();

      return new JsonResponse($userAppointments);
  }
  
}

?>