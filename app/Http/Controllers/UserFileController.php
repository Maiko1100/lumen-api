<?php

namespace App\Http\Controllers;

use App\UserFile;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;

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


}

?>