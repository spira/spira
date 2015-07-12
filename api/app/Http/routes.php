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

$app->get('/', function () use ($app) {

    return view('documentation.layouts.master', [
        'apibUrl' => '/documentation.apib',
    ]);

});

$app->get('/documentation.apib', function (Request $request) use ($app) {

    $app->view->addExtension('blade.apib', 'blade'); //allow sections to be defined as .blade.apib for correct syntax highlighting

    return view('documentation.apiary', [
        'apiUrl' => $request->root(),
        'faker'  => Faker\Factory::create(),
    ]);

});

$app->get('timezones', function () { return App\Models\Timezones::getTimezones(); });

$app->group(['prefix' => 'users', 'namespace' => 'App\Http\Controllers'], function ($app) {
    $app->get('/', ['middleware' => 'permission:readAll,users', 'uses' => 'UserController@getAll']);
    $app->get('{id}', ['middleware' => 'permission:readOne,users', 'uses' => 'UserController@getOne']);
    $app->put('{id}', ['uses' => 'UserController@putOne']);
    $app->patch('{id}', ['middleware' => 'permission:update,users', 'uses' => 'UserController@patchOne']);
    $app->delete('{id}', ['middleware' => 'permission:delete,users', 'uses' => 'UserController@deleteOne']);
});

$app->group(['prefix' => 'test'], function ($app) {

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

$app->group(['prefix' => 'auth', 'namespace' => 'App\Http\Controllers'], function ($app) {
    $app->get('jwt/login', 'AuthController@login');
    $app->get('jwt/refresh', 'AuthController@refresh');
    $app->get('jwt/token', 'AuthController@token');
});
