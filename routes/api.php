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
    $api->post('/user/reset/', [
        'as' => 'api.user.reset',
        'uses' => 'App\Http\Controllers\UserController@createResetLink',
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
        $api->get('/files/taxreturn', [
            'uses' => 'App\Http\Controllers\UserFileController@getTaxReturnFiles',
            'as' => 'api.files.taxreturn'
        ]);
        $api->get('/files/taxreturn/useryear', [
            'uses' => 'App\Http\Controllers\UserFileController@getUserYearTaxReturnFiles',
            'as' => 'api.files.taxreturn'
        ]);
        $api->get('/files/case', [
            'uses' => 'App\Http\Controllers\UserFileController@getCaseFiles',
            'as' => 'api.files.case'
        ]);
        $api->post('/file/get', [
            'uses' => 'App\Http\Controllers\UserFileController@getFile',
            'as' => 'api.file.get'
        ]);
        $api->post('/file/delete', [
            'uses' => 'App\Http\Controllers\UserFileController@deleteFile',
            'as' => 'api.file.delete'
        ]);
        $api->post('/file/save', [
            'uses' => 'App\Http\Controllers\UserFileController@saveFile',
            'as' => 'api.file.save'
        ]);
        $api->get('/report/get', [
            'uses' => 'App\Http\Controllers\UserFileController@getReport',
            'as' => 'api.file.get'
        ]);
        $api->post('/report/save', [
            'uses' => 'App\Http\Controllers\UserFileController@saveReport',
            'as' => 'api.file.save'
        ]);
        $api->get('/finalTaxAssessment/get', [
            'uses' => 'App\Http\Controllers\UserFileController@getFinalTaxAssessment',
            'as' => 'api.file.get'
        ]);
        $api->post('/finalTaxAssessment/save', [
            'uses' => 'App\Http\Controllers\UserFileController@saveFinalTaxAssessment',
            'as' => 'api.file.save'
        ]);
        $api->get('/preliminary/get', [
            'uses' => 'App\Http\Controllers\UserFileController@getPreliminaryTax',
            'as' => 'api.file.get'
        ]);
        $api->post('/preliminary/save', [
            'uses' => 'App\Http\Controllers\UserFileController@savePreliminaryTax',
            'as' => 'api.file.save'
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
        $api->post('/userYear/create', [
            'uses' => 'App\Http\Controllers\UserYearController@create',
            'as' => 'api.useryear.create'
        ]);
        $api->post('/useryear/report/agree', [
            'uses' => 'App\Http\Controllers\UserYearController@reportAgreed',
            'as' => 'api.useryear.create'
        ]);
        $api->post('/useryear/status/change', [
            'uses' => 'App\Http\Controllers\UserYearController@changeStatus',
            'as' => 'api.useryear.status.change'
        ]);
        $api->get('/useryear/change/partner', [
            'uses' => 'App\Http\Controllers\UserYearController@changePartner',
            'as' => 'api.useryear.status.change'
        ]);
    });
    $api->get('/calendar/meeting/get', [
        'uses' => 'App\Http\Controllers\gCalendarController@getMeetings',
        'as' => 'api.oath'
    ]);
    $api->post('/calendar/meeting/update', [
        'uses' => 'App\Http\Controllers\gCalendarController@updateMeeting',
        'as' => 'api.oath'
    ]);

    $api->group([
        'middleware' => 'api.auth',
    ], function ($api) {
        $api->get('/calendar/getEvents', [
            'uses' => 'App\Http\Controllers\gCalendarController@index',
            'as' => 'api.calendar.events'
        ]);
    });

    $api->get('/group/questions', [
        'uses' => 'App\Http\Controllers\QuestionController@getQuestionsByGroup',
        'as' => 'api.questioncontroller'
    ]);
    $api->post('/group/questions/sort', [
        'uses' => 'App\Http\Controllers\QuestionController@setQuestionsGroupSort',
        'as' => 'api.group.questions.sort'
    ]);

    $api->group([
        'middleware' => 'api.auth',
    ], function ($api) {
        $api->post('/question', [
            'uses' => 'App\Http\Controllers\QuestionController@getQuestions',
            'as' => 'api.questioncontroller'
        ]);

        $api->post('/question/30', [
            'uses' => 'App\Http\Controllers\QuestionController@get30',
            'as' => 'api.question.30'
        ]);

        $api->post('/question/profile', [
            'uses' => 'App\Http\Controllers\QuestionController@getProfileQuestions',
            'as' => 'api.questioncontroller'
        ]);

        $api->post('/question/save', [
            'uses' => 'App\Http\Controllers\UserQuestionController@save',
            'as' => 'api.userquestion.save'
        ]);

        $api->post('/question/file/save', [
            'uses' => 'App\Http\Controllers\UserFileController@saveQuestionFile',
            'as' => 'api.question.file.save'
        ]);
        $api->post('/question/file/delete', [
            'uses' => 'App\Http\Controllers\UserFileController@deleteQuestionFile',
            'as' => 'api.question.file.delete'
        ]);

        $api->post('/question/feedback/save', [
            'uses' => 'App\Http\Controllers\FeedbackController@saveQuestionFeedBack',
            'as' => 'api.question.file.save'
        ]);
    });
    $api->group([
        'middleware' => 'api.auth',
    ], function ($api) {
        $api->post('/child/save', [
            'uses' => 'App\Http\Controllers\ChildController@saveChild',
            'as' => 'api.ChildController.save'
        ]);
    });

    $api->group([
        'middleware' => 'api.auth',
    ], function ($api) {
        $api->post('/questionplus/create', [
            'uses' => 'App\Http\Controllers\QuestionPlusController@create',
            'as' => 'api.QuestionPlusController.create'
        ]);
        $api->post('/questionplus/delete', [
            'uses' => 'App\Http\Controllers\QuestionPlusController@delete',
            'as' => 'api.QuestionPlusController.delete'
        ]);
    });

    $api->group([
        'middleware' => 'api.auth',
    ], function ($api) {
        $api->post('/taxrulingstate/change', [
            'uses' => 'App\Http\Controllers\UserController@changeTaxRulingState',
            'as' => 'api.UserController.changeTaxRulingState'
        ]);
    });

    $api->group([
        'middleware' => 'api.auth',
    ], function ($api) {
        $api->post('/partner/save', [
            'uses' => 'App\Http\Controllers\PartnerController@savePartner',
            'as' => 'api.PartnerController.save'
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

    $api->post('/order/create', [
        'uses' => 'App\Http\Controllers\OrderController@createOrder',
        'as' => 'api.order.create'
    ]);
    $api->get('/payment/get', [
        'uses' => 'App\Http\Controllers\OrderController@getPayment',
        'as' => 'api.payment.get'
    ]);

    $api->post('/mollie-webhook', [
        'uses' => 'App\Http\Controllers\OrderController@webhook',
        'as' => 'api.order.webhook'
    ]);

    $api->post('/user/resetPasswordWithToken', [
        'uses' => 'App\Http\Controllers\UserController@resetPasswordWithToken',
        'as' => 'api.user.resetPassword'
    ]);

    $api->group([
        'middleware' => 'api.auth',
    ], function ($api) {
        $api->get('/customers', [
            'uses' => 'App\Http\Controllers\UserController@getAllCustomers',
            'as' => 'api.users.customers'
        ]);
        $api->post('/customers/delete', [
            'uses' => 'App\Http\Controllers\UserController@deleteUser',
            'as' => 'api.users.customers.delete'
        ]);
        $api->get('/users/employees', [
            'uses' => 'App\Http\Controllers\UserController@getAllEmployees',
            'as' => 'api.users.users.employees'
        ]);
        $api->post('/users/employees/assign', [
            'uses' => 'App\Http\Controllers\UserYearController@assignEmployee',
            'as' => 'api.users.users.employees.assign'
        ]);
        $api->get('/customers/cases', [
            'uses' => 'App\Http\Controllers\UserController@getAllCases',
            'as' => 'api.users.customers.cases'
        ]);
        $api->get('/customers/case', [
            'uses' => 'App\Http\Controllers\UserController@getCaseAndUser',
            'as' => 'api.users.customers.case'
        ]);
        $api->post('/user/changePassword', [
            'uses' => 'App\Http\Controllers\UserController@updateUserPassword',
            'as' => 'api.user.changePassword'
        ]);
        $api->get('/customers/profileData', [
            'uses' => 'App\Http\Controllers\UserQuestionController@getProfileData',
            'as' => 'api.userquestion.profiledata'
        ]);
    });

    $api->group([
        'middleware' => 'api.auth',
    ], function ($api) {
        $api->get('/appointment', [
            'uses' => 'App\Http\Controllers\AppointmentController@getUserAppointment',
            'as' => 'api.users.customers'
        ]);
    });
    $api->group([
        'middleware' => 'api.auth',
    ], function ($api) {
        $api->get('/discount/get', [
            'uses' => 'App\Http\Controllers\DiscountController@getDiscount',
            'as' => 'api.discount.get'
        ]);
    });

    $api->group([
        'middleware' => 'api.auth',
    ], function ($api) {
        $api->get('/discount/all', [
            'uses' => 'App\Http\Controllers\DiscountController@getDiscounts',
            'as' => 'api.discount.all'
        ]);
        $api->post('/discount/add', [
            'uses' => 'App\Http\Controllers\DiscountController@addDiscount',
            'as' => 'api.discount.add'
        ]);
        $api->post('/discount/delete', [
            'uses' => 'App\Http\Controllers\DiscountController@deleteDiscount',
            'as' => 'api.discount.delete'
        ]);
    });

    $api->post('/question/clone', [
        'uses' => 'App\Http\Controllers\QuestionGeneratorController@cloneYearQuestions',
        'as' => 'api.questiongenerator.clone'
    ]);

    $api->post('/users/employees/add', [
        'uses' => 'App\Http\Controllers\UserController@addEmployee',
        'as' => 'api.users.users.employees.add'
    ]);

    $api->post('/testmail', [
        'uses' => 'App\Http\Controllers\MailController@testMail',
        'as' => 'api.mail.test'
    ]);
});
