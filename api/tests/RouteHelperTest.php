<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 28.07.15
 * Time: 1:01
 */

use Mockery as m;
use Rhumsaa\Uuid\Uuid;

class RouteHelperTest extends TestCase
{
    public function testRoute()
    {
        $uuid = Uuid::uuid4();
        $route = \App\Helpers\RouteHelper::getRoute(new \App\Models\TestEntity(['entity_id'=>$uuid]));
        $this->assertStringEndsWith('/test/entities/'.$uuid, $route);
    }

    public function testBadRoute()
    {
        $baseModel = m::mock('App\Models\BaseModel')->makePartial();
        \App\Helpers\RouteHelper::getRoute($baseModel);
        $this->assertTrue(\App\Helpers\RouteHelper::$badRoutes[get_class($baseModel)]);
    }
}