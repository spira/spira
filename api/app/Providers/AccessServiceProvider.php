<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Providers;

use Spira\Rbac\Providers\RBACProvider;

class AccessServiceProvider extends RBACProvider
{
    protected $defaultRoles = ['user'];
}
