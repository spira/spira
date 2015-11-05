<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Http\Transformers;

use Spira\Rbac\Item\Item;

class RoleTransformer extends EloquentModelTransformer
{
    public $addSelfKey = false;

    public $nestedMap = [
        'permissions' => PermissionsTransformer::class,
    ];

    /**
     * @param $object
     * @return mixed
     */
    public function transform($object)
    {
        $object = parent::transform($object);
        $object['type'] = Item::TYPE_ROLE;

        return $object;
    }
}
