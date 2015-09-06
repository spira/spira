<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use Mockery as m;
use Rhumsaa\Uuid\Uuid;

class RouteHelperTest extends TestCase
{
    public function testRoute()
    {
        $uuid = Uuid::uuid4();
        $route = \App\Helpers\RouteHelper::getRoute(new \App\Models\TestEntity(['entity_id' => $uuid]));
        $this->assertStringEndsWith('/test/entities/'.$uuid, $route);
    }

    public function testBadRoute()
    {
        $baseModel = m::mock('Spira\Model\Model\BaseModel')->makePartial();
        \App\Helpers\RouteHelper::getRoute($baseModel);
        $this->assertTrue(\App\Helpers\RouteHelper::$badRoutes[get_class($baseModel)]);
    }
}
