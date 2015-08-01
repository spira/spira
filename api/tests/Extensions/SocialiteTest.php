<?php

use Laravel\Socialite\Two\User;
use App\Exceptions\NotImplementedException;
use App\Extensions\Socialite\SocialiteManager;
use App\Extensions\Socialite\Two\FacebookProvider;
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

        $this->assertInstanceOf(FacebookProvider::class, $driver);
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

    public function testProviderTraitUser()
    {
        $mock = Mockery::mock(FacebookProvider::class, [$this->app->request, null, null, null])->makePartial()->shouldAllowMockingProtectedMethods();
        $mock->shouldReceive('getAccessToken')
            ->once()
            ->andReturn([]);
        $mock->shouldReceive('getUserByToken')
            ->once()
            ->andReturn(['id' => 'foo']);

        $user = $mock->user();

        $this->assertInstanceOf(User::class, $user);
    }

    public function testProviderTraitgetCachedReturnUrlCache()
    {
        $returnUrl = 'http://foo.bar';
        Cache::put('oauth_return_url_foo', $returnUrl, 1);
        $request = Mockery::mock('Illuminate\Http\Request');
        $request->shouldReceive('get')
            ->once()
            ->andReturn('foo');

        $mock = Mockery::mock(FacebookProvider::class, [$request, null, null, null])->makePartial();

        $url = $mock->getCachedReturnUrl();

        $this->assertEquals($url, $returnUrl);
    }

    public function testProviderTraitgetCachedReturnUrlNoCache()
    {
        $mock = Mockery::mock(FacebookProvider::class, [$this->app->request, null, null, null])->makePartial();

        $url = $mock->getCachedReturnUrl();

        $this->assertEquals($url, config('hosts.app'));
    }

    public function testAbstractParserMagicMethods()
    {
        $mock = Mockery::mock('App\Extensions\Socialite\Parsers\AbstractParser')->makePartial();
        $mock->token = 'foobar';

        $this->assertNull($mock->foobar);
        $this->assertEquals('foobar', $mock->token);
    }
}
