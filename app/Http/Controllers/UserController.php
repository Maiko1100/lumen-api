<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\AuthController;
use App\Person as Person;
use App\User as User;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\UserYear as UserYear;
use stdClass;
use App\PasswordReset as PasswordReset;
use App\ActivateToken as ActivateToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Utils\Enums\userRole;
use Webpatser\Uuid\Uuid;

class UserController extends Controller
{

    public function addEmployee(Request $request){
        $auth = new AuthController();
        $credentials = $this->getCredentials($request);

        if(User::where('email', '=', $credentials['email'])->exists()){
            abort(400, "A user with this email address already exists.");
        }

        $person = new Person();
        $person->first_name = $request->input('fname');
        $person->last_name = $request->input('lname');
        $person->save();

        $user = new User();
        $user->person_id = $person->id;
        $user->email = $credentials['email'];
        $user->role=userRole::employee;
        $user->password = app('hash')->make($credentials['password']);
        $user->save();

        return $user;
    }

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

//        $activationToken = Uuid::generate();
//        $activateToken = new ActivateToken();
//        $activateToken->user_id = $user->person_id;
//        $activateToken->token = $activationToken;
//        $activateToken->save();
//        $meeting = new StdClass();
//        $meeting->email = $user->email;
//        $meeting->name = "test";
//        $meeting->template="mails.userMails.activateAccount";
//        $meeting->subject="TTMTax activate account";
//        $meeting->activateLink = "http://localhost:1333/user/reset/".$activationToken;
//
//        MailController::sendMail($meeting);

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
                ->leftjoin('order', 'user_year.id', 'order.user_year_id')
                ->select('employee.first_name as employee_name', 'user_year.year_id', 'order.service_name as package', 'user_year.status', 'user_year.id', 'user_year.employee_id', 'person.id as person_id', 'person.first_name')->get();
            return $cases;
        } elseif ($user->role == 2) {
            $cases = DB::table('user_year')
                ->join('person', 'user_year.person_id', '=', 'person.id')
                ->leftjoin('order', 'user_year.id', 'order.user_year_id')
                ->where('user_year.employee_id', '=', $user->person_id)
                ->select('user_year.year_id', 'order.service_name as package', 'user_year.status', 'user_year.id', 'user_year.employee_id', 'person.id as person_id', 'person.first_name')->get();
            return $cases;
        } else {
            abort(400, "You are not authorized to do this call");
//            return "You are not authorized to do this call";
        }
    }

    public function changeTaxRulingState(Request $request) {
        if ($request->has('state')) {
            $user = JWTAuth::parseToken()->authenticate();

            User::where('person_id', "=", $user->person_id)->update(
                [
                    'tax_ruling_state' => $request->input('state')
                ]);

            return $request->input('state');
        } else {
            return 'Bad parameters';
        }
    }

    public function getCaseAndUser(Request $request){
        $user = JWTAuth::parseToken()->authenticate();

        $case = UserYear::where('user_year.id', '=' ,$request->input('caseId'))
            ->leftjoin('person', 'person.id', '=', 'user_year.person_id')
            ->leftjoin('person as assignee', 'assignee.id', '=', 'employee_id')
            ->leftjoin('order', 'user_year.id', 'order.user_year_id')
            ->select('assignee.first_name as assignee_first_name', 'person.first_name', 'user_year.status', 'user_year.year_id', 'user_year.updated_at', 'order.service_name as package', 'order.price')->first();
        return $case;
    }

    public function createResetLink(Request $request){
        $email = $request->input('email');
        $user = User::where('email','=',$email)->first();

        if(!isset($user)){
            abort(400,"Email does not exist". $email);
        }

        $token = Uuid::generate();

        $passwordReset = new PasswordReset();
        $passwordReset->email = $email;
        $passwordReset->token = $token;
        $passwordReset->save();

        $meeting = new StdClass();
        $meeting->email = $email;
        $meeting->name = "test";
        $meeting->template="mails.userMails.passwordReset";
        $meeting->subject="Reset password";
        $meeting->resetLink = env("BASE_URL_FRONT_END","http://test-ttmtax.kcps.nl/")."/reset?reset_token=".$token;

        MailController::sendMail($meeting);

        return JsonResponse::create("reset email send to user");
    }

    public function activateAccount(Request $request){
        $token = $request->input('activationToken');
        $activateToken = ActivateToken::where('token', '=',$token);
        $user = User::where('person_id','=',$activateToken->user_id)->first();
        $user->is_active = 1;
        $user->save();

        $activateToken->delete();
        return JsonResponse::create("account activated succesfully");
    }

    public function resetPasswordWithToken(Request $request){
        $resetString = $request->input('resetString');
        $passwordReset = PasswordReset::where('token','=', $resetString)->first();

        if(isset($passwordReset)){
            $user = User::where('email','=',$passwordReset->email)->first();
            $user->password = app('hash')->make($request->input('newPassword'));
            $user->save();
            $passwordReset->delete();
            return $user;
        }
        abort(400, "Reset token not found");
    }

    public function deleteUser(Request $request) {
        $user = JWTAuth::parseToken()->authenticate();

        if($user->role >= 3){
            $userToDelete = $request->input('customerId');


            $userYears = UserYear::where('person_id','=',$userToDelete)->get();
            $user = User::where('person_id', '=', $userToDelete)->first();
            $person = Person::where('id', '=', $userToDelete)->first();


            foreach($userYears as $userYear){
                $userYear->delete();
            }

            $user->delete();
            $person->delete();

            return "User and cases has been deleted.";

        }else{
            abort(400, "You're not authorized to make this call");
        }
    }
}
