<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Providers;

use App\Models\User;
use App\Polices\UserPolicy;

class AccessServiceProvider extends \Spira\Auth\Providers\AccessServiceProvider
{
    protected $policies = [
        User::class => UserPolicy::class,
    ];
}
