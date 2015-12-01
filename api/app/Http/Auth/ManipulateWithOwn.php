<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Http\Auth;

use Spira\Core\Model\Model\BaseModel;
use Spira\Rbac\Item\Rule;
use Spira\Rbac\User\UserProxy;

class ManipulateWithOwn extends Rule
{
    /**
     * Executes the rule.
     *
     * @param UserProxy $userProxy
     * @param array $params parameters passed to check.
     * @return bool a value indicating whether the rule permits the auth item it is associated with.
     */
    public function execute(UserProxy $userProxy, $params)
    {
        /** @var BaseModel $model */
        $model = isset($params['model']) ? $params['model'] : null;

        if (! $model) {
            return false;
        }

        $userId = $model->exists ? $model->getOriginal('user_id') : $model->user_id;

        return $userId == $userProxy->resolveUser()->getAuthIdentifier();
    }
}
