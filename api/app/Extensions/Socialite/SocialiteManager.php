<?php

namespace App\Extensions\Socialite;

use Laravel\Socialite\SocialiteManager as Manager;
use League\OAuth1\Client\Server\Twitter as TwitterServer;

class SocialiteManager extends Manager
{
    /**
     * Create an instance of the specified driver.
     *
     * @return \Laravel\Socialite\One\AbstractProvider
     */
    protected function createTwitterDriver()
    {
        $config = $this->app['config']['services.twitter'];

        return new TwitterProvider(
            $this->app['request'],
            new TwitterServer($this->formatConfig($config)),
            $this->app['cache']
        );
    }
}
