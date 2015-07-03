<?php namespace App\Providers;

use Tymon\JWTAuth\Providers\JWTAuthServiceProvider as ServiceProvider;

class JWTAuthServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     */
    public function boot()
    {
        $this->app->configure('jwt');

        $this->bootBindings();

        $this->commands('tymon.jwt.generate');
    }
}
