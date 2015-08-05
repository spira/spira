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

use Laravel\Lumen\Application;

$app->get('/', 'ApiaryController@index');

$app->get('/documentation.apib', 'ApiaryController@getApiaryDocumentation');

$app->get('timezones', 'TimezoneController@getAll');
$app->get('countries', 'CountriesController@getAll');

$app->group(['prefix' => 'users', 'namespace' => 'App\Http\Controllers'], function (Application $app) {
    $app->get('/', ['uses' => 'UserController@getAll', 'as' => App\Models\User::class]);
    $app->get('{id}', ['uses' => 'UserController@getOne', 'as' => App\Models\User::class]);
    $app->put('{id}', ['uses' => 'UserController@putOne']);
    $app->patch('{id}', ['uses' => 'UserController@patchOne']);
    $app->delete('{id}', ['uses' => 'UserController@deleteOne']);
    $app->delete('{id}/password', ['uses' => 'UserController@resetPassword']);
});


$app->group(['prefix' => 'articles'], function (Application $app) {
    $app->get('/', 'App\Http\Controllers\ArticleController@getAllPaginated');
    $app->get('{id}', ['as'=>\App\Models\Article::class, 'uses'=>'App\Http\Controllers\ArticleController@getOne']);
    $app->post('/', 'App\Http\Controllers\ArticleController@postOne');
    $app->put('{id}', 'App\Http\Controllers\ArticleController@putOne');
    $app->patch('{id}', 'App\Http\Controllers\ArticleController@patchOne');
    $app->delete('{id}', 'App\Http\Controllers\ArticleController@deleteOne');

    $app->get('{id}/permalinks', 'App\Http\Controllers\ArticlePermalinkController@getAll');

    $app->get('{id}/meta', 'App\Http\Controllers\ArticleMetaController@getAll');
    $app->put('{id}/meta', 'App\Http\Controllers\ArticleMetaController@putMany');
    $app->delete('{id}/meta/{childId}', 'App\Http\Controllers\ArticleMetaController@deleteOne');
});

$app->group(['prefix' => 'test'], function (Application $app) {

    $app->get('/internal-exception', 'App\Http\Controllers\TestController@internalException');
    $app->get('/fatal-error', 'App\Http\Controllers\TestController@fatalError');

    $app->get('/entities', 'App\Http\Controllers\TestController@getAll');
    $app->get('/entities/pages', 'App\Http\Controllers\TestController@getAllPaginated');
    $app->get('/entities_encoded/{id}', 'App\Http\Controllers\TestController@urlEncode');
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

    $app->get('social/{provider}', 'AuthController@redirectToProvider');
    $app->get('social/{provider}/callback', 'AuthController@handleProviderCallback');
});
