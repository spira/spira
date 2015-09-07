<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Providers;

use App\Extensions\JWTAuth\JWTAuth;
use App\Extensions\JWTAuth\JWTManager;
use App\Extensions\JWTAuth\ClaimFactory;
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
        $this->registerAsCoreAuthenticator();
    }

    /**
     * Register the authenticator services.
     *
     * @return void
     */
    protected function registerAsCoreAuthenticator()
    {
        $this->app->singleton('auth', function (Application $app) {
            // Once the authentication service has actually been requested by the developer
            // we will set a variable in the application indicating such. This helps us
            // know that we need to set any queued cookies in the after event later.
            $app['auth.loaded'] = true;

            return $app['tymon.jwt.auth'];
        });
    }

    /**
     * Register the bindings for the Payload Factory.
     */
    protected function registerPayloadFactory()
    {
        $this->app['tymon.jwt.payload.factory'] = $this->app->share(function ($app) {
            $factory = new PayloadFactory($app['tymon.jwt.claim.factory'], $app['request'], $app['tymon.jwt.validators.payload']);

            return $factory->setTTL($this->config('ttl'));
        });
    }

    /**
     * Register the bindings for the Payload Factory.
     */
    protected function registerClaimFactory()
    {
        $this->app->singleton('tymon.jwt.claim.factory', function () {
            return new ClaimFactory();
        });
    }

    /**
     * Register the bindings for the JWT Manager.
     */
    protected function registerJWTManager()
    {
        $this->app['tymon.jwt.manager'] = $this->app->share(function ($app) {

            $instance = new JWTManager(
                $app['tymon.jwt.provider.jwt'],
                $app['tymon.jwt.blacklist'],
                $app['tymon.jwt.payload.factory']
            );

            return $instance->setBlacklistEnabled((bool) $this->config('blacklist_enabled'));
        });
    }

    /**
     * Register the bindings for the main JWTAuth class.
     */
    protected function registerJWTAuth()
    {
        $this->app['tymon.jwt.auth'] = $this->app->share(function ($app) {

            $auth = new JWTAuth(
                $app['tymon.jwt.manager'],
                $app['tymon.jwt.provider.user'],
                $app['tymon.jwt.provider.auth'],
                $app['request']
            );

            return $auth->setIdentifier($this->config('identifier'));
        });
    }
}
