<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Http\Transformers;

use Spira\Core\Responder\Transformers\EloquentModelTransformer;
use Spira\Core\Responder\TransformerService;
use Spira\Rbac\Item\Item;

class PermissionsTransformer extends EloquentModelTransformer
{
    public $addSelfKey = false;

    private $routes;

    public function __construct(TransformerService $service)
    {
        $this->prepareRoutes(app()->getRoutes());
        parent::__construct($service);
    }

    protected function prepareRoutes($routes)
    {
        foreach ($routes as $uri => $route) {
            if (isset($route['action']['uses']) && isset($route['uri'])) {
                $this->routes[$route['action']['uses']][] = [
                    'uri' => $route['uri'],
                    'method' => isset($route['method']) ? $route['method'] : null,
                ];
            }
        }
    }

    /**
     * @param $object
     * @return mixed
     */
    public function transform($object)
    {
        $object = parent::transform($object);
        $object['type'] = Item::TYPE_PERMISSION;
        if (isset($this->routes[$object['key']])) {
            $matchingRoutes = [];
            foreach ($this->routes[$object['key']] as $route) {
                $matchingRoute = [];
                $matchingRoute['method'] = $route['method'];
                $matchingRoute['uri'] = $route['uri'];
                $matchingRoutes[] = $matchingRoute;
            }

            $object['matchingRoutes'] = $matchingRoutes;
        }

        return $object;
    }
}
