<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

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
        $fullpath="app/userDocuments/{$personId}/{$filename}";

        return response()->download(storage_path($fullpath), null, [], null);

    }





}

?>