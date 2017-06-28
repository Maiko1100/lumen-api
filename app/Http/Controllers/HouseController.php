<?php

namespace App\Http\Controllers;

use App\house;
use Dingo\Api\Contract\Http\Request;
use Laravel\Lumen\Application;
use Illuminate\Http\JsonResponse;
use Intervention\Image\Facades\Image as Image;
use Illuminate\Support\Facades\File;
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

        $date = date('his', time());
        $housepictures = $request->input('pictures');

        //dd($request->all());



        foreach($housepictures as $housepicture ) {
            $img = Image::make($housepicture['preview']);
            var_dump($img);
            exit;
//            $imageName = $housepicture['name'];
            $imageName = 'testestest.jpg';


            dump($housepicture);
            exit;

            if (File::exists('images/houseImages/'.$imageName)){
                $imageName = $date . $imageName;
            }

//
//            $img = Image::make( 'images/houseImages/' . $imageName);
//            $img->resize(1920, 1280);
//            $img->save('images/houseImages/' . $imageName);

        }


//        $book->author = $request->input('author');
//        $book->year = $request->input('year');
//        $book->save();
//
//        $books = Book::get(a


        return new JsonResponse(['pictures' => $housepictures]);
    }

    public function deleteHouse(Request $request)
    {

        $book = Book::find($request->input('id'));

        $book->delete();

        $books = Book::get();


        return new JsonResponse(['books' => $books]);
    }

}
