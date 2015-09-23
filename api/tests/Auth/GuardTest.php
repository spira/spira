<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use Illuminate\Contracts\Auth\Authenticatable;
use Mockery\Mock;
use Spira\Auth\Blacklist\Blacklist;
use Spira\Auth\Driver\Guard;
use Spira\Auth\Payload\PayloadFactory;
use Spira\Auth\Payload\PayloadValidationFactory;
use Spira\Auth\Token\JWTInterface;
use Spira\Auth\Token\RequestParser;
use Spira\Auth\Token\TokenIsMissingException;
use Spira\Auth\User\UserProvider;
use Spira\Contract\Exception\NotImplementedException;
use Mockery as m;

class GuardTest extends TestCase
{
    /**
     * @return Guard|Mock
     */
    protected function getGuard()
    {
        return m::mock(Guard::class)->makePartial();
    }

    public function testLogin()
    {
        $guard = $this->getGuard();
        $user = m::mock(Authenticatable::class);
        $guard->login($user);
        $this->assertEquals($user, $guard->user());
    }

    public function testToken()
    {
        $guard = $this->getGuard();
        $guard->shouldReceive('generateToken')->once()->andReturn('token');

        $this->assertNull($guard->token());
        $user = m::mock(Authenticatable::class);
        $guard->login($user);
        $this->assertEquals($user, $guard->user());
        $this->assertTrue($guard->check());
        $this->assertEquals('token', $guard->token());
    }

    public function testLogout()
    {
        $guard = $this->getGuard();
        $user = m::mock(Authenticatable::class);
        $guard->login($user);
        $this->assertEquals($user, $guard->user());

        //gettin token from request
        $request = m::mock(Illuminate\Http\Request::class);
        $requestParser = m::mock(RequestParser::class);
        $requestParser->shouldReceive('getToken')->once()->andReturn('some token');

        //transforming token to payload
        $tokenizer = m::mock(JWTInterface::class);
        $tokenizer->shouldReceive('decode')->once()->andReturn(['some data']);

        //adding payload to blacklist
        $blackList = m::mock(Blacklist::class);
        $blackList->shouldReceive('add')->once()->andReturnNull();

        $guard
            ->shouldReceive('getRequest')->once()->andReturn($request)
            ->shouldReceive('getRequestParser')->once()->andReturn($requestParser)
            ->shouldReceive('getTokenizer')->once()->andReturn($tokenizer)
            ->shouldReceive('getBlacklist')->once()->andReturn($blackList);

        $guard->logout();
        $this->assertNull($guard->user());
    }

    public function testAuth_from_request()
    {
        $guard = $this->getGuard();
        $this->assertFalse($guard->viaToken());

        $user = m::mock(Authenticatable::class);
        $guard->shouldReceive('getUserFromRequest')->once()->andReturn($user);

        $this->assertEquals($user, $guard->user());
        $this->assertTrue($guard->viaToken());
    }

    public function testAuth_from_request_no_token()
    {
        $guard = $this->getGuard();
        $this->assertFalse($guard->viaToken());

        $user = null;
        $guard->shouldReceive('getUserFromRequest')->once()->andThrowExceptions([new TokenIsMissingException()]);

        $this->assertEquals($user, $guard->user());
        $this->assertFalse($guard->viaToken());
    }

    public function testLogin_via_id_success()
    {
        $provider = m::mock(UserProvider::class);
        $user = m::mock(Authenticatable::class);
        $provider->shouldReceive('retrieveById')->once()->andReturn($user);
        $guard = $this->getGuard();
        $guard->shouldReceive('getProvider')->once()->andReturn($provider);
        $result = $guard->loginUsingId('fake data');
        $this->assertEquals($user, $result);
    }

    public function testLogin_via_id_fail()
    {
        $provider = m::mock(UserProvider::class);
        $user = null;
        $provider->shouldReceive('retrieveById')->once()->andReturn($user);
        $guard = $this->getGuard();
        $guard->shouldReceive('getProvider')->once()->andReturn($provider);
        $result = $guard->loginUsingId('fake data');
        $this->assertEquals($user, $result);
    }

    public function testAttempt_success()
    {
        $provider = m::mock(UserProvider::class);
        $user = m::mock(Authenticatable::class);
        $provider
            ->shouldReceive('retrieveByCredentials')->once()->andReturn($user)
            ->shouldReceive('validateCredentials')->once()->andReturn(true);
        $guard = $this->getGuard();
        $guard->shouldReceive('getProvider')->twice()->andReturn($provider);
        $result = $guard->attempt(['some fake']);
        $this->assertTrue($result);
        $this->assertEquals($user, $guard->user());
    }

    public function testOnce_is_an_alias_of_attempt()
    {
        $provider = m::mock(UserProvider::class);
        $user = m::mock(Authenticatable::class);
        $provider
            ->shouldReceive('retrieveByCredentials')->once()->andReturn($user)
            ->shouldReceive('validateCredentials')->once()->andReturn(true);
        $guard = $this->getGuard();
        $guard->shouldReceive('getProvider')->twice()->andReturn($provider);
        $result = $guard->once(['some fake']);
        $this->assertTrue($result);
        $this->assertEquals($user, $guard->user());
    }

    public function testAttempt_with_invalid_credentials()
    {
        $provider = m::mock(UserProvider::class);
        $user = m::mock(Authenticatable::class);
        $provider
            ->shouldReceive('retrieveByCredentials')->once()->andReturn($user)
            ->shouldReceive('validateCredentials')->once()->andReturn(false);
        $guard = $this->getGuard();
        $guard->shouldReceive('getProvider')->twice()->andReturn($provider);
        $result = $guard->attempt(['some fake']);
        $this->assertFalse($result);
        $this->assertNull($guard->user());
    }

    public function testAttempt_fail()
    {
        $provider = m::mock(UserProvider::class);
        $user = false;
        $provider->shouldReceive('retrieveByCredentials')->once()->andReturn($user);
        $guard = $this->getGuard();
        $guard->shouldReceive('getProvider')->once()->andReturn($provider);
        $result = $guard->attempt(['some fake']);
        $this->assertFalse($result);
        $this->assertNull($guard->user());
    }

    public function testBasic()
    {
        $this->setExpectedException(NotImplementedException::class, 'Not Implemented.');
        $this->getGuard()->basic();
    }

    public function onceBasic()
    {
        $this->setExpectedException(NotImplementedException::class, 'Not Implemented.');
        $this->getGuard()->onceBasic();
    }

    public function testValidate()
    {
        $this->setExpectedException(NotImplementedException::class, 'Not Implemented.');
        $this->getGuard()->validate();
    }

    public function testViaRemember()
    {
        $this->setExpectedException(NotImplementedException::class, 'Not Implemented.');
        $this->getGuard()->viaRemember();
    }

    public function testCheck()
    {
        $guard = $this->getGuard();
        $this->assertFalse($guard->check());

        $user = m::mock(Authenticatable::class);
        $guard->login($user);

        $this->assertTrue($guard->check());
    }

    public function testGuest()
    {
        $guard = $this->getGuard();
        $this->assertTrue($guard->guest());

        $user = m::mock(Authenticatable::class);
        $guard->login($user);

        $this->assertFalse($guard->guest());
    }

    /**
     * covers getters and setters
     * also covers tricky generateToken and getUserFromRequest.
     */
    public function testRealLife()
    {
        $gate = new Guard(
            new FakeGuardTokenizer(),
            new FakePayloadFactory(),
            new FakePayloadValidationFactory(),
            new FakeUserProvider(),
            new FakeRequestParser(),
            new FakeBlacklist()
        );

        //request and provider are set inside lumen
        $gate->setRequest(new \Illuminate\Http\Request());
        $gate->setProvider(new FakeUserProvider());

        // no user at first
        $this->assertFalse($gate->check());

        // get user from the token in Request
        $user = $gate->user();
        $this->assertInstanceOf(Authenticatable::class, $user);
        $this->assertTrue($gate->check());
        $this->assertEquals('token', $gate->token());

        // logout
        $gate->logout();
        $this->assertFalse($gate->check());
        $this->assertNull($gate->user());
        $this->assertNull($gate->token());
    }
}

class FakeGuardTokenizer implements  JWTInterface
{
    public function encode(array $payload)
    {
        return 'token';
    }

    public function decode($token)
    {
        return ['foo' => 'bar'];
    }
}

class FakePayloadFactory extends PayloadFactory
{
    public function createFromUser(Authenticatable $user)
    {
        return ['foo' => 'bar'];
    }
}

class FakePayloadValidationFactory extends PayloadValidationFactory
{
    public function validatePayload($payload)
    {
    }
}

class FakeUserProvider extends UserProvider
{
    public function __construct()
    {
    }

    public function retrieveByToken($identifier, $token)
    {
        return new \Illuminate\Auth\GenericUser([]);
    }
}

class FakeRequestParser extends RequestParser
{
    public function getToken(\Illuminate\Http\Request $request)
    {
        return 'token';
    }
}

class FakeBlacklist extends Blacklist
{
    public function __construct()
    {
    }

    public function add($payload)
    {
    }

    public function check($payload)
    {
        return false;
    }
}
