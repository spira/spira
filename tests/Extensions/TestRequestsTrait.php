<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use App\Models\User;
use Faker\Factory as Faker;
use Spira\Auth\Driver\Guard;
use Spira\Rbac\Storage\StorageInterface;

namespace Spira\Core\tests\Extensions;

trait TestRequestsTrait
{
    /**
     * Validates Response is a JSON and returns it as an object.
     *
     * @return \stdClass|array
     */
    protected function getJsonResponseAsObject()
    {
        return $this->getJsonResponse(false);
    }

    /**
     * Validates Response is a JSON and returns it as an array.
     *
     * @return array
     */
    protected function getJsonResponseAsArray()
    {
        return $this->getJsonResponse(true);
    }

    /**
     * Validates Response is a JSON and returns it as an array or object.
     *
     * @param bool $asArray
     * @return array
     */
    protected function getJsonResponse($asArray = false)
    {
        $this->shouldReturnJson();

        return json_decode($this->response->getContent(), $asArray);
    }

}
