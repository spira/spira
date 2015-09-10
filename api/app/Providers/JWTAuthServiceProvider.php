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
use App\Models\User;
use App\Polices\UserPolicy;
use Illuminate\Http\Request;
use Spira\Auth\Access\Gate;
use Tymon\JWTAuth\Providers\JWTAuthServiceProvider as ServiceProvider;

class JWTAuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        User::class => UserPolicy::class,
    ];

    /**
     * Boot the service provider.
     */
    public function boot()
    {
        $this->app->configure('jwt');

        $this->bootBindings();

        //authorization part
        $this->registerUserResolver();
        $this->registerAccessGate();

        foreach ($this->policies as $class => $policy) {
            $this->getGate()->policy($class, $policy);
        }
    }

    /**
     * @return Gate
     */
    protected function getGate()
    {
        return $this->app[Gate::GATE_NAME];
    }

    /**
     * Register the access gate service.
     *
     * @return void
     */
    protected function registerAccessGate()
    {
        $this->app->singleton(Gate::GATE_NAME, function ($app) {
            return new Gate($app, function () use ($app) { return $app['tymon.jwt.auth']->user(); });
        });
    }

    /**
     * Register a resolver for the authenticated user.
     *
     * @return void
     */
    protected function registerUserResolver()
    {
        $this->app->bind('Illuminate\Contracts\Auth\Authenticatable', function ($app) {
            return $app['tymon.jwt.auth']->user();
        });

        $this->app->extend(Request::class, function (Request $request, $app) {
            return $request->setUserResolver(function () use ($app) { return $app['tymon.jwt.auth']->user(); });
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
