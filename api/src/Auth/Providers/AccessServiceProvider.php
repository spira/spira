<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spira\Auth\Providers;

use Illuminate\Support\ServiceProvider;
use Spira\Auth\Access\Gate;

class AccessServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [

    ];

    public function register()
    {
        $this->attachPolicesToGate();
        $this->registerAccessGate();
    }

    protected function attachPolicesToGate()
    {
        $this->app->extend(Gate::GATE_NAME, function (Gate $gate, $app) {
            foreach ($this->policies as $class => $policy) {
                $gate->policy($class, $policy);
            }

            return $gate;
        });
    }

    /**
     * Register the access gate service.
     *
     * @return void
     */
    protected function registerAccessGate()
    {
        $this->app->singleton(Gate::GATE_NAME, function ($app) {
            return new Gate($app, function () use ($app) { return $app['auth']->user(); });
        });
    }
}
