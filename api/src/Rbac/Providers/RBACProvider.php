<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spira\Rbac\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application;
use Spira\Rbac\Access\Gate;
use Spira\Rbac\Commands\GenerateTablesCommand;
use Spira\Rbac\Storage\AssignmentStorageInterface;
use Spira\Rbac\Storage\Db\AssignmentStorage;
use Spira\Rbac\Storage\Db\ItemStorage;
use Spira\Rbac\Storage\DbStorage;
use Spira\Rbac\Storage\ItemStorageInterface;
use Spira\Rbac\Storage\Storage;
use Spira\Rbac\Storage\StorageInterface;

class RBACProvider extends ServiceProvider
{
    protected $defaultRoles = [];

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerAccessGate();
        $this->registerItemStorage();
        $this->registerAssignmentStorage();
        $this->registerBaseStorage();
    }

    /**
     * Register the access gate service.
     *
     * @return void
     */
    protected function registerAccessGate()
    {
        $this->app->singleton(Gate::GATE_NAME, function (Application $app) {
            return new Gate(
                    $app->make(StorageInterface::class),
                    function () use ($app) { return $app['auth']->user(); },
                    $this->defaultRoles
                );
        });
    }

    protected function registerAssignmentStorage()
    {
        $this->app->singleton(AssignmentStorageInterface::class, function (Application $app) {
            return $app->make(AssignmentStorage::class);
        });
    }

    protected function registerItemStorage()
    {
        $this->app->singleton(ItemStorageInterface::class, function (Application $app) {
            return $app->make(ItemStorage::class);
        });
    }

    /**
     * Rbac rules storage.
     */
    protected function registerBaseStorage()
    {
        $this->app->singleton(StorageInterface::class, function (Application $app) {
            return $app->make(Storage::class);
        });
    }
}
