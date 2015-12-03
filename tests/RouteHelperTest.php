<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spira\Core\tests;

use Mockery as m;
use Rhumsaa\Uuid\Uuid;
use Spira\Core\Helpers\RouteHelper;
use Spira\Core\Model\Test\TestEntity;

class RouteHelperTest extends TestCase
{
    public function testRoute()
    {
        $this->app->get('test/entities/{id}', ['as' => TestEntity::class, 'uses' => 'TestController@getOne']);
        $uuid = Uuid::uuid4();
        $route = RouteHelper::getRoute(new TestEntity(['entity_id' => $uuid]));
        $this->assertStringEndsWith('/test/entities/'.$uuid, $route);
    }

    public function testBadRoute()
    {
        $baseModel = m::mock('Spira\Core\Model\Model\BaseModel')->makePartial();
        RouteHelper::getRoute($baseModel);
        $this->assertTrue(RouteHelper::$badRoutes[get_class($baseModel)]);
    }
}
