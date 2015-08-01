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

    public function testCreateTwitterDriver()
    {
        // If no twitter credentials exists in the env, add mock credentials
        if (!$this->app->config->get('services.twitter.client_id')) {
            $this->app->config->set('services.twitter.client_id', 'foo');
            $this->app->config->set('services.twitter.client_secret', 'bar');
        }

        $manager = new SocialiteManager($this->app);
        $driver = $manager->with('twitter');

        $this->assertInstanceOf('App\Extensions\Socialite\One\TwitterProvider', $driver);
    }

    public function testCreateGoogleDriver()
    {
        $manager = new SocialiteManager($this->app);
        $driver = $manager->with('google');

        $this->assertInstanceOf('App\Extensions\Socialite\Two\GoogleProvider', $driver);
    }

    public function testAbstractParserMagicMethods()
    {
        $mock = Mockery::mock('App\Extensions\Socialite\Parsers\AbstractParser')->makePartial();
        $mock->token = 'foobar';

        $this->assertNull($mock->foobar);
        $this->assertEquals('foobar', $mock->token);
    }
}
