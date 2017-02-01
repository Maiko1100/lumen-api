<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Application;
use Illuminate\Http\JsonResponse;

class APIController extends Controller
{
    /**
     * Get root url.
     *
     * @return \Illuminate\Http\Response dit is een test commit
     */
    public function getIndex(Application $app)
    {
        return new JsonResponse(['message' => $app->version()]);
    }

    public function getBooks()
    {
        return new JsonResponse(['message' => 'jeej het werkt']);
    }

}
