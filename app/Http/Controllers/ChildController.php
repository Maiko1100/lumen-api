<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use App\Child as Child;
use App\Person as Person;
use App\UserYear as UserYear;
use App\UserFile as UserFile;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Storage;

class ChildController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {

    }

    public function saveChild(Request $request) {

        $user = JWTAuth::parseToken()->authenticate();

        $field = $request->input('field');
        $answer = $request->input('answer');
        $id = $request->input('personId');

        $child = new Child();
        $person = new Person();
        $person->save();

        if(empty($id)){

            if($field == "passport"){

                $file = $request->file('answer');
                $fileName= $file->getClientOriginalName();

                Storage::putFileAs('userDocuments/' . $person->id, $file, $fileName);
                $userFile = new UserFile();
                $userFile->name = $fileName;
                $userFile->type = 1;
                $userFile->person_id = $person->id;

                $userFile->save();
                $person->passport = $userFile->id;
            }else{
                $person->$field = $answer;
                $person->save();
            }

            $child->person_id = $person->id;
            $child->user_id = $user->person_id;
            $child->save();

            return response($child->person_id);
        }else{

            if($field == "passport"){
                $file = $request->file('answer');
                $fileName = $file->getClientOriginalName();

                Storage::putFileAs('userDocuments/' . $person->id, $file, $fileName);
                $userFile = new UserFile();
                $userFile->name = $fileName;
                $userFile->type = 1;
                $userFile->person_id = $person->id;

                $userFile->save();

                $person = Person::where("id", "=", $id)->first();
                UserFile::where("id", "=", $person->passport)->delete();
                $person->$field = $userFile->id;
                $person->save();

            }else{
                Person::where("id", "=", $id)
                    ->update(
                        [
                            $field => $answer
                        ]
                    );
            }


            return response($id);
        }



    }

}

?>