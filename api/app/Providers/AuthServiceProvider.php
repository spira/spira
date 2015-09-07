<?php
/**
 * Created by PhpStorm.
 * User: ivanmatveev
 * Date: 07.09.15
 * Time: 16:16
 */

namespace App\Providers;

use Illuminate\Contracts\Auth\Access\Gate;
use \Illuminate\Auth\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [];


    public function boot()
    {
        foreach ($this->policies as $class => $policy) {
            $this->getGate()->policy($class,$policy);
        }
    }

    /**
     * Register the authenticator services.
     *
     * @return void
     */
    protected function registerAuthenticator()
    {
        return;
    }


    /**
     * @return Gate
     */
    protected function getGate()
    {
        return $this->app[Gate::class];
    }

}