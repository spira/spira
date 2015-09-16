<?php
/**
 * Created by PhpStorm.
 * User: ivanmatveev
 * Date: 16.09.15
 * Time: 18:21
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