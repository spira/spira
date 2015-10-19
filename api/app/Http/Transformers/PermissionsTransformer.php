<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Http\Transformers;

use Spira\Rbac\Item\Assignment;

class PermissionsTransformer extends BaseTransformer
{
    /**
     * @param $object
     * @return mixed
     */
    public function transform($object)
    {
        if ($object instanceof Assignment) {
            return $object->roleName;
        }
    }
}
