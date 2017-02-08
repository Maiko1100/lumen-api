<?php

namespace App\Http\Controllers;

use App\house;
use Dingo\Api\Contract\Http\Request;
use Laravel\Lumen\Application;
use Illuminate\Http\JsonResponse;
use App\Book as Book;

class HouseController extends Controller
{

    public function getHouses()
    {
        $houses = house::get();

        return new JsonResponse(['houses' => $houses]);
    }

    public function addHouse(Request $request)
    {

        $housepictures = $request->input('pictures');
        var_dump($housepictures);
        exit;


        $book->author = $request->input('author');
        $book->year = $request->input('year');
        $book->save();

        $books = Book::get();


        return new JsonResponse(['books' => $books]);
    }

    public function deleteHouse(Request $request)
    {

        $book = Book::find($request->input('id'));

        $book->delete();

        $books = Book::get();


        return new JsonResponse(['books' => $books]);
    }

}
