<?php

namespace App\Extensions\Socialite;

use App\Extensions\Socialite\One\TwitterProvider;
use App\Extensions\Socialite\Two\FacebookProvider;
use App\Extensions\Socialite\Two\GoogleProvider;
use Laravel\Socialite\SocialiteManager as Manager;
use League\OAuth1\Client\Server\Twitter as TwitterServer;

class SocialiteManager extends Manager
{
    /**
     * Create an instance of the specified driver.
     *
     * @return \Laravel\Socialite\Two\AbstractProvider
     */
    protected function createFacebookDriver()
    {
        $config = config('services.facebook');

        return $this->buildProvider(FacebookProvider::class, $config);
    }

    /**
     * Create an instance of the specified driver.
     *
     * @return \Laravel\Socialite\Two\AbstractProvider
     */
    protected function createGoogleDriver()
    {
        $config = config('services.google');

        return $this->buildProvider(GoogleProvider::class, $config);
    }

    /**
     * Create an instance of the specified driver.
     *
     * @return \Laravel\Socialite\One\AbstractProvider
     */
    protected function createTwitterDriver()
    {
        $config = config('services.twitter');

        return new TwitterProvider(
            $this->app['request'],
            new TwitterServer($this->formatConfig($config)),
            $this->app['cache']
        );
    }
}
