<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use Laravel\Lumen\Application;

//use Illuminate\Support\Facades\Event;
//Event::listen('illuminate.query', function($sql, $params) { echo($sql); var_dump($params); });

$app->get('/', 'ApiaryController@index');

$app->get('/documentation.apib', 'ApiaryController@getApiaryDocumentation');

$app->get('timezones', 'TimezoneController@getAll');
$app->get('countries', 'CountriesController@getAll');

$app->group(['prefix' => 'users', 'namespace' => 'App\Http\Controllers'], function (Application $app) {
    $app->get('/', ['uses' => 'UserController@getAllPaginated']);
    $app->get('{id}', ['uses' => 'UserController@getOne', 'as' => App\Models\User::class]);
    $app->put('{id}', ['uses' => 'UserController@putOne']);
    $app->patch('{id}', ['uses' => 'UserController@patchOne']);
    $app->delete('{id}', ['uses' => 'UserController@deleteOne']);

    $app->get('/{id}/roles', 'PermissionsController@getAll');
    $app->put('/{id}/roles', 'PermissionsController@putManyReplace');

    $app->get('{id}/profile', ['uses' => 'UserProfileController@getOne', 'as' => App\Models\UserProfile::class]);
    $app->put('{id}/profile', ['uses' => 'UserProfileController@putOne']);
    $app->patch('{id}/profile', ['uses' => 'UserProfileController@patchOne']);

    $app->get('{id}/credentials', ['uses' => 'UserCredentialController@getOne', 'as' => App\Models\UserCredential::class]);
    $app->put('{id}/credentials', ['uses' => 'UserCredentialController@putOne']);
    $app->patch('{id}/credentials', ['uses' => 'UserCredentialController@patchOne']);
    $app->delete('{id}/credentials', ['uses' => 'UserCredentialController@deleteOne']);

    $app->delete('{email}/password', ['uses' => 'UserController@resetPassword']);

    $app->delete('{id}/socialLogin/{provider}', ['uses' => 'UserController@unlinkSocialLogin']);
});

$app->group(['prefix' => 'roles', 'namespace' => 'App\Http\Controllers'], function (Application $app) {
    $app->get('/', ['uses' => 'RoleController@getAll']);
});

$app->group(['prefix' => 'articles'], function (Application $app) {

    $app->get('/tag-categories', 'App\Http\Controllers\ArticleController@getAllTagCategories');

    $app->get('/', 'App\Http\Controllers\ArticleController@getAllPaginated');
    $app->get('{id}', ['as' => \App\Models\Article::class, 'uses' => 'App\Http\Controllers\ArticleController@getOne']);
    $app->post('/', 'App\Http\Controllers\ArticleController@postOne');
    $app->put('{id}', 'App\Http\Controllers\ArticleController@putOne');
    $app->patch('{id}', 'App\Http\Controllers\ArticleController@patchOne');
    $app->delete('{id}', 'App\Http\Controllers\ArticleController@deleteOne');

    $app->get('{id}/localizations', 'App\Http\Controllers\ArticleController@getAllLocalizations');
    $app->get('{id}/localizations/{region}', 'App\Http\Controllers\ArticleController@getOneLocalization');
    $app->put('{id}/localizations/{region}', 'App\Http\Controllers\ArticleController@putOneLocalization');

    $app->get('{id}/permalinks', 'App\Http\Controllers\ArticlePermalinkController@getAll');

    $app->get('{id}/meta', 'App\Http\Controllers\ArticleMetaController@getAll');
    $app->put('{id}/meta', 'App\Http\Controllers\ArticleMetaController@putManyAdd');
    $app->delete('{id}/meta/{childId}', 'App\Http\Controllers\ArticleMetaController@deleteOne');

    $app->get('{id}/comments', 'App\Http\Controllers\ArticleCommentController@getAll');
    $app->post('{id}/comments', 'App\Http\Controllers\ArticleCommentController@postOne');

    $app->get('{id}/tags', 'App\Http\Controllers\ArticleTagController@getAll');
    $app->put('{id}/tags', 'App\Http\Controllers\ArticleTagController@putManyReplace');

    $app->get('{id}/sections', 'App\Http\Controllers\ArticleSectionController@getAll');
    $app->put('{id}/sections', 'App\Http\Controllers\ArticleSectionController@putManyAdd');
    $app->delete('{id}/sections', 'App\Http\Controllers\ArticleSectionController@deleteMany');
    $app->delete('{id}/sections/{childId}', 'App\Http\Controllers\ArticleSectionController@deleteOne');

    $app->get('{id}/article-images', 'App\Http\Controllers\ArticleImageController@getAll');
    $app->put('{id}/article-images', 'App\Http\Controllers\ArticleImageController@putManyAdd');
    $app->delete('{id}/article-images', 'App\Http\Controllers\ArticleImageController@deleteMany');
});

$app->group(['prefix' => 'tags'], function (Application $app) {
    $app->get('/', ['uses' => 'App\Http\Controllers\TagController@getAllPaginated', 'as' => \App\Models\Tag::class]);
    $app->get('/group/{group}', ['as' => \App\Models\Tag::class, 'uses' => 'App\Http\Controllers\TagController@getGroupTags']);
    $app->get('{id}', ['as' => \App\Models\Tag::class, 'uses' => 'App\Http\Controllers\TagController@getOne']);
    $app->post('/', 'App\Http\Controllers\TagController@postOne');
    $app->patch('{id}', 'App\Http\Controllers\TagController@patchOne');
    $app->delete('{id}', 'App\Http\Controllers\TagController@deleteOne');
    $app->put('{id}/child-tags', 'App\Http\Controllers\ChildTagController@putManyReplace');
});

$app->group(['prefix' => 'images'], function (Application $app) {
    $app->get('/', 'App\Http\Controllers\ImageController@getAllPaginated');
    $app->get('{id}', ['as' => \App\Models\Image::class, 'uses' => 'App\Http\Controllers\ImageController@getOne']);
    $app->put('{id}', 'App\Http\Controllers\ImageController@putOne');
    $app->patch('{id}', 'App\Http\Controllers\ImageController@patchOne');
    $app->delete('{id}', 'App\Http\Controllers\ImageController@deleteOne');
});

$app->group(['prefix' => 'test'], function (Application $app) {

    $app->get('/internal-exception', 'App\Http\Controllers\TestController@internalException');
    $app->get('/fatal-error', 'App\Http\Controllers\TestController@fatalError');

    $app->get('/entities', 'App\Http\Controllers\TestController@getAll');
    $app->get('/entities/pages', 'App\Http\Controllers\TestController@getAllPaginated');
    $app->get('/entities_encoded/{id}', 'App\Http\Controllers\TestController@urlEncode');
    $app->get('/entities/{id}', ['as' => \App\Models\TestEntity::class, 'uses' => 'App\Http\Controllers\TestController@getOne']);
    $app->get('/entities-second/{id}', ['as' => \App\Models\SecondTestEntity::class, 'uses' => 'App\Http\Controllers\TestController@getOne']);
    $app->post('/entities', 'App\Http\Controllers\TestController@postOne');
    $app->put('/entities/{id}', 'App\Http\Controllers\TestController@putOne');
    $app->put('/entities', 'App\Http\Controllers\TestController@putMany');
    $app->patch('/entities/{id}', 'App\Http\Controllers\TestController@patchOne');
    $app->patch('/entities', 'App\Http\Controllers\TestController@patchMany');
    $app->delete('/entities/{id}', 'App\Http\Controllers\TestController@deleteOne');
    $app->delete('/entities', 'App\Http\Controllers\TestController@deleteMany');

    $app->get('/entities/{id}/localizations', 'App\Http\Controllers\TestController@getAllLocalizations');
    $app->get('/entities/{id}/localizations/{region}', 'App\Http\Controllers\TestController@getOneLocalization');
    $app->put('/entities/{id}/localizations/{region}', 'App\Http\Controllers\TestController@putOneLocalization');

    $app->get('/entities/{id}/children', 'App\Http\Controllers\ChildTestController@getAll');
    $app->get('/entities/{id}/child/{childId}', 'App\Http\Controllers\ChildTestController@getOne');
    $app->post('/entities/{id}/child', 'App\Http\Controllers\ChildTestController@postOne');
    $app->put('/entities/{id}/child/{childId}', 'App\Http\Controllers\ChildTestController@putOne');
    $app->put('/entities/{id}/children', 'App\Http\Controllers\ChildTestController@putManyAdd');
    $app->patch('/entities/{id}/child/{childId}', 'App\Http\Controllers\ChildTestController@patchOne');
    $app->patch('/entities/{id}/children', 'App\Http\Controllers\ChildTestController@patchMany');
    $app->delete('/entities/{id}/child/{childId}', 'App\Http\Controllers\ChildTestController@deleteOne');
    $app->delete('/entities/{id}/children', 'App\Http\Controllers\ChildTestController@deleteMany');
});

$app->group(['prefix' => 'auth', 'namespace' => 'App\Http\Controllers'], function (Application $app) {
    $app->get('jwt/login', 'AuthController@login');
    $app->get('jwt/refresh', 'AuthController@refresh');
    $app->get('jwt/token', 'AuthController@token');
    $app->get('jwt/user/{userId}', 'AuthController@loginAsUser');

    $app->get('social/{provider}', 'AuthController@redirectToProvider');
    $app->get('social/{provider}/callback', 'AuthController@handleProviderCallback');

    $app->get('sso/{requester}', 'AuthController@singleSignOn');
});

$app->group(['prefix' => 'cloudinary', 'namespace' => 'App\Http\Controllers'], function (Application $app) {
    $app->get('signature', 'CloudinaryController@getSignature');
});
