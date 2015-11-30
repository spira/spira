<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spira\Core\Helpers;


use Spira\Core\Model\Model\BaseModel;

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
