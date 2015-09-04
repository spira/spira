<?php

/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 17.07.15
 * Time: 18:19.
 */

namespace App\Helpers;

use Spira\Model\Model\BaseModel;

class RouteHelper
{
    public static $badRoutes = [];

    /**
     * @param BaseModel $model
     * @return string|false
     */
    public static function getRoute(BaseModel $model)
    {
        if (! isset(static::$badRoutes[get_class($model)])) {
            try {
                return route(get_class($model), ['id' => $model->getQueueableId()]);
            } catch (\InvalidArgumentException $e) {
                static::$badRoutes[get_class($model)] = true;
            }
        }

        return false;
    }
}
