<?php

use GuzzleHttp\Client;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tymon\JWTAuth\Claims\Expiration;
use Tymon\JWTAuth\Claims\IssuedAt;
use Tymon\JWTAuth\Claims\Issuer;
use Tymon\JWTAuth\Claims\JwtId;
use Tymon\JWTAuth\Claims\NotBefore;
use Tymon\JWTAuth\Claims\Subject;
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
        $this->assertArrayHasKey('userId', $array['decodedTokenBody']['#user']);
    }

    public function testFailedLogin()
    {
        $this->markTestSkipped('must be revisited');
        $user = factory(App\Models\User::class)->create();

        $this->get('/auth/jwt/login', [
            'PHP_AUTH_USER' => $user->email,
            'PHP_AUTH_PW'   => 'foobar',
        ]);

        $body = json_decode($this->response->getContent());
        $this->assertResponseStatus(401);
        $this->assertContains('failed', $body->message);
    }

    public function testFailedTokenEncoding()
    {
        $this->markTestSkipped('must be revisited');
        $user = factory(App\Models\User::class)->create();
        $this->app->config->set('jwt.algo', 'foobar');

        $this->get('/auth/jwt/login', [
            'PHP_AUTH_USER' => $user->email,
            'PHP_AUTH_PW'   => 'password',
        ]);

        $this->assertException('token', 500, 'RuntimeException');
    }

    public function testRefresh()
    {
        $this->markTestSkipped('must be revisited');
        $user = factory(App\Models\User::class)->create();
        $jwtAuth = $this->app->make('Tymon\JWTAuth\JWTAuth');
        $token = $jwtAuth->fromUser($user);

        $this->callRefreshToken($token);

        $object = json_decode($this->response->getContent());
        $this->assertResponseOk();

        $this->assertNotEquals($token, $object->token);
    }

    public function testRefreshPlainHeader()
    {
        $this->markTestSkipped('must be revisited');
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
        $this->markTestSkipped('must be revisited');
        $jwtAuth = $this->app->make('Tymon\JWTAuth\JWTAuth');

        $claims = [
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

        $token = $jwtAuth->encode($payload);

        $this->callRefreshToken($token);

        $body = json_decode($this->response->getContent());
        $this->assertResponseStatus(401);
        $this->assertContains('expired', $body->message);
    }

    public function testRefreshInvalidTokenSignature()
    {
        $this->markTestSkipped('must be revisited');
        $user = factory(App\Models\User::class)->create();
        $jwtAuth = $this->app->make('Tymon\JWTAuth\JWTAuth');
        $token = $jwtAuth->fromUser($user);

        // Replace the signature with an invalid string
        $segments = explode('.', $token);
        $segments[2] = 'foobar';
        $token = implode('.', $segments);

        $this->callRefreshToken($token);

        $this->assertException('Signature could not', 422, 'UnprocessableEntityException');
    }

    public function testRefreshInvalidToken()
    {
        $this->markTestSkipped('must be revisited');
        $token = 'foo.bar.baz';

        $this->callRefreshToken($token);

        $this->assertException('invalid', 422, 'UnprocessableEntityException');
    }

    public function testRefreshMissingToken()
    {
        $this->markTestSkipped('must be revisited');
        $this->get('/auth/jwt/refresh');

        $this->assertException('not provided', 400, 'BadRequestException');
    }

    public function testRefreshMissingUser()
    {
        $this->markTestSkipped('must be revisited');
        $user = factory(App\Models\User::class)->make();
        $jwtAuth = $this->app->make('Tymon\JWTAuth\JWTAuth');
        $token = $jwtAuth->fromUser($user);

        $this->callRefreshToken($token);

        $this->assertException('not exist', 500, 'RuntimeException');
    }

    public function testToken()
    {
        $this->markTestSkipped('must be revisited');
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
        $this->markTestSkipped('must be revisited');
        $this->get('/auth/jwt/token');

        $this->assertException('not provided', 400, 'BadRequestException');
    }

    public function testInvalidToken()
    {
        $this->markTestSkipped('must be revisited');
        $token = 'invalid';
        $this->get('/auth/jwt/token', [
            'HTTP_AUTHORIZATION' => 'Token '.$token,
        ]);

        $this->assertResponseStatus(401);
    }

    public function testTokenInvalid()
    {
        $this->markTestSkipped('must be revisited');
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
        $this->markTestSkipped('must be revisited');
        $repo = $this->app->make('App\Repositories\UserRepository');
        $user = factory(App\Models\User::class)->create();

        $token = $repo->makeLoginToken($user->user_id);

        $id = Cache::pull('login_token_'.$token);

        $this->assertEquals($id, $user->user_id);
    }
}
