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

    $api->get('/years', [
        'uses' => 'App\Http\Controllers\YearController@getYears',
        'as' => 'api.yearcontroller'
    ]);

    $api->post('/auth/login', [
        'as' => 'api.auth.login',
        'uses' => 'App\Http\Controllers\Auth\AuthController@postLogin',
    ]);
    $api->post('/user/create', [
        'as' => 'api.user.create',
        'uses' => 'App\Http\Controllers\UserController@addUser',
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
        $api->get('/files', [
            'uses' => 'App\Http\Controllers\UserFileController@getFiles',
            'as' => 'api.files'
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
//
    $api->group([
        'middleware' => 'api.auth',
    ], function ($api) {
        $api->get('/userYears', [
            'uses' => 'App\Http\Controllers\UserYearController@getUserYears',
            'as' => 'api.useryears'
        ]);
        $api->get('/userYear', [
            'uses' => 'App\Http\Controllers\UserYearController@getUserYear',
            'as' => 'api.useryear'
        ]);
    });

    $api->group([
        'middleware' => 'api.auth',
    ], function ($api) {
        $api->get('/question', [
            'uses' => 'App\Http\Controllers\QuestionController@getQuestions',
            'as' => 'api.questioncontroller'
        ]);

        $api->post('/question/save', [
            'uses' => 'App\Http\Controllers\QuestionController@saveQuestion',
            'as' => 'api.questioncontroller'
        ]);
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

    $api->post('/pay', [
        'uses' => 'App\Http\Controllers\OrderController@create',
        'as' => 'api.order.create'
    ]);

    $api->post('/mollie-webhook', [
        'uses' => 'App\Http\Controllers\OrderController@webhook',
        'as' => 'api.order.webhook'
    ]);

});
