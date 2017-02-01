<?php

namespace App\Http\Controllers;

use Dingo\Api\Contract\Http\Request;
use Laravel\Lumen\Application;
use Illuminate\Http\JsonResponse;
use App\Book as Book;

class BookController extends Controller
{

    public function getBooks()
    {
        $books = Book::get();

        return new JsonResponse(['books' => $books]);
    }

    public function addBook(Request $request)
    {

        $book = new Book();

        $book->name = $request->input('name');
        $book->author = $request->input('author');
        $book->year = $request->input('year');
        $book->save();

        $books = Book::get();


        return new JsonResponse(['books' => $books]);
    }

    public function deleteBook(Request $request)
    {

        $book = Book::find($request->input('id'));

        $book->delete();

        $books = Book::get();


        return new JsonResponse(['books' => $books]);
    }

}
