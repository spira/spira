<?php
/**
 * Created by PhpStorm.
 * User: ivanmatveev
 * Date: 24.09.15
 * Time: 19:19
 */

namespace Spira\Rbac\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application;
use Spira\Rbac\Access\Gate;
use Spira\Rbac\Storage\DbStorage;
use Spira\Rbac\Storage\StorageInterface;

class RBACProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerAccessGate();
        $this->registerStorage();
    }

    /**
     * Register the access gate service.
     *
     * @return void
     */
    protected function registerAccessGate()
    {
        $this->app->singleton(Gate::GATE_NAME, function (Application $app) {
            //init auth with gate just in case
            $app->make('auth');
            return $app->make(Gate::class);
        });
    }


    protected function registerStorage()
    {
        $this->app->bind(StorageInterface::class, function(Application $app){
            return $app->make(DbStorage::class);
        });
    }


}