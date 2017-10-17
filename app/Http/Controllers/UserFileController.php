<?php

namespace App\Http\Controllers;

use App\UserFile;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;
use App\UserYear as UserYear;

class UserFileController extends Controller
{
    public function getFiles()
    {
        $user = JWTAuth::parseToken()->authenticate();
        $files = $user->getUserFiles();

        return $files;
    }

    public function getFile(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $personId = $user->person_id;
        $filename = $request->input('fileName');
        $fullpath = "app/userDocuments/{$personId}/{$filename}";

        return response()->download(storage_path($fullpath), null, [], null);

    }


    public function deleteFile(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $personId = $user->person_id;
        $filename = $request->input('fileName');
        $fullpath = "userDocuments/{$personId}/{$filename}";

        if (Storage::delete($fullpath)) {
            $userfile = UserFile::where("user_year.person_id", "=", $user->person_id)->where('name', "=", $filename)
                ->join("user_year", "user_file.user_year_id", "user_year.id");
            $userfile->delete();
            return new Response('file deleted');
        } else {
            return new Response('file does not exist');
        }


    }

    public function saveQuestionFile(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $year = $request->input('year');
        $questionId = $request->input('id');
        $userYear = UserYear::where("person_id", "=", $user->person_id)
            ->where("year_id", "=", $year)->first();

        foreach ($request->file('files') as $file){
            $pinfo = pathinfo($file->getClientOriginalName());
            $newName = $pinfo['filename'] . "_" . date("YmdHis") . "." . $pinfo['extension'];
            Storage::putFileAs('userDocuments/' . $user->person_id, $file, $newName);

            $userFile = new UserFile();
            $userFile->user_year_id = $userYear->id;
            $userFile->person_id = $user->person_id;
            $userFile->question_id = $questionId;
            $userFile->name = $newName;
            $userFile->type = 10;

            $userFile->save();
        }
        return $newName;
    }

    public function saveFile(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $file = $request->file('file');
        $fileName= $file->getClientOriginalName();
        $userYear = UserYear::where('user_year.person_id', "=", $user->person_id)->where("user_year.year_id", "=", $request->input('year'))->first();


            Storage::putFileAs('userDocuments/' . $user->person_id, $file, $fileName);

            $userFile = new UserFile();
            $userFile->name = $fileName;
            $userFile->type = 1;
            $userFile->user_year_id = $userYear->id;
            $userFile->person_id = $user->person_id;

            $userFile->save();

        return $userFile;

    }



}

?>