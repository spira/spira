<?php

namespace App\Providers;

use App\Models\User;
use BeatSwitch\Lock\Callers\SimpleCaller;
use BeatSwitch\Lock\Drivers\ArrayDriver;
use BeatSwitch\Lock\Manager;
use Illuminate\Support\ServiceProvider;

class LockServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider
     *
     * @return void
     */
    public function register()
    {
        $this->bootstrapManager();
        $this->bootstrapAuthedUserLock();
    }

    /**
     * This method will bootstrap the lock manager instance.
     *
     * @return  void
     */
    protected function bootstrapManager()
    {
        $this->app->bindShared('lock.manager', function () {
            return new Manager($this->getDriver());
        });

        $this->app->alias('lock.manager', 'BeatSwitch\Lock\Manager');
    }

    /**
     * Returns the configured driver
     *
     * @return \BeatSwitch\Lock\Drivers\Driver
     */
    protected function getDriver()
    {
        return new ArrayDriver();
    }

    /**
     * This will bootstrap the lock instance for the authed user.
     *
     * @return  void
     */
    protected function bootstrapAuthedUserLock()
    {
        $this->app->bindShared('lock', function ($app) {
            // If the user is logged in, we'll make the user lock aware and register its lock instance.
            if ($app['auth']->check()) {
                // Get the lock instance for the authed user.
                $lock = $app['lock.manager']->caller($app['auth']->user());

                // Enable the LockAware trait on the user.
                $app['auth']->user()->setLock($lock);

                return $lock;
            }

            // Set the caller type for the user caller.
            $userCallerType = 'users';

            // Bootstrap a SimpleCaller object which has the "guest" role.
            return $app['lock.manager']->caller(new SimpleCaller($userCallerType, 0, ['guest']));
        });

        $this->app->alias('lock', 'BeatSwitch\Lock\Lock');
    }

    /**
     * Get the services provided by the provider
     *
     * @return array
     */
    public function provides()
    {
        return ['lock', 'lock.manager'];
    }
}
