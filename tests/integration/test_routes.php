<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use Laravel\Lumen\Application;
use Spira\Core\Model\Test\SecondTestEntity;
use Spira\Core\Model\Test\TestEntity;

$app->group(['namespace' => 'Spira\Core\tests\integration', 'middleware' => 'requireAuthorization'], function (Application $app) {
    $app->post('test/entities', 'TestController@postOne');
    $app->put('test/entities/{id}', 'TestController@putOne');
    $app->put('test/entities', 'TestController@putMany');
    $app->patch('test/entities/{id}', 'TestController@patchOne');
    $app->patch('test/entities', 'TestController@patchMany');
    $app->delete('test/entities/{id}', 'TestController@deleteOne');
    $app->delete('test/entities', 'TestController@deleteMany');

    $app->put('test/entities/{id}/localizations/{region}', 'TestController@putOneLocalization');

    $app->post('test/entities/{id}/child', 'ChildTestController@postOne');
    $app->put('test/entities/{id}/child/{childId}', 'ChildTestController@putOne');
    $app->put('test/entities/{id}/children', 'ChildTestController@putMany');
    $app->post('test/entities/{id}/children', 'ChildTestController@postMany');
    $app->patch('test/entities/{id}/child/{childId}', 'ChildTestController@patchOne');
    $app->patch('test/entities/{id}/children', 'ChildTestController@patchMany');
    $app->delete('test/entities/{id}/child/{childId}', 'ChildTestController@deleteOne');
    $app->delete('test/entities/{id}/children', 'ChildTestController@deleteMany');

    $app->put('test/entities/{id}/child/{childId}/localizations/{region}', 'ChildTestController@putOneChildLocalization');
});

$app->group(['namespace' => 'Spira\Core\tests\integration'], function (Application $app) {

    $app->get('test/internal-exception', 'TestController@internalException');
    $app->get('test/fatal-error', 'TestController@fatalError');
    $app->get('test/entities', 'TestController@getAll');
    $app->get('test/entities/pages', 'TestController@getAllPaginated');
    $app->get('test/entities_encoded/{id}', 'TestController@urlEncode');
    $app->get('test/entities/{id}', ['as' => TestEntity::class, 'uses' => 'TestController@getOne']);
    $app->get('test/entities-second/{id}', ['as' => SecondTestEntity::class, 'uses' => 'TestController@getOne']);

    $app->get('test/entities/{id}/localizations', 'TestController@getAllLocalizations');
    $app->get('test/entities/{id}/localizations/{region}', 'TestController@getOneLocalization');

    $app->get('test/entities/{id}/children', 'ChildTestController@getAll');
    $app->get('test/entities/{id}/child/{childId}', 'ChildTestController@getOne');

    $app->get('test/many/{id}/children', 'LinkedEntityTestController@getAll');
    $app->put('test/many/{id}/children', 'LinkedEntityTestController@syncMany');
    $app->post('test/many/{id}/children', 'LinkedEntityTestController@attachMany');
    $app->put('test/many/{id}/children/{childId}', 'LinkedEntityTestController@attachOne');
    $app->delete('test/many/{id}/children/{childId}', 'LinkedEntityTestController@detachOne');
    $app->delete('test/many/{id}/children', 'LinkedEntityTestController@detachAll');
});
