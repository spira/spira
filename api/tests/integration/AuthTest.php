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
use Illuminate\Support\Facades\Cache;
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
        $this->markTestSkipped('why check that?');
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

        // Test that decoding the token, will match the user
        $payload = $this->app->make('auth')->getTokenizer()->decode($array['token']);
        $this->assertEquals($user->user_id, $payload['sub']);
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
            'iat' => 123
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


}
