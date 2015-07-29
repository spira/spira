<?php

use GuzzleHttp\Client;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tymon\JWTAuth\Claims\Expiration;
use Tymon\JWTAuth\Claims\IssuedAt;
use Tymon\JWTAuth\Claims\Issuer;
use Tymon\JWTAuth\Claims\JwtId;
use Tymon\JWTAuth\Claims\NotBefore;
use Tymon\JWTAuth\Claims\Subject;
use App\Extensions\JWTAuth\UserClaim;
use Tymon\JWTAuth\Payload;
use Tymon\JWTAuth\Token;

class AuthTest extends TestCase
{
    use DatabaseTransactions;

    protected function callRefreshToken($token)
    {
        $this->get('/auth/jwt/refresh', [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ]);
    }

    public function testLogin()
    {
        $user = factory(App\Models\User::class)->create();
        $credential = factory(App\Models\UserCredential::class)->make();
        $credential->user_id = $user->user_id;
        $credential->save();
        $this->get('/auth/jwt/login', [
            'PHP_AUTH_USER' => $user->email,
            'PHP_AUTH_PW'   => 'password',
        ]);

        $array = json_decode($this->response->getContent(), true);
        $this->assertResponseOk();
        $this->assertEquals('application/json', $this->response->headers->get('content-type'));
        $this->assertArrayHasKey('token', $array);
        $this->assertArrayHasKey('iss', $array['decodedTokenBody']);
        $this->assertArrayHasKey('userId', $array['decodedTokenBody']['_user']);

        // Test that decoding the token, will match the decoded body
        $token = new Token($array['token']);
        $jwtAuth = $this->app->make('Tymon\JWTAuth\JWTAuth');
        $decoded = $jwtAuth->decode($token)->toArray();
        $this->assertEquals($decoded, $array['decodedTokenBody']);
    }

    public function testFailedLogin()
    {
        $user = factory(App\Models\User::class)->create();

        $this->get('/auth/jwt/login', [
            'PHP_AUTH_USER' => $user->email,
            'PHP_AUTH_PW'   => 'foobar',
        ]);

        $body = json_decode($this->response->getContent());
        $this->assertResponseStatus(401);
        $this->assertContains('failed', $body->message);
    }

    public function testLoginEmptyPassword()
    {
        $user = factory(App\Models\User::class)->create();
        $credential = factory(App\Models\UserCredential::class)->make();
        $credential->user_id = $user->user_id;
        $credential->save();
        $this->get('/auth/jwt/login', [
            'PHP_AUTH_USER' => $user->email,
            'PHP_AUTH_PW'   => '',
        ]);

        $body = json_decode($this->response->getContent());
        $this->assertResponseStatus(401);
        $this->assertContains('failed', $body->message);
    }

    public function testLoginUserMissCredentials()
    {
        $user = factory(App\Models\User::class)->create();

        $this->get('/auth/jwt/login', [
            'PHP_AUTH_USER' => $user->email,
            'PHP_AUTH_PW'   => '',
        ]);

        $body = json_decode($this->response->getContent());
        $this->assertResponseStatus(401);
        $this->assertContains('failed', $body->message);
    }

    public function testFailedTokenEncoding()
    {
        $user = factory(App\Models\User::class)->create();
        $credential = factory(App\Models\UserCredential::class)->make();
        $credential->user_id = $user->user_id;
        $credential->save();

        $this->app->config->set('jwt.algo', 'foobar');

        $this->get('/auth/jwt/login', [
            'PHP_AUTH_USER' => $user->email,
            'PHP_AUTH_PW'   => 'password',
        ]);

        $this->assertException('token', 500, 'RuntimeException');
    }

    public function testRefresh()
    {
        $user = factory(App\Models\User::class)->create();
        $token = $this->tokenFromUser($user);

        $this->callRefreshToken($token);

        $object = json_decode($this->response->getContent());
        $this->assertResponseOk();
        $this->assertNotEquals($token, $object->token);
    }

    public function testRefreshPlainHeader()
    {
        $user = App\Models\User::first();
        $jwtAuth = $this->app->make('Tymon\JWTAuth\JWTAuth');
        $token = $jwtAuth->fromUser($user);

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
    }

    public function testRefreshExpiredToken()
    {
        $user = factory(App\Models\User::class)->create();

        $claims = [
            new UserClaim($user),
            new Subject(1),
            new Issuer('http://foo.bar'),
            new Expiration(123 - 3600),
            new NotBefore(123),
            new IssuedAt(123),
            new JwtId('foo'),
        ];

        $this->validator = Mockery::mock('Tymon\JWTAuth\Validators\PayloadValidator');
        $this->validator->shouldReceive('setRefreshFlow->check');
        $payload = new Payload($claims, $this->validator, true);

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
        $user = factory(App\Models\User::class)->create();
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
        $this->get('/auth/jwt/refresh');

        $this->assertException('not provided', 400, 'BadRequestException');
    }

    public function testRefreshMissingUser()
    {
        $user = factory(App\Models\User::class)->make();
        $token = $this->tokenFromUser($user);

        $this->callRefreshToken($token);

        $this->assertException('not exist', 500, 'RuntimeException');
    }

    public function testToken()
    {
        $token = 'foobar';
        $user = factory(App\Models\User::class)->create();
        Cache::put('login_token_'.$token, $user->user_id, 1);

        $this->get('/auth/jwt/token', [
            'HTTP_AUTHORIZATION' => 'Token '.$token,
        ]);

        $array = json_decode($this->response->getContent(), true);
        $this->assertResponseOk();
        $this->assertArrayHasKey('token', $array);
    }

    public function testMissingToken()
    {
        $this->get('/auth/jwt/token');

        $this->assertException('not provided', 400, 'BadRequestException');
    }

    public function testInvalidToken()
    {
        $token = 'invalid';
        $this->get('/auth/jwt/token', [
            'HTTP_AUTHORIZATION' => 'Token '.$token,
        ]);

        $this->assertResponseStatus(401);
    }

    public function testTokenInvalid()
    {
        $token = 'foobar';
        $user = factory(App\Models\User::class)->create();
        Cache::put('login_token_'.$token, $user->user_id, 1);

        $this->get('/auth/jwt/token', [
            'HTTP_AUTHORIZATION' => 'Token '.$token,
        ]);

        $this->assertResponseOk();

        $this->get('/auth/jwt/token', [
            'HTTP_AUTHORIZATION' => 'Token '.$token,
        ]);

        $this->assertException('invalid', 401, 'UnauthorizedException');
    }

    public function testMakeLoginToken()
    {
        $repo = $this->app->make('App\Repositories\UserRepository');
        $user = factory(App\Models\User::class)->create();

        $token = $repo->makeLoginToken($user->user_id);

        $id = Cache::pull('login_token_'.$token);

        $this->assertEquals($id, $user->user_id);
    }
}
