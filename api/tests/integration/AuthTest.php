<?php

use Tymon\JWTAuth\Token;
use Tymon\JWTAuth\Payload;
use Tymon\JWTAuth\Claims\Issuer;
use Tymon\JWTAuth\Claims\IssuedAt;
use Tymon\JWTAuth\Claims\Expiration;
use Tymon\JWTAuth\Claims\NotBefore;
use Tymon\JWTAuth\Claims\Audience;
use Tymon\JWTAuth\Claims\Subject;
use Tymon\JWTAuth\Claims\JwtId;
use Tymon\JWTAuth\Claims\Custom;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AuthTest extends TestCase
{
    use DatabaseTransactions;

    protected function callRefreshToken($token)
    {
        $this->get('/auth/jwt/refresh', [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token
        ]);
    }

    protected function assertRuntimeError($message)
    {
        $body = json_decode($this->response->getContent());
        $this->assertResponseStatus(500);
        $this->assertContains($message, $body->message);
    }

    public function testLogin()
    {
        $user = factory(App\Models\User::class)->create();

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
        $user = factory(App\Models\User::class)->create();

        $this->get('/auth/jwt/login', [
            'PHP_AUTH_USER' => $user->email,
            'PHP_AUTH_PW'   => 'foobar',
        ]);

        $this->assertResponseStatus(401);
    }

    public function testFailedTokenEncoding()
    {
        $user = factory(App\Models\User::class)->create();
        $this->app->config->set('jwt.algo', 'foobar');

        $this->get('/auth/jwt/login', [
            'PHP_AUTH_USER' => $user->email,
            'PHP_AUTH_PW'   => 'password',
        ]);

        $this->assertResponseStatus(500);
    }

    public function testRefresh()
    {
        $user = factory(App\Models\User::class)->create();
        $jwtAuth = $this->app->make('Tymon\JWTAuth\JWTAuth');
        $token = $jwtAuth->fromUser($user);

        $this->callRefreshToken($token);

        $object = json_decode($this->response->getContent());
        $this->assertResponseOk();

        $this->assertNotEquals($token, $object->token);
    }

    public function testRefreshExpiredToken()
    {
        $jwtAuth = $this->app->make('Tymon\JWTAuth\JWTAuth');

        $claims = [
            new Subject(1),
            new Issuer('http://foo.bar'),
            new Expiration(123 - 3600),
            new NotBefore(123),
            new IssuedAt(123),
            new JwtId('foo')
        ];

        $this->validator = Mockery::mock('Tymon\JWTAuth\Validators\PayloadValidator');
        $this->validator->shouldReceive('setRefreshFlow->check');
        $payload = new Payload($claims, $this->validator, true);

        $token = $jwtAuth->encode($payload);

        $this->callRefreshToken($token);

        $this->assertResponseStatus(401);
    }

    public function testRefreshInvalidToken()
    {
        $token = 'foo.bar.baz';

        $this->callRefreshToken($token);

        $this->assertRuntimeError('invalid');
    }

    public function testRefreshMissingToken()
    {
        $this->get('/auth/jwt/refresh');

        $this->assertRuntimeError('not provided');
    }

    public function testRefreshMissingUser()
    {
        $user = factory(App\Models\User::class)->make();
        $jwtAuth = $this->app->make('Tymon\JWTAuth\JWTAuth');
        $token = $jwtAuth->fromUser($user);

        $this->callRefreshToken($token);

        $this->assertRuntimeError('not exist');
    }

    public function testToken()
    {
        $token = 'foobar';
        $user = factory(App\Models\User::class)->make();
        $user->login_token = $token;
        $user->save();

        $this->get('/auth/jwt/token', [
            'HTTP_AUTHORIZATION' => 'Token '.$token
        ]);

        $array = json_decode($this->response->getContent(), true);
        $this->assertResponseOk();
        $this->assertArrayHasKey('token', $array);
    }

    public function testMissingToken()
    {
        $this->get('/auth/jwt/token');

        $this->assertRuntimeError('not provided.');
    }

    public function testInvalidToken()
    {
        $token = 'invalid';
        $this->get('/auth/jwt/token', [
            'HTTP_AUTHORIZATION' => 'Token '.$token
        ]);

        $this->assertResponseStatus(401);
    }

    public function testTokenInvalidated()
    {
        $token = 'foobar';
        $user = factory(App\Models\User::class)->make();
        $user->login_token = $token;
        $user->save();

        $this->get('/auth/jwt/token', [
            'HTTP_AUTHORIZATION' => 'Token '.$token
        ]);

        $this->assertResponseOk();

        $this->get('/auth/jwt/token', [
            'HTTP_AUTHORIZATION' => 'Token '.$token
        ]);

        $this->assertResponseStatus(401);
    }

    public function testMakeLoginToken()
    {
        $repo = $this->app->make('App\Repositories\UserRepository');
        $user = factory(App\Models\User::class)->create();

        $token = $repo->makeLoginToken($user->user_id);

        $user = $repo->find($user->user_id);

        $this->assertEquals($token, $user->login_token);
    }
}
