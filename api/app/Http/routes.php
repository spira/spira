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
use Laravel\Lumen\Application;

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

$app->get('timezones', 'TimezoneController@getAll');
$app->get('countries', 'CountriesController@getAll');

$app->group(['prefix' => 'users', 'namespace' => 'App\Http\Controllers'], function (Application $app) {
    $app->get('/', ['uses' => 'UserController@getAll', 'as' => App\Models\User::class]);
    $app->get('{id}', ['uses' => 'UserController@getOne', 'as' => App\Models\User::class]);
    $app->put('{id}', ['uses' => 'UserController@putOne']);
    $app->patch('{id}', ['uses' => 'UserController@patchOne']);
    $app->delete('{id}', ['uses' => 'UserController@deleteOne']);
});


$app->group(['prefix' => 'test'], function (Application $app) {

    $app->get('/internal-exception', 'App\Http\Controllers\TestController@internalException');
    $app->get('/fatal-error', 'App\Http\Controllers\TestController@fatalError');

    $app->get('/entities', 'App\Http\Controllers\TestController@getAll');
    $app->get('/entities/{id}', ['as'=>\App\Models\TestEntity::class, 'uses'=>'App\Http\Controllers\TestController@getOne']);
    $app->get('/entities-second/{id}', ['as'=>\App\Models\SecondTestEntity::class, 'uses'=>'App\Http\Controllers\TestController@getOne']);
    $app->post('/entities', 'App\Http\Controllers\TestController@postOne');
    $app->put('/entities/{id}', 'App\Http\Controllers\TestController@putOne');
    $app->put('/entities', 'App\Http\Controllers\TestController@putMany');
    $app->patch('/entities/{id}', 'App\Http\Controllers\TestController@patchOne');
    $app->patch('/entities', 'App\Http\Controllers\TestController@patchMany');
    $app->delete('/entities/{id}', 'App\Http\Controllers\TestController@deleteOne');
    $app->delete('/entities', 'App\Http\Controllers\TestController@deleteMany');
});

$app->group(['prefix' => 'auth', 'namespace' => 'App\Http\Controllers'], function (Application $app) {
    $app->get('jwt/login', 'AuthController@login');
    $app->get('jwt/refresh', 'AuthController@refresh');
    $app->get('jwt/token', 'AuthController@token');


    //@todo implement proper social logins
    $app->get('social/facebook', function(Request $request){
        echo "Dummy Facebook request:\n";
        return $request;
    });
    $app->get('social/twitter', function(Request $request){
        echo "Dummy Twitter request:\n";
        return $request;
    });
    $app->get('social/google', function(Request $request){
        echo "Dummy Google request:\n";
        return $request;
    });

});
