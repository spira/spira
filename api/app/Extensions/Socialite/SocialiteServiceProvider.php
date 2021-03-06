<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Extensions\Socialite;

use Laravel\Socialite\SocialiteServiceProvider as ServiceProvider;

class SocialiteServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     *
     * @return  void
     */
    public function boot()
    {
        $this->app->configure('services');

        $this->registerServiceRedirects();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('Laravel\Socialite\Contracts\Factory', function ($app) {
            return new SocialiteManager($app);
        });
    }

    /**
     * Add redirect urls for the services to the config array.
     *
     * @return void
     */
    public function registerServiceRedirects()
    {
        $services = array_keys($this->app['config']['services']);
        $host = $this->app['config']['hosts.app'];

        foreach ($services as $service) {
            $url = sprintf('%s/auth/social/%s/callback', $host, $service);
            $this->app['config']['services.'.$service.'.redirect'] = $url;
        }
    }
}
