<?php namespace App\Providers;

use App\Extensions\Lock\Manager;
use BeatSwitch\Lock\Drivers\ArrayDriver;
use Illuminate\Support\ServiceProvider;

class LockServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider
     *
     * @return void
     */
    public function register()
    {
        $this->app->bindShared('App\Extensions\Lock\Manager', function () {
            return new Manager(new ArrayDriver());
        });
    }
}
