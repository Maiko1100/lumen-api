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
        $file = Storage::get('userDocuments/1/Final Assignment Data Processing.pdf');
        $user = JWTAuth::parseToken()->authenticate();

        return (new Response($file, 200))
            ->header('Content-Type', Storage::mimeType('userDocuments/1/Final Assignment Data Processing.pdf'));
    }


}

?>