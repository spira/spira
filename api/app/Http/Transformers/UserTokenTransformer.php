<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Http\Transformers;

use App\Models\User;
use Spira\Core\Responder\Transformers\EloquentModelTransformer;

class UserTokenTransformer extends EloquentModelTransformer
{
    public function transform($object)
    {
        /* @var User $object */
        $object->setHidden(['roles']);
        $roles = $object->roles->lists('key')->toArray();
        $object = parent::transform($object);
        $object['roles'] = $roles;

        return $object;
    }
}
