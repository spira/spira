<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use App\Exceptions\NotImplementedException;
use App\Services\SingleSignOn\SingleSignOnFactory;
use App\Services\SingleSignOn\VanillaSingleSignOn;

class SingleSignOnTest extends TestCase
{
    protected function mockRequest($clientId = false, $timestamp = false, $signature = false, $callback = false)
    {
        $request = Mockery::mock('Illuminate\Http\Request');
        $request->shouldReceive('has')->with('client_id')->andReturn($clientId);
        $request->shouldReceive('get')->with('client_id')->andReturn($clientId);

        $request->shouldReceive('has')->with('timestamp')->andReturn($timestamp);
        $request->shouldReceive('get')->with('timestamp')->andReturn($timestamp);

        $request->shouldReceive('has')->with('signature')->andReturn($signature);
        $request->shouldReceive('get')->with('signature')->andReturn($signature);

        $request->shouldReceive('has')->with('callback')->andReturn($callback);
        $request->shouldReceive('get')->with('callback')->andReturn($callback);

        return $request;
    }

    public function testUnknownRequester()
    {
        $this->setExpectedExceptionRegExp(
            NotImplementedException::class,
            '/exists/',
            0
        );

        $request = Mockery::mock('Illuminate\Http\Request');
        $requester = SingleSignOnFactory::create('foobar', $request, null);
    }

    public function testGetMappedRoles()
    {
        $request = Mockery::mock('Illuminate\Http\Request');

        $user = $this->createUser();
        $this->assignAdmin($user);
        $this->assignTest($user);

        $requester = SingleSignOnFactory::create('vanilla', $request, $user);
        $roles = $requester->formatUser()['roles'];

        $this->assertContains('administrator', $roles);
        $this->assertContains('member', $roles);
        $this->assertContains('testrole', $roles);
    }

    public function testMissingClient()
    {
        $user = null;
        $request = $this->mockRequest();

        $requester = SingleSignOnFactory::create('vanilla', $request, $user);
        $response = $requester->getResponse();

        $this->assertContains('invalid_request', $response);
        $this->assertContains('client_id parameter is missing', $response);
    }

    public function testInvalidClient()
    {
        $user = null;
        $clientId = 'foobar';
        $request = $this->mockRequest($clientId);

        $requester = SingleSignOnFactory::create('vanilla', $request, $user);
        $response = $requester->getResponse();

        $this->assertContains('invalid_client', $response);
        $this->assertContains('Unknown client '.$clientId, $response);
    }

    public function testInvalidTimestamp()
    {
        $user = null;
        $clientId = env('VANILLA_JSCONNECT_CLIENT_ID');
        $timestamp = 'foo';
        $request = $this->mockRequest($clientId, $timestamp);

        $requester = SingleSignOnFactory::create('vanilla', $request, $user);
        $response = $requester->getResponse();

        $this->assertContains('invalid_request', $response);
        $this->assertContains('timestamp parameter', $response);
    }

    public function testMissingSignature()
    {
        $user = null;
        $clientId = env('VANILLA_JSCONNECT_CLIENT_ID');
        $timestamp = time();
        $request = $this->mockRequest($clientId, $timestamp);

        $requester = SingleSignOnFactory::create('vanilla', $request, $user);
        $response = $requester->getResponse();

        $this->assertContains('invalid_request', $response);
        $this->assertContains('signature parameter', $response);
    }

    public function testExpiredTimestamp()
    {
        $user = null;
        $clientId = env('VANILLA_JSCONNECT_CLIENT_ID');
        $timestamp = time() - 3600;
        $signature = 'foo';
        $request = $this->mockRequest($clientId, $timestamp, $signature);

        $requester = SingleSignOnFactory::create('vanilla', $request, $user);
        $response = $requester->getResponse();

        $this->assertContains('invalid_request', $response);
        $this->assertContains('timestamp is invalid', $response);
    }

    public function testInvalidSignature()
    {
        $user = null;
        $clientId = env('VANILLA_JSCONNECT_CLIENT_ID');
        $timestamp = time();
        $signature = 'foo';
        $request = $this->mockRequest($clientId, $timestamp, $signature);

        $requester = SingleSignOnFactory::create('vanilla', $request, $user);
        $response = $requester->getResponse();

        $this->assertContains('access_denied', $response);
        $this->assertContains('Signature invalid', $response);
    }

    public function testNoUser()
    {
        $user = null;
        $clientId = env('VANILLA_JSCONNECT_CLIENT_ID');
        $timestamp = time();
        $signature = sha1($timestamp.env('VANILLA_JSCONNECT_SECRET'));
        $request = $this->mockRequest($clientId, $timestamp, $signature);

        $requester = SingleSignOnFactory::create('vanilla', $request, $user);
        $response = $requester->getResponse();

        $response = json_decode($response);

        $this->assertEmpty($response->name);
        $this->assertEmpty($response->photourl);
    }

    public function testUnsecureUser()
    {
        $user = $this->createUser();
        $this->assignAdmin($user);
        $this->assignTest($user);

        $clientId = env('VANILLA_JSCONNECT_CLIENT_ID');
        $timestamp = time();
        $signature = sha1($timestamp.env('VANILLA_JSCONNECT_SECRET'));
        $request = $this->mockRequest($clientId, $timestamp, $signature);

        $requester = SingleSignOnFactory::create('vanilla', $request, $user);
        $requester->setSecure(null);
        $response = $requester->getResponse();

        $response = json_decode($response);

        $this->assertObjectHasAttribute('name', $response);
        $this->assertObjectNotHasAttribute('client_id', $response);
        $this->assertObjectNotHasAttribute('signature', $response);
    }

    public function testPublicWithUser()
    {
        $user = $this->createUser();
        $this->assignAdmin($user);
        $this->assignTest($user);

        $clientId = env('VANILLA_JSCONNECT_CLIENT_ID');
        $request = $this->mockRequest($clientId);

        $requester = SingleSignOnFactory::create('vanilla', $request, $user);
        $response = $requester->getResponse();

        $response = json_decode($response);

        $this->assertObjectHasAttribute('name', $response);
        $this->assertObjectHasAttribute('photourl', $response);
        $this->assertObjectNotHasAttribute('email', $response);
        $this->assertObjectNotHasAttribute('uniqueid', $response);
    }

    public function testPublicWithNoUser()
    {
        $user = null;
        $clientId = env('VANILLA_JSCONNECT_CLIENT_ID');
        $request = $this->mockRequest($clientId);

        $requester = SingleSignOnFactory::create('vanilla', $request, $user);
        $response = $requester->getResponse();

        $response = json_decode($response);

        $this->assertObjectHasAttribute('name', $response);
        $this->assertObjectHasAttribute('photourl', $response);
        $this->assertEmpty($response->name);
        $this->assertEmpty($response->photourl);
    }

    public function testCallback()
    {
        $user = null;
        $callback = 'categories';
        $request = $this->mockRequest(false, false, false, $callback);

        $requester = SingleSignOnFactory::create('vanilla', $request, $user);
        $response = $requester->getResponse();

        $this->assertStringStartsWith($callback.'(', $response);
        $this->assertStringEndsWith(')', $response);
    }

    public function testSignNoReturnData()
    {
        $user = [
            'username' => null,
            'avatar_img_url' => null,
        ];

        $requester = Mockery::mock(VanillaSingleSignOn::class);
        $response = $requester->sign($user, 'sha1');

        $this->assertFalse(is_array($response));
    }

    public function testHash()
    {
        $string = 'foobar';
        $requester = Mockery::mock(VanillaSingleSignOn::class);

        $response = $requester->hash($string, true);
        $this->assertEquals(md5($string), $response);

        $response = $requester->hash($string, false);
        $this->assertEquals(md5($string), $response);

        $response = $requester->hash($string, 'sha1');
        $this->assertEquals(sha1($string), $response);

        $response = $requester->hash($string, 'sha256');
        $this->assertEquals(hash('sha256', $string), $response);
    }

    public function testSsoString()
    {
        $user = [
            'username' => null,
            'avatar_img_url' => null,
        ];

        $callback = 'categories';
        $request = $this->mockRequest(false, false, false, $callback);

        $ssoClass = Mockery::mock(VanillaSingleSignOn::class, [$request, $user]);

        $ssoString = $ssoClass->ssoString($user);
        $ssoStringPieces = explode(' ', $ssoString);

        $string = $ssoStringPieces[0];
        $hash = $ssoStringPieces[1];
        $timestamp = $ssoStringPieces[2];
        $algo = $ssoStringPieces[3];

        $user['client_id'] = env('VANILLA_JSCONNECT_CLIENT_ID');

        $this->assertEquals(base64_encode(json_encode($user)), $string);
        $this->assertEquals(hash_hmac('sha1', "$string $timestamp", env('VANILLA_JSCONNECT_SECRET')), $hash);
        $this->assertEquals('hmacsha1', $algo);
    }
}
