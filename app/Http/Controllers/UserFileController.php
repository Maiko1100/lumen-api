<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Storage;


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

        $path = app()->storagePath('userDocuments/\1/\dr anders.PNG');
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

//        $user = JWTAuth::parseToken()->authenticate();
//        $fileName = 'userDocuments/' . $user->person_id . '/' . $request->input('fileName');
//        $file = Storage::get($fileName);
//        $file = Storage::get('userDocuments/1/dr anders.PNG');

//        return (new Response($file, 200))
//            ->header('Content-Type', Storage::mimeType('userDocuments/1/dr anders.PNG'));

        return $base64;

//        return (new Response($file, 200))
//            ->header('Content-Type', Storage::mimeType($fileName));
    }


}

?>