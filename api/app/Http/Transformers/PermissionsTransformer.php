<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Http\Transformers;

use App\Services\TransformerService;
use Spira\Rbac\Item\Item;

class PermissionsTransformer extends BaseTransformer
{
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
        if ($object instanceof Item) {
            $viewObj = new \StdClass();
            $viewObj->key = $object->name;

            if (isset($this->routes[$object->name])) {
                $matchingRoutes = [];
                foreach ($this->routes[$object->name] as $route) {
                    $matchingRouteObject = new \StdClass();
                    $matchingRouteObject->method = $route['method'];
                    $matchingRouteObject->uri = $route['uri'];
                    $matchingRoutes[] = $matchingRouteObject;
                }

                $viewObj->matchingRoutes = $matchingRoutes;
            }

            $viewObj->type = $object->type;
            $viewObj->description = $object->description;
            if (isset($object->_permissions)) {
                $viewObj->_permissions = $this->transformCollection($object->_permissions);
            }

            return $viewObj;
        }
    }
}
