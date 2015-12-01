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

class UserTransformer extends EloquentModelTransformer
{
    public $nestedMap = [
        'roles' => RoleTransformer::class,
        'permissions' => PermissionsTransformer::class,
    ];
}
