<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use App\Models\User;
use GuzzleHttp\Client;

/**
 * Class AuthTest.
 * @group integration
 */
class AuthTest extends TestCase
{
    protected function callRefreshToken($token)
    {
        $this->getJson('/auth/jwt/refresh', [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ]);
    }

    public function testLogin()
    {
        $user = $this->createUser();
        $credential = factory(App\Models\UserCredential::class)->make();
        $credential->user_id = $user->user_id;
        $credential->save();
        $this->getJson('/auth/jwt/login', [
            'PHP_AUTH_USER' => $user->email,
            'PHP_AUTH_PW'   => 'password',
        ]);

        $array = json_decode($this->response->getContent(), true);
        $this->assertResponseOk();
        $this->assertEquals('application/json', $this->response->headers->get('content-type'));
        $this->assertArrayHasKey('token', $array);

        // Test that decoding the token, will match the user
        $payload = $this->app->make('auth')->getTokenizer()->decode($array['token']);
        $this->assertEquals($user->user_id, $payload['sub']);

        $this->assertArrayHasKey('iss', $array['decodedTokenBody']);
        $this->assertArrayHasKey('userId', $array['decodedTokenBody']['_user']);
        $this->assertEquals('password', $array['decodedTokenBody']['method']);

        $this->assertEquals($payload, $array['decodedTokenBody']);
    }

    public function testFailedLogin()
    {
        $user = $this->createUser();

        $this->getJson('/auth/jwt/login', [
            'PHP_AUTH_USER' => $user->email,
            'PHP_AUTH_PW'   => 'foobar',
        ]);

        $body = json_decode($this->response->getContent());
        $this->assertResponseStatus(401);
        $this->assertContains('failed', $body->message);
    }

    public function testLoginEmptyPassword()
    {
        $user = $this->createUser();
        $credential = factory(App\Models\UserCredential::class)->make();
        $credential->user_id = $user->user_id;
        $credential->save();
        $this->getJson('/auth/jwt/login', [
            'PHP_AUTH_USER' => $user->email,
            'PHP_AUTH_PW'   => '',
        ]);

        $body = json_decode($this->response->getContent());
        $this->assertResponseStatus(401);
        $this->assertContains('failed', $body->message);
    }

    public function testLoginUserMissCredentials()
    {
        $user = $this->createUser();

        $this->getJson('/auth/jwt/login', [
            'PHP_AUTH_USER' => $user->email,
            'PHP_AUTH_PW'   => '',
        ]);

        $body = json_decode($this->response->getContent());
        $this->assertResponseStatus(401);
        $this->assertContains('failed', $body->message);
    }

    public function testLoginNewEmailAfterChange()
    {
        $user = factory(User::class)->create();
        $credential = factory(App\Models\UserCredential::class)->make();
        $credential->user_id = $user->user_id;
        $credential->save();

        $user->createEmailConfirmToken('foo@bar.net', $user->email);

        $this->getJson('/auth/jwt/login', [
            'PHP_AUTH_USER' => 'foo@bar.net',
            'PHP_AUTH_PW'   => 'password',
        ]);

        $array = json_decode($this->response->getContent(), true);
        $this->assertResponseOk();
        $this->assertEquals('application/json', $this->response->headers->get('content-type'));
        $this->assertArrayHasKey('token', $array);

        // Test that decoding the token, will match the user
        $payload = $this->app->make('auth')->getTokenizer()->decode($array['token']);
        $this->assertEquals($user->user_id, $payload['sub']);

        $this->assertArrayHasKey('iss', $array['decodedTokenBody']);
        $this->assertArrayHasKey('userId', $array['decodedTokenBody']['_user']);
        $this->assertEquals('password', $array['decodedTokenBody']['method']);

        $this->assertEquals($payload, $array['decodedTokenBody']);
    }

    public function testLoginNewEmailAfterChangeWrongPassword()
    {
        $user = factory(User::class)->create();
        $credential = factory(App\Models\UserCredential::class)->make();
        $credential->user_id = $user->user_id;
        $credential->save();

        $user->createEmailConfirmToken('foo@bar.net', $user->email);

        $this->getJson('/auth/jwt/login', [
            'PHP_AUTH_USER' => 'foo@bar.net',
            'PHP_AUTH_PW'   => '',
        ]);

        $body = json_decode($this->response->getContent());
        $this->assertResponseStatus(401);
        $this->assertContains('failed', $body->message);
    }

    public function testLoginOldEmailAfterChange()
    {
        $user = factory(User::class)->create();
        $credential = factory(App\Models\UserCredential::class)->make();
        $credential->user_id = $user->user_id;
        $credential->save();

        $user->createEmailConfirmToken('foo@bar.net', $user->email);

        $this->getJson('/auth/jwt/login', [
            'PHP_AUTH_USER' => $user->email,
            'PHP_AUTH_PW'   => 'password',
        ]);

        $array = json_decode($this->response->getContent(), true);
        $this->assertResponseOk();
        $this->assertEquals('application/json', $this->response->headers->get('content-type'));
        $this->assertArrayHasKey('token', $array);

        // Test that decoding the token, will match the user
        $payload = $this->app->make('auth')->getTokenizer()->decode($array['token']);
        $this->assertEquals($user->user_id, $payload['sub']);

        $this->assertArrayHasKey('iss', $array['decodedTokenBody']);
        $this->assertArrayHasKey('userId', $array['decodedTokenBody']['_user']);
        $this->assertEquals('password', $array['decodedTokenBody']['method']);

        $this->assertEquals($payload, $array['decodedTokenBody']);
    }

    public function testRefresh()
    {
        $user = $this->createUser();
        $token = $this->tokenFromUser($user, ['method' => 'password']);

        $this->callRefreshToken($token);

        $object = json_decode($this->response->getContent());
        $this->assertResponseOk();
        $this->assertNotEquals($token, $object->token);

        $payload = $this->app->make('auth')->getTokenizer()->decode($object->token);
        $this->assertEquals('password', $payload['method']);
    }

    public function testRefreshPlainHeader()
    {
        $user = User::first();
        $token = $this->tokenFromUser($user, ['method' => 'password']);

        $options = ['headers' => ['authorization' => 'Bearer '.$token]];
        $client = new Client([
            'base_url' => sprintf(
                'http://%s:%s',
                getenv('WEBSERVER_HOST'),
                getenv('WEBSERVER_PORT')
            ),
        ]);
        $res = $client->get('/auth/jwt/refresh', $options);

        $array = $res->json();
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertNotEquals($token, $array['token']);
        $payload = $this->app->make('auth')->getTokenizer()->decode($array['token']);
        $this->assertEquals('password', $payload['method']);
    }

    public function testRefreshExpiredToken()
    {
        $user = $this->createUser();
        $token = $this->tokenFromUser($user, [
            'method' => 'password',
            'exp' => 123 - 3600,
            'nbf' => 123,
            'iat' => 123,
        ]);

        $this->callRefreshToken($token);

        $body = json_decode($this->response->getContent());
        $this->assertResponseStatus(401);
        $this->assertContains('expired', $body->message);
    }

    public function testRefreshInvalidTokenSignature()
    {
        $user = $this->createUser();
        $token = $this->tokenFromUser($user);

        // Replace the signature with an invalid string
        $segments = explode('.', $token);
        $segments[2] = 'foobar';
        $token = implode('.', $segments);

        $this->callRefreshToken($token);
        $this->assertException('Signature could not', 422, 'TokenInvalidException');
    }

    public function testRefreshInvalidToken()
    {
        $token = 'foo.bar.baz';

        $this->callRefreshToken($token);

        $this->assertException('invalid', 422, 'TokenInvalidException');
    }

    public function testRefreshMissingToken()
    {
        $this->getJson('/auth/jwt/refresh');

        $this->assertException('The token can not be parsed from the Request', 400, 'TokenIsMissingException');
    }

    public function testToken()
    {
        $token = 'foobar';
        $user = $this->createUser();
        Cache::put('login_token_'.$token, $user->user_id, 1);

        $this->getJson('/auth/jwt/token', [
            'HTTP_AUTHORIZATION' => 'Token '.$token,
        ]);

        $array = json_decode($this->response->getContent(), true);
        $this->assertResponseOk();
        $this->assertArrayHasKey('token', $array);
    }

    public function testMissingToken()
    {
        $this->getJson('/auth/jwt/token');

        $this->assertException('Single use token not provided.', 400, 'TokenIsMissingException');
    }

    public function testInvalidToken()
    {
        $token = 'invalid';
        $this->getJson('/auth/jwt/token', [
            'HTTP_AUTHORIZATION' => 'Token '.$token,
        ]);

        $this->assertResponseStatus(422);
    }

    public function testTokenInvalid()
    {
        $token = 'foobar';
        $user = $this->createUser();
        Cache::put('login_token_'.$token, $user->user_id, 1);

        $this->getJson('/auth/jwt/token', [
            'HTTP_AUTHORIZATION' => 'Token '.$token,
        ]);

        $this->assertResponseOk();

        $this->getJson('/auth/jwt/token', [
            'HTTP_AUTHORIZATION' => 'Token '.$token,
        ]);

        $this->assertException('Invalid single use token', 422, 'TokenInvalidException');
    }

    public function testMakeLoginToken()
    {
        $user = $this->createUser();

        $token = $user->makeLoginToken($user->user_id);

        $id = Cache::pull('login_token_'.$token);

        $this->assertEquals($id, $user->user_id);
    }

    public function testEnvironmentForSocial()
    {
        // Make sure we have the hosts env variable for the redirect url generation
        $hostApi = $this->app['config']['hosts.api'];
        $hostApp = $this->app['config']['hosts.app'];

        $this->assertNotNull($hostApi);
        $this->assertStringStartsWith('http', $hostApi);
        $this->assertStringEndsNotWith('/', $hostApi);
        $this->assertNotNull($hostApp);
        $this->assertStringStartsWith('http', $hostApp);
        $this->assertStringEndsNotWith('/', $hostApp);
    }

    public function testInvalidProvider()
    {
        $this->getJson('/auth/social/foobar');

        $this->assertException('Provider', 501, 'NotImplementedException');
    }

    public function testProviderRedirect()
    {
        $this->getJson('/auth/social/facebook');

        $this->assertResponseStatus(302);
    }

    public function testProviderRedirectReturnUrlOAuthOne()
    {
        $returnUrl = 'http://www.foo.bar/';

        // If we have no valid twitter credentials, we'll mock the redirect
        // request and set a cache manually. That allows us to still run live
        // tests against twitter if credentials is available, and if not
        // available, we still can test that the cache with the returnurl is
        // properly set.
        if (! $this->app->config->get('services.twitter.client_id')) {
            Cache::put('oauth_return_url_'.'foobar', $returnUrl, 1);
            $mock = Mockery::mock('App\Extensions\Socialite\SocialiteManager');
            $this->app->instance('Laravel\Socialite\Contracts\Factory', $mock);
            $mock->shouldReceive('with->redirect')
                ->once()
                ->andReturn(redirect('http://foo.bar?oauth_token=foobar'));
        }

        $this->getJson('/auth/social/twitter?returnUrl='.urlencode($returnUrl));

        // Parse the oauth token from the response and get the cached value
        $this->assertTrue($this->response->headers->has('location'));
        $segments = parse_url($this->response->headers->get('location'));
        parse_str($segments['query'], $array);
        $key = 'oauth_return_url_'.$array['oauth_token'];
        $url = Cache::get($key);

        $this->assertEquals($url, $returnUrl);
    }

    public function testProviderRedirectReturnUrlOAuthTwo()
    {
        $returnUrl = 'http://www.foo.bar/';

        $this->getJson('/auth/social/facebook?returnUrl='.urlencode($returnUrl));

        // Parse the oauth token from the response and get the cached value
        $this->assertTrue($this->response->headers->has('location'));
        $segments = parse_url($this->response->headers->get('location'));
        parse_str($segments['query'], $array);
        $key = 'oauth_return_url_'.$array['state'];
        $url = Cache::get($key);

        $this->assertEquals($returnUrl, $url);
    }

    public function testProviderCallbackNoEmail()
    {
        $mock = Mockery::mock('App\Extensions\Socialite\SocialiteManager');
        $this->app->instance('Laravel\Socialite\Contracts\Factory', $mock);
        $mock->shouldReceive('with->user')
            ->once()
            ->andReturn((object) [
                'email' => null,
                'token' => 'foobar',
            ]);

        $this->getJson('/auth/social/facebook/callback');

        $this->assertException('no email', 422, 'UnprocessableEntityException');
    }

    public function testProviderCallbackExistingUser()
    {
        $user = $this->createUser();

        $socialUser = Mockery::mock('Laravel\Socialite\Contracts\User');
        $mock = Mockery::mock('App\Extensions\Socialite\SocialiteManager');
        $this->app->instance('Laravel\Socialite\Contracts\Factory', $mock);
        $socialUser->email = $user->email;
        $socialUser->token = 'foobar';
        $socialUser->avatar = 'foobar';
        $socialUser->name = 'foobar';
        $socialUser->user = ['first_name' => 'foo', 'last_name' => 'bar'];
        $mock->shouldReceive('with->user')
            ->once()
            ->andReturn($socialUser);
        $mock->shouldReceive('with->getCachedReturnUrl')
            ->once()
            ->andReturn('http://foo.bar');

        $this->getJson('/auth/social/facebook/callback');

        $this->assertResponseStatus(302);

        $this->assertTrue($this->response->headers->has('location'), 'Response has location header.');
        $locationHeader = $this->response->headers->get('location');

        // Get the returned token
        $tokenParam = parse_url($locationHeader, PHP_URL_QUERY);
        $this->assertStringStartsWith('jwtAuthToken=', $tokenParam);

        $token = str_replace('jwtAuthToken=', '', $tokenParam);

        $decoded = $this->app->make('auth')->getTokenizer()->decode($token);
        $this->assertEquals('facebook', $decoded['method']);
        $this->assertStringStartsWith('http://foo.bar', $locationHeader);

        // Assert that the social login was created
        $user = User::find($user->user_id);
        $socialLogin = $user->socialLogins->first()->toArray();
        $this->assertEquals('facebook', $socialLogin['provider']);
    }

    public function testProviderCallbackNewUser()
    {
        $user = factory(User::class)->make();

        $socialUser = Mockery::mock('Laravel\Socialite\Contracts\User');
        $mock = Mockery::mock('App\Extensions\Socialite\SocialiteManager');
        $this->app->instance('Laravel\Socialite\Contracts\Factory', $mock);
        $socialUser->email = $user->email;
        $socialUser->token = 'foobar';
        $socialUser->avatar = 'foobar';
        $socialUser->name = 'foobar';
        $socialUser->user = ['first_name' => 'foo', 'last_name' => 'bar'];
        $mock->shouldReceive('with->user')
            ->once()
            ->andReturn($socialUser);
        $mock->shouldReceive('with->getCachedReturnUrl')
            ->once()
            ->andReturn('http://foo.bar');

        $this->getJson('/auth/social/facebook/callback');

        $this->assertResponseStatus(302);

        $this->assertTrue($this->response->headers->has('location'), 'Response has location header.');
        $locationHeader = $this->response->headers->get('location');

        // Get the returned token
        $tokenParam = parse_url($locationHeader, PHP_URL_QUERY);
        $this->assertStringStartsWith('jwtAuthToken=', $tokenParam);

        $token = str_replace('jwtAuthToken=', '', $tokenParam);

        $decoded = $this->app->make('auth')->getTokenizer()->decode($token);

        $this->assertEquals('facebook', $decoded['method']);
        $this->assertTrue($this->response->headers->has('location'), 'Response has location header.');
        $this->assertStringStartsWith('http://foo.bar', $this->response->headers->get('location'));

        // Assert that the social login was created
        $user = User::find($decoded['sub']);
        $socialLogin = $user->socialLogins->first()->toArray();
        $this->assertEquals('facebook', $socialLogin['provider']);
    }

    public function testSingleSignOnVanillaNoParameters()
    {
        $this->getJson('/auth/sso/vanilla');

        $this->assertResponseStatus(200);
        $this->assertContains('parameter is missing', $this->response->getContent());
    }

    public function testSingleSignOnVanillaValidClient()
    {
        $params = ['client_id' => env('VANILLA_JSCONNECT_CLIENT_ID')];
        $this->call('GET', '/auth/sso/vanilla', $params);

        $this->assertResponseStatus(200);
        $this->assertContains('name', $this->response->getContent());
    }

    public function testSingleSignOnVanillaWithUser()
    {
        $user = $this->createUser();
        $token = $this->tokenFromUser($user);

        $params = ['client_id' => env('VANILLA_JSCONNECT_CLIENT_ID')];
        $cookies = ['ngJwtAuthToken' => $token];
        $this->call('GET', '/auth/sso/vanilla', $params, $cookies);

        $response = json_decode($this->response->getContent());

        $this->assertResponseStatus(200);
        $this->assertContains($user->username, $response->name);
        $this->assertObjectNotHasAttribute('email', $response);
        $this->assertObjectNotHasAttribute('uniqueid', $response);
    }

    public function testSingleSignOnVanillaWithUserAndSignature()
    {
        $user = $this->createUser();
        $token = $this->tokenFromUser($user);
        $timestamp = time();

        $params = [
            'client_id' => env('VANILLA_JSCONNECT_CLIENT_ID'),
            'timestamp' => $timestamp,
            'signature' => sha1($timestamp.env('VANILLA_JSCONNECT_SECRET')),
        ];

        $cookies = ['ngJwtAuthToken' => $token];
        $this->call('GET', '/auth/sso/vanilla', $params, $cookies);

        $response = json_decode($this->response->getContent());

        $this->assertResponseStatus(200);
        $this->assertContains($user->user_id, $response->uniqueid);
        $this->assertContains($user->email, $response->email);
    }
}
