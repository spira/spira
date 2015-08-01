<?php

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
        $this->app->bindShared('Laravel\Socialite\Contracts\Factory', function ($app) {
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
        $host = $this->app['config']['hosts.api'];

        foreach ($services as $service) {
            $url = sprintf('%s/auth/social/%s/callback', $host, $service);
            $this->app['config']['services.'.$service.'.redirect'] = $url;
        }
    }
}
