<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserFileController extends Controller
{
    public function getFiles() {

        $user = JWTAuth::parseToken()->authenticate();

        $files = $user->getUserFiles();

            return $files;
        }
}

?>