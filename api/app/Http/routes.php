<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use Laravel\Lumen\Application;

$app->group(['namespace' => 'App\Http\Controllers', 'middleware' => 'requireAuthorization'], function (Application $app) {

    $app->get('auth/jwt/user/{userId}', 'AuthController@loginAsUser');

    $app->get('users/', ['uses' => 'UserController@getAllPaginated']);
    $app->get('users/{id}', ['uses' => 'UserController@getOne', 'as' => App\Models\User::class]);
    $app->put('users/{id}', ['uses' => 'UserController@putOne']);
    $app->patch('users/{id}', ['uses' => 'UserController@patchOne']);
    $app->delete('users/{id}', ['uses' => 'UserController@deleteOne']);
    $app->get('users/{id}/roles', ['uses' => 'PermissionsController@getAll']);
    $app->put('users/{id}/roles', ['uses' => 'PermissionsController@putMany']);
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
    $app->put('articles/{id}/meta', 'ArticleMetaController@putMany');
    $app->delete('articles/{id}/meta/{childId}', 'ArticleMetaController@deleteOne');
    $app->post('articles/{id}/comments', ['uses' => 'ArticleCommentController@postOne', 'middleware' => 'attachUserToEntity']);
    $app->put('articles/{id}/tags', 'ArticleTagController@putMany');
    $app->post('articles/{id}/sections', 'ArticleSectionController@postMany');
    $app->delete('articles/{id}/sections', 'ArticleSectionController@deleteMany');
    $app->delete('articles/{id}/sections/{childId}', 'ArticleSectionController@deleteOne');
    $app->put('articles/{id}/sections/{childId}/localizations/{region}', 'ArticleSectionController@putOneChildLocalization');
    $app->put('articles/{id}/bookmarks/{childId}', ['uses' => 'ArticleBookmarksController@putOne', 'middleware' => 'attachUserToEntity']);
    $app->put('articles/{id}/ratings/{childId}', ['uses' => 'ArticleUserRatingsController@putOne', 'middleware' => 'attachUserToEntity']);
    $app->delete('articles/{id}/bookmarks/{childId}', ['uses' => 'ArticleBookmarksController@deleteOne']);
    $app->delete('articles/{id}/ratings/{childId}', ['uses' => 'ArticleUserRatingsController@deleteOne']);

    $app->post('tags/', 'TagController@postOne');
    $app->patch('tags/{id}', 'TagController@patchOne');
    $app->delete('tags/{id}', 'TagController@deleteOne');
    $app->put('tags/{id}/child-tags', 'ChildTagController@putMany');

    $app->get('cloudinary/signature', 'CloudinaryController@getSignature');

    $app->put('images/{id}', 'ImageController@putOne');
    $app->patch('images/{id}', 'ImageController@patchOne');
    $app->delete('images/{id}', 'ImageController@deleteOne');

    $app->get('utility/system-information', 'UtilityController@getSystemInformation');

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

    $app->get('tags/', ['uses' => 'TagController@getAllPaginated', 'as' => \App\Models\Tag::class]);
    $app->get('tags/group/{group}', ['as' => \App\Models\Tag::class, 'uses' => 'TagController@getGroupTags']);
    $app->get('tags/{id}', ['as' => \App\Models\Tag::class, 'uses' => 'TagController@getOne']);

    $app->get('images/', 'ImageController@getAllPaginated');
    $app->get('images/{id}', ['as' => \App\Models\Image::class, 'uses' => 'ImageController@getOne']);

});
