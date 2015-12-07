<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Services;

use App\Models\Relations\GateTrait;
use Spira\Core\Validation\SpiraValidator;

class Validator extends SpiraValidator
{
    use GateTrait;

    public function validateRbacRoleExists($attribute, $value, $parameters)
    {
        return (bool) $this->getGate()->getStorage()->getItem($value);
    }
}
