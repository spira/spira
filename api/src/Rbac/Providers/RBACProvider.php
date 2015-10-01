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
use Spira\Rbac\Storage\DbStorage;
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
        $this->registerStorage();
        $this->commands(GenerateTablesCommand::class);
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

    /**
     * Rbac rules storage.
     */
    protected function registerStorage()
    {
        $this->app->bind(StorageInterface::class, function (Application $app) {
            return $app->make(DbStorage::class);
        });
    }
}
