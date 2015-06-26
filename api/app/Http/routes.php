<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

use Illuminate\Http\Request;

$app->get('/', function() use ($app) {

    return view('documentation.layouts.master', [
        'apibUrl' => '/documentation.apib'
    ]);

});

$app->get('/documentation.apib', function(Request $request) use ($app) {

    $app->view->addExtension('blade.apib', 'blade'); //allow sections to be defined as .blade.apib for correct syntax highlighting

    return view('documentation.apiary', [
        'apiUrl' => $request->root()
    ]);

});

$app->group(['prefix' => 'users'], function($app){

    $app->get('/', 'App\Http\Controllers\UserController@getAll');

    $app->get('/{id}', 'App\Http\Controllers\UserController@getOne');

});


$app->group(['prefix' => 'test'], function($app){

    $app->get('/internal-exception', 'App\Http\Controllers\TestController@internalException');
    $app->get('/fatal-error', 'App\Http\Controllers\TestController@fatalError');

    $app->get('/entities', 'App\Http\Controllers\TestController@getAll');
    $app->get('/entities/{id}', 'App\Http\Controllers\TestController@getOne');
    $app->post('/entities', 'App\Http\Controllers\TestController@postOne');
    $app->put('/entities/{id}', 'App\Http\Controllers\TestController@putOne');
    $app->put('/entities', 'App\Http\Controllers\TestController@putMany');
    $app->patch('/entities/{id}', 'App\Http\Controllers\TestController@patchOne');
    $app->patch('/entities', 'App\Http\Controllers\TestController@patchMany');
    $app->delete('/entities/{id}', 'App\Http\Controllers\TestController@deleteOne');
    $app->delete('/entities', 'App\Http\Controllers\TestController@deleteMany');
});
