<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Auth\AuthController;
use App\Person as Person;
use App\User as User;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\UserYear as UserYear;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Utils\Enums\userRole;

class UserController extends Controller
{

    public function addUser(Request $request)
    {
        $auth = new AuthController();
        $credentials = $this->getCredentials($request);

        if(User::where('email', '=', $credentials['email'])->exists()){
            abort(400, "A user with this email address already exists.");
        }

        $person = new Person();
        $person->first_name = $request->input('name');
        $person->save();

        $user = new User();
        $user->person_id = $person->id;
        $user->email = $credentials['email'];
        $user->role=userRole::regularUser;
        $user->password = app('hash')->make($credentials['password']);
        $user->save();

        $loggedInUser = $auth->postLogin($request);

        return $loggedInUser;


    }

    public function updateUserPassword(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $oldPassword = $request->input('oldPassword');


        if(Hash::check($oldPassword, $user->password)){
            $user->password = app('hash')->make($request->input('newPassword'));
            $user->save();
            return $user;
        }else{
            abort (400, "Password didn't match");
//            return "error - password didn't match";
        }
    }

    protected function getCredentials(Request $request)
    {
        $header = $request->header('Authorization');
        $loginarray = explode(':', base64_decode(substr($header, 6)));

        $login = [
            'email'=> $loginarray[0],
            'password'=> $loginarray[1]
        ];

        return ($login);
    }

    public function getAllCustomers()
    {
        // Check if user is admin
        $user = JWTAuth::parseToken()->authenticate();
        if ($user->role == 2 || $user->role == 3) {
            $customers = User::where("role", "=", 1)->orWhere("role", "=", 0)
            ->join('person', 'person_id', '=', 'person.id')->get();

            return $customers;
        } else {
            abort(400, "You are not authorized to do this call");
//            return "You are not authorized to do this call";
        }
    }

    public function getAllEmployees()
    {
        $user = JWTAuth::parseToken()->authenticate();
        if ($user->role == 3) {
            $employees = User::where("role", "=", 2)
            ->join('person', 'person_id', '=', 'person.id')->get();
            return $employees;
        } else {
            abort(400, "You are not authorized to do this call");
//            return "You are not authorized to do this call";
        }
    }


    public function getAllCases()
    {
        $user = JWTAuth::parseToken()->authenticate();
        if ($user->role == 3) {
            $cases = DB::table('user_year')
                ->join('person', 'user_year.person_id', '=', 'person.id')
                ->leftjoin('person as employee', 'user_year.employee_id', '=', 'employee.id')
                ->select('employee.first_name as employee_name', 'user_year.year_id', 'user_year.package', 'user_year.status', 'user_year.id', 'user_year.employee_id', 'person.id as person_id', 'person.first_name', 'person.last_name', 'person.passport', 'person.bsn', 'person.dob')->get();
            return $cases;
        } elseif ($user->role == 2) {
            $cases = DB::table('user_year')
                ->join('person', 'user_year.person_id', '=', 'person.id')
                ->where('user_year.employee_id', '=', $user->person_id)
                ->select('user_year.year_id', 'user_year.package', 'user_year.status', 'user_year.id', 'user_year.employee_id', 'person.id as person_id', 'person.first_name', 'person.last_name', 'person.passport', 'person.bsn', 'person.dob')->get();
            return $cases;
        } else {
            abort(400, "You are not authorized to do this call");
//            return "You are not authorized to do this call";
        }
    }

    public function getCaseAndUser(Request $request){
        $user = JWTAuth::parseToken()->authenticate();

        $case = UserYear::where('user_year.id', '=' ,$request->input('caseId'))
            ->leftjoin('person', 'person.id', '=', 'user_year.person_id')
            ->leftjoin('person as employee', 'employee.id', '=', 'employee_id')
            ->select('employee.first_name as employee_first_name', 'employee.last_name as employee_last_name', 'person.first_name', 'person.last_name', 'person.bsn', 'person.dob', 'user_year.status', 'user_year.year_id', 'user_year.package')->first();
        return $case;
    }
}
