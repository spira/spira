<?php

use Laravel\Socialite\Two\User;
use App\Exceptions\NotImplementedException;
use App\Extensions\Socialite\SocialiteManager;
use App\Extensions\Socialite\Parsers\ParserFactory;

class SocialiteTest extends TestCase
{
    public function testParserFactoryUnknownParser()
    {
        $this->setExpectedExceptionRegExp(
            NotImplementedException::class,
            '/parser.*/',
            0
        );

        $user = new User;
        $socialUser = ParserFactory::parse($user, 'foobar');
    }

    public function testCreateFacebookDriver()
    {
        $manager = new SocialiteManager($this->app);
        $driver = $manager->with('facebook');

        $this->assertInstanceOf('App\Extensions\Socialite\Two\FacebookProvider', $driver);
    }

    public function testCreateGoogleDriver()
    {
        $manager = new SocialiteManager($this->app);
        $driver = $manager->with('google');

        $this->assertInstanceOf('App\Extensions\Socialite\Two\GoogleProvider', $driver);
    }

    public function testCreateTwitterDriver()
    {
        $manager = new SocialiteManager($this->app);
        $driver = $manager->with('twitter');

        $this->assertInstanceOf('App\Extensions\Socialite\One\TwitterProvider', $driver);
    }
}
