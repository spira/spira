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

$app->group(['namespace' => 'App\Http\Controllers', 'middleware' => 'requireAuthorization'], function (Application $app) {

    $app->get('auth/jwt/user/{userId}', 'AuthController@loginAsUser');

    $app->get('users/', ['uses' => 'UserController@getAllPaginated']);
    $app->get('users/{id}', ['uses' => 'UserController@getOne', 'as' => App\Models\User::class]);
    $app->put('users/{id}', ['uses' => 'UserController@putOne']);
    $app->patch('users/{id}', ['uses' => 'UserController@patchOne']);
    $app->delete('users/{id}', ['uses' => 'UserController@deleteOne']);
    $app->get('users/{id}/roles', ['uses' => 'PermissionsController@getAll', ]);
    $app->put('users/{id}/roles', ['uses' => 'PermissionsController@putManyReplace']);
    $app->get('users/{id}/profile', ['uses' => 'UserProfileController@getOne', 'as' => App\Models\UserProfile::class]);
    $app->put('users/{id}/profile', ['uses' => 'UserProfileController@putOne']);
    $app->patch('users/{id}/profile', ['uses' => 'UserProfileController@patchOne']);
    $app->get('users/{id}/credentials', ['uses' => 'UserCredentialController@getOne', 'as' => App\Models\UserCredential::class]);
    $app->put('users/{id}/credentials', ['uses' => 'UserCredentialController@putOne']);
    $app->patch('users/{id}/credentials', ['uses' => 'UserCredentialController@patchOne']);
    $app->delete('users/{id}/credentials', ['uses' => 'UserCredentialController@deleteOne']);
    $app->delete('users/{id}/socialLogin/{provider}', ['uses' => 'UserController@unlinkSocialLogin']);

    $app->get('roles/', ['uses' => 'RoleController@getAll']);

    $app->post('articles/', 'ArticleController@postOne');
    $app->put('articles/{id}', 'ArticleController@putOne');
    $app->patch('articles/{id}', 'ArticleController@patchOne');
    $app->delete('articles/{id}', 'ArticleController@deleteOne');
    $app->put('articles/{id}/localizations/{region}', 'ArticleController@putOneLocalization');
    $app->put('articles/{id}/meta', 'ArticleMetaController@putManyAdd');
    $app->delete('articles/{id}/meta/{childId}', 'ArticleMetaController@deleteOne');
    $app->post('articles/{id}/comments', 'ArticleCommentController@postOne');
    $app->put('articles/{id}/tags', 'ArticleTagController@putManyReplace');
    $app->put('articles/{id}/sections', 'ArticleSectionController@putManyAdd');
    $app->delete('articles/{id}/sections', 'ArticleSectionController@deleteMany');
    $app->delete('articles/{id}/sections/{childId}', 'ArticleSectionController@deleteOne');
    $app->put('articles/{id}/sections/{childId}/localizations/{region}', 'ArticleSectionController@putOneChildLocalization');
    $app->put('articles/{id}/article-images', 'ArticleImageController@putManyAdd');
    $app->delete('articles/{id}/article-images', 'ArticleImageController@deleteMany');

});

$app->group(['namespace' => 'App\Http\Controllers'], function (Application $app) {

    $app->get('/', 'ApiaryController@index');
    $app->get('/documentation.apib', 'ApiaryController@getApiaryDocumentation');
    $app->get('timezones', 'TimezoneController@getAll');
    $app->get('countries', 'CountriesController@getAll');

    $app->get('auth/jwt/login', 'AuthController@login');
    $app->get('auth/jwt/refresh', 'AuthController@refresh');
    $app->get('auth/jwt/token', 'AuthController@token');
    $app->get('auth/social/{provider}', 'AuthController@redirectToProvider');
    $app->get('auth/social/{provider}/callback', 'AuthController@handleProviderCallback');
    $app->get('auth/sso/{requester}', 'AuthController@singleSignOn');

    $app->delete('users/{email}/password', ['uses' => 'UserController@resetPassword']);

    $app->get('articles/tag-categories', 'ArticleController@getAllTagCategories');
    $app->get('articles/', 'ArticleController@getAllPaginated');
    $app->get('articles/{id}', ['as' => \App\Models\Article::class, 'uses' => 'ArticleController@getOne']);
    $app->get('articles/{id}/localizations', 'ArticleController@getAllLocalizations');
    $app->get('articles/{id}/localizations/{region}', 'ArticleController@getOneLocalization');
    $app->get('articles/{id}/permalinks', 'ArticlePermalinkController@getAll');
    $app->get('articles/{id}/meta', 'ArticleMetaController@getAll');
    $app->get('articles/{id}/comments', 'ArticleCommentController@getAll');
    $app->get('articles/{id}/tags', 'ArticleTagController@getAll');
    $app->get('articles/{id}/sections', 'ArticleSectionController@getAll');
    $app->get('articles/{id}/article-images', 'ArticleImageController@getAll');

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

    $app->put('/entities/{id}/child/{childId}/localizations/{region}', 'App\Http\Controllers\ChildTestController@putOneChildLocalization');
});

$app->group(['prefix' => 'cloudinary', 'namespace' => 'App\Http\Controllers'], function (Application $app) {
    $app->get('signature', 'CloudinaryController@getSignature');
});
