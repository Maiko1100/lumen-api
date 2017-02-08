<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$api = $app->make(Dingo\Api\Routing\Router::class);

$api->version('v1', function ($api) {

    $api->post('/auth/login', [
        'as' => 'api.auth.login',
        'uses' => 'App\Http\Controllers\Auth\AuthController@postLogin',
    ]);

    $api->group([
        'middleware' => 'api.auth',
    ], function ($api) {
        $api->get('/', [
            'uses' => 'App\Http\Controllers\APIController@getIndex',
            'as' => 'api.index'
        ]);
        $api->get('/auth/user', [
            'uses' => 'App\Http\Controllers\Auth\AuthController@getUser',
            'as' => 'api.auth.user'
        ]);
        $api->patch('/auth/refresh', [
            'uses' => 'App\Http\Controllers\Auth\AuthController@patchRefresh',
            'as' => 'api.auth.refresh'
        ]);
        $api->delete('/auth/invalidate', [
            'uses' => 'App\Http\Controllers\Auth\AuthController@deleteInvalidate',
            'as' => 'api.auth.invalidate'
        ]);
    });

    $api->group([
        'middleware' => 'api.auth',
    ], function ($api) {
        $api->get('/books', [
            'uses' => 'App\Http\Controllers\BookController@getBooks',
            'as' => 'api.books'
        ]);
        $api->post('/books/add', [
            'uses' => 'App\Http\Controllers\BookController@addBook',
            'as' => 'api.books.add'
        ]);
        $api->post('/book/delete', [
            'uses' => 'App\Http\Controllers\BookController@deleteBook',
            'as' => 'api.book.refresh'
        ]);
//        $api->delete('/auth/invalidate', [
//            'uses' => 'App\Http\Controllers\Auth\AuthController@deleteInvalidate',
//            'as' => 'api.auth.invalidate'
//        ]);
    });
    $api->group([
        'middleware' => 'api.auth',
    ], function ($api) {
        $api->get('/houses', [
            'uses' => 'App\Http\Controllers\HouseController@getHouses',
            'as' => 'api.books'
        ]);
        $api->post('/houses/add', [
            'uses' => 'App\Http\Controllers\HouseController@addHouse',
            'as' => 'api.books.add'
        ]);
        $api->post('/house/delete', [
            'uses' => 'App\Http\Controllers\BookController@deleteBook',
            'as' => 'api.book.refresh'
        ]);
//        $api->delete('/auth/invalidate', [
//            'uses' => 'App\Http\Controllers\Auth\AuthController@deleteInvalidate',
//            'as' => 'api.auth.invalidate'
//        ]);
    });

});
