<?php 

namespace App\Http\Controllers;

use App\UserFile;
use App\UserYear;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use Intervention\Image\Facades\Image as Image;
use Illuminate\Support\Facades\DB;
use App\Utils\Enums\ProgressState;
use App\Utils\Enums\DocumentType;

class UserYearController extends Controller
{
    public function changeStatus(Request $request){
        $userYearId = $request->input('userYear');

        if(isset($userYearId)){
            $userYear = UserYear::where('id', '=',$userYearId)->first();
            $userYear->status = $request->input('status');
            $userYear->save();
            MailController::sendStatusMail($userYear);
            $cases = DB::table('user_year')
                ->join('person', 'user_year.person_id', '=', 'person.id')
                ->leftjoin('person as employee', 'user_year.employee_id', '=', 'employee.id')
                ->select('employee.first_name as employee_name','user_year.year_id', 'user_year.package', 'user_year.status', 'user_year.id', 'user_year.employee_id', 'person.id as person_id', 'person.first_name', 'person.last_name', 'person.passport', 'person.bsn', 'person.dob')->get();
            return $cases;
        }
        $user = JWTAuth::parseToken()->authenticate();
        $year = $request->input('year');
        $userYear = UserYear::where('person_id', '=',$user->person_id)->where('year_id', '=',$year)->first();
        $status = $request->input('status');
        $userYear->status = $status;
        $userYear->save();
        MailController::sendStatusMail($userYear);


        $userYears = UserYear::where("person_id", "=", $user->person_id)->get();

        $userYearsArray = [];

        foreach ($userYears as $userYear) {
            $userYearsArray['status'][$userYear->year_id] = $userYear->status;
            $userYearsArray['partner'][$userYear->year_id] = $userYear->withPartner;
        }

        return new JsonResponse($userYearsArray);
    }

    public function getUserYears() {
        $user = JWTAuth::parseToken()->authenticate();
        $userYears = UserYear::where("person_id", "=", $user->person_id)->get();

        $userYearsArray = [];

        foreach ($userYears as $userYear) {
            $userYearsArray['status'][$userYear->year_id] = $userYear->status;
            $userYearsArray['partner'][$userYear->year_id] = $userYear->withPartner;
        }

        return new JsonResponse($userYearsArray);
    }

    public function getUserYear(Request $request) {
        $user = JWTAuth::parseToken()->authenticate();
        $userYear = UserYear::where("person_id", "=", $user->person_id)
            ->where("year_id", "=", $request->input('year'))
            ->get();

        $userYearArray = $userYear->toArray();

        $output = NULL;
        if(array_key_exists(0, $userYearArray)) {
            $output = $userYearArray[0];
        }

        return new JsonResponse($output);
    }

  /**
   * Show the form for creating a new resource.
   *
   * @return Response
   */
  public function create(Request $request)
  {
      $user = JWTAuth::parseToken()->authenticate();

      $userYear = new UserYear();
      $userYear->person_id = $user->person_id;
      $userYear->year_id = $request->input('year');
      $userYear->package = $request->input('package');
      $userYear->withPartner = $request->input('partner');
      $userYear->status = ProgressState::questionnaireStartedNotPaid;
      $userYear->save();

      $userYears = UserYear::where("person_id", "=", $user->person_id)->get();

      $userYearsArray = [];

      foreach ($userYears as $userYear) {
          $userYearsArray['status'][$userYear->year_id] = $userYear->status;
          $userYearsArray['partner'][$userYear->year_id] = $userYear->withPartner;
      }

      return new JsonResponse($userYearsArray);
  }

    public function reportAgreed(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $year = $request->input('year');
        $userYear = UserYear::where('person_id', '=',$user->person_id)->where('year_id', '=',$year)->first();
        $userYear->status= ProgressState::fileTaxReturn;
        $userYear->save();

        $fullpath = "app/userDocuments/{$user->person_id}/signature".'_'.$user->person_id.'_'.$year.".png";
        Image::make(file_get_contents($request->input('signature')))->save(storage_path($fullpath));

        $userFile = new UserFile();
        $userFile->name = "signature".'_'.$user->person_id.'_'.$year.".png";
        $userFile->type = DocumentType::signature;
        $userFile->person_id = $user->person_id;
        $userFile->user_year_id = $userYear->id;
        $userFile->save();

        return 'gelukt';


    }

    public function assignEmployee(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        if ($user->role == 3) {
            // assign employee to case
            $caseIdArray = $request->input('caseIdArray');
            $employeeId = $request->input('employeeId');

            foreach ($caseIdArray as $caseId) {
                $case = UserYear::where("id", "=", $caseId)
                    ->first();

                $case->employee_id = $employeeId;
                $case->save();
            }
            $cases = DB::table('user_year')
                ->join('person', 'user_year.person_id', '=', 'person.id')
                ->leftjoin('person as employee', 'user_year.employee_id', '=', 'employee.id')
                ->select('employee.first_name as employee_name','user_year.year_id', 'user_year.package', 'user_year.status', 'user_year.id', 'user_year.employee_id', 'person.id as person_id', 'person.first_name', 'person.last_name', 'person.passport', 'person.bsn', 'person.dob')->get();
            return $cases;
        } else {
            return "You are not authorized to do this call";
        }
    }

}

?>