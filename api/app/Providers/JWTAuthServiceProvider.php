<?php namespace App\Providers;

use App\Extensions\JWTAuth\PayloadFactory;
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
    }

    /**
     * Register the bindings for the Payload Factory
     */
    protected function registerPayloadFactory()
    {
        $this->app['tymon.jwt.payload.factory'] = $this->app->share(function ($app) {
            $factory = new PayloadFactory($app['tymon.jwt.claim.factory'], $app['request'], $app['tymon.jwt.validators.payload']);

            return $factory->setTTL($this->config('ttl'));
        });
    }

}
