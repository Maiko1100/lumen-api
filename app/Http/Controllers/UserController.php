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

class UserController extends Controller
{

    public function addUser(Request $request)
    {
        $auth = new AuthController();

        $credentials = $this->getCredentials($request);

        $person = new Person();
        $person->first_name = $request->input('name');
        $person->save();

        $user = new User();
        $user->person_id = $person->id;
        $user->email = $credentials['email'];
        $user->password = app('hash')->make($credentials['password']);
        $user->save();

        $loggedInUser = $auth->postLogin($request);


        return $loggedInUser;
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
            return "You are not authorized to do this call";
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
            return "You are not authorized to do this call";
        }
    }


    public function getAllCases()
    {
        $user = JWTAuth::parseToken()->authenticate();
        if ($user->role == 3) {
            $cases = DB::table('user_year')
                ->join('person', 'user_year.person_id', '=', 'person.id')
                ->leftjoin('person as employee', 'user_year.employee_id', '=', 'employee.id')
                ->select('employee.first_name as employee_name','user_year.year_id', 'user_year.package', 'user_year.status', 'user_year.id', 'user_year.employee_id', 'person.id as person_id', 'person.first_name', 'person.last_name', 'person.passport', 'person.bsn', 'person.dob')->get();
            return $cases;
        } elseif ($user->role == 2) {
            $cases = DB::table('user_year')
                ->join('person', 'user_year.person_id', '=', 'person.id')
                ->where('user_year.employee_id', '=', $user->person_id)
                ->select('user_year.year_id', 'user_year.package', 'user_year.status', 'user_year.id', 'user_year.employee_id', 'person.id as person_id', 'person.first_name', 'person.last_name', 'person.passport', 'person.bsn', 'person.dob')->get();
            return $cases;
        } else {
            return "You are not authorized to do this call";
        }
    }
}
