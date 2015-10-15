<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Providers;

use App\Extensions\Rbac\UserAssignmentStorage;
use Laravel\Lumen\Application;
use Spira\Rbac\Providers\RBACProvider;
use Spira\Rbac\Storage\AssignmentStorageInterface;
use Spira\Rbac\Storage\File\ItemStorage;
use Spira\Rbac\Storage\ItemStorageInterface;

class AccessServiceProvider extends RBACProvider
{
    protected $defaultRoles = ['user'];

    protected function registerAssignmentStorage()
    {
        $this->app->singleton(AssignmentStorageInterface::class, function (Application $app) {
            return $app->make(UserAssignmentStorage::class);
        });
    }

    protected function registerItemStorage()
    {
        $this->app->singleton(ItemStorageInterface::class, function (Application $app) {
            return new ItemStorage($app->basePath().'/config/permissions.php');
        });
    }
}
