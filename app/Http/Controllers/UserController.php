<?php 

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Auth\AuthController;
use App\Person as Person;
use App\User as User;


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
        $loginarray = explode(':',base64_decode(substr($header,6)));

        $login = [
            'email'=> $loginarray[0],
            'password'=> $loginarray[1]
        ];


        return ($login);
    }
  
}

?>