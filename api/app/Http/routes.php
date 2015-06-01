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

$app->get('/', function() use ($app) {
    return $app->welcome();
});

$app->group(['prefix' => 'users'], function($app){

    $app->get('/', 'App\Http\Controllers\UserController@getAll');

    $app->get('/{id}', 'App\Http\Controllers\UserController@getOne');

});


$app->group(['prefix' => 'test'], function($app){

    $app->post('/email', 'App\Http\Controllers\TestController@email');

    $app->get('/queue', 'App\Http\Controllers\TestController@queue');

    $app->get('/internal-exception', 'App\Http\Controllers\TestController@internalException');
    $app->get('/fatal-error', 'App\Http\Controllers\TestController@fatalError');

    $app->put('/cache/{key}', 'App\Http\Controllers\TestController@addToCache');
    $app->get('/cache/{key}', 'App\Http\Controllers\TestController@getFromCache');

    $app->post('/logs', 'App\Http\Controllers\TestController@postLogs');


    $app->get('/entities', 'App\Http\Controllers\TestController@getAll');

    $app->get('/entities/{id}', 'App\Http\Controllers\TestController@getOne');

});