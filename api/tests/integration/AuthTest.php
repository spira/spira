<?php

use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Tymon\JWTAuth\Claims\Expiration;
use Tymon\JWTAuth\Claims\IssuedAt;
use Tymon\JWTAuth\Claims\Issuer;
use Tymon\JWTAuth\Claims\JwtId;
use Tymon\JWTAuth\Claims\NotBefore;
use Tymon\JWTAuth\Claims\Subject;
use App\Extensions\JWTAuth\UserClaim;
use Tymon\JWTAuth\Payload;
use Tymon\JWTAuth\Token;

/**
 * Class AuthTest
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
        $this->assertArrayHasKey('iss', $array['decodedTokenBody']);
        $this->assertArrayHasKey('userId', $array['decodedTokenBody']['_user']);
        $this->assertEquals('password', $array['decodedTokenBody']['method']);

        // Test that decoding the token, will match the decoded body
        $token = new Token($array['token']);
        $jwtAuth = $this->app->make('Tymon\JWTAuth\JWTAuth');
        $decoded = $jwtAuth->decode($token)->toArray();
        $this->assertEquals($decoded, $array['decodedTokenBody']);
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

    public function testFailedTokenEncoding()
    {
        $user = $this->createUser();
        $credential = factory(App\Models\UserCredential::class)->make();
        $credential->user_id = $user->user_id;
        $credential->save();

        $this->app->config->set('jwt.algo', 'foobar');

        $this->getJson('/auth/jwt/login', [
            'PHP_AUTH_USER' => $user->email,
            'PHP_AUTH_PW'   => 'password',
        ]);

        $this->assertException('token', 500, 'RuntimeException');
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
        $this->assertArrayHasKey('iss', $array['decodedTokenBody']);
        $this->assertArrayHasKey('userId', $array['decodedTokenBody']['_user']);
        $this->assertEquals('password', $array['decodedTokenBody']['method']);

        // Test that decoding the token, will match the decoded body
        $token = new Token($array['token']);
        $jwtAuth = $this->app->make('Tymon\JWTAuth\JWTAuth');
        $decoded = $jwtAuth->decode($token)->toArray();
        $this->assertEquals($decoded, $array['decodedTokenBody']);
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
        $this->assertArrayHasKey('iss', $array['decodedTokenBody']);
        $this->assertArrayHasKey('userId', $array['decodedTokenBody']['_user']);
        $this->assertEquals('password', $array['decodedTokenBody']['method']);

        // Test that decoding the token, will match the decoded body
        $token = new Token($array['token']);
        $jwtAuth = $this->app->make('Tymon\JWTAuth\JWTAuth');
        $decoded = $jwtAuth->decode($token)->toArray();
        $this->assertEquals($decoded, $array['decodedTokenBody']);
    }

    public function testRefresh()
    {
        $user = $this->createUser();
        $token = $this->tokenFromUser($user, ['method' => 'password']);

        $this->callRefreshToken($token);

        $object = json_decode($this->response->getContent());
        $this->assertResponseOk();
        $this->assertNotEquals($token, $object->token);
        $this->assertEquals('password', $object->decodedTokenBody->method);
    }

    public function testRefreshPlainHeader()
    {
        $user = User::first();
        $jwtAuth = $this->app->make('Tymon\JWTAuth\JWTAuth');
        $token = $jwtAuth->fromUser($user, ['method' => 'password']);

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
        $this->assertEquals('password', $array['decodedTokenBody']['method']);
    }

    public function testRefreshExpiredToken()
    {
        $user = $this->createUser();

        $claims = [
            new UserClaim($user),
            new Subject(1),
            new Issuer('http://foo.bar'),
            new Expiration(123 - 3600),
            new NotBefore(123),
            new IssuedAt(123),
            new JwtId('foo'),
        ];

        $validator = Mockery::mock('Tymon\JWTAuth\Validators\PayloadValidator');
        $validator->shouldReceive('setRefreshFlow->check');
        $payload = new Payload($claims, $validator, true);

        $cfg = $this->app->config->get('jwt');
        $adapter = new App\Extensions\JWTAuth\NamshiAdapter($cfg['secret'], $cfg['algo']);
        $token = $adapter->encode($payload->get());

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

        $this->assertException('Signature could not', 422, 'UnprocessableEntityException');
    }

    public function testRefreshInvalidToken()
    {
        $token = 'foo.bar.baz';

        $this->callRefreshToken($token);

        $this->assertException('invalid', 422, 'UnprocessableEntityException');
    }

    public function testRefreshMissingToken()
    {
        $this->getJson('/auth/jwt/refresh');

        $this->assertException('not provided', 400, 'BadRequestException');
    }

    public function testRefreshMissingUser()
    {
        $user = factory(User::class)->make();
        $token = $this->tokenFromUser($user);

        $this->callRefreshToken($token);

        $this->assertException('not exist', 500, 'RuntimeException');
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

        $this->assertException('not provided', 400, 'BadRequestException');
    }

    public function testInvalidToken()
    {
        $token = 'invalid';
        $this->getJson('/auth/jwt/token', [
            'HTTP_AUTHORIZATION' => 'Token '.$token,
        ]);

        $this->assertResponseStatus(401);
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

        $this->assertException('invalid', 401, 'UnauthorizedException');
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
        if (!$this->app->config->get('services.twitter.client_id')) {
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

        $this->assertEquals($url, $returnUrl);
    }

    public function testProviderCallbackNoEmail()
    {
        $mock = Mockery::mock('App\Extensions\Socialite\SocialiteManager');
        $this->app->instance('Laravel\Socialite\Contracts\Factory', $mock);
        $mock->shouldReceive('with->user')
            ->once()
            ->andReturn((object) [
                'email' => null,
                'token' => 'foobar'
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

        $token = new Token($token);
        $jwtAuth = $this->app->make('Tymon\JWTAuth\JWTAuth');
        $decoded = $jwtAuth->decode($token)->toArray();

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

        $token = new Token($token);
        $jwtAuth = $this->app->make('Tymon\JWTAuth\JWTAuth');
        $decoded = $jwtAuth->decode($token)->toArray();

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
        $cookies = [\App\Http\Controllers\AuthController::JWT_AUTH_TOKEN_COOKIE => $token];
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
            'signature' => sha1($timestamp.env('VANILLA_JSCONNECT_SECRET'))
        ];

        $cookies = [\App\Http\Controllers\AuthController::JWT_AUTH_TOKEN_COOKIE => $token];
        $this->call('GET', '/auth/sso/vanilla', $params, $cookies);

        $response = json_decode($this->response->getContent());

        $this->assertResponseStatus(200);
        $this->assertContains($user->user_id, $response->uniqueid);
        $this->assertContains($user->email, $response->email);
    }
}
