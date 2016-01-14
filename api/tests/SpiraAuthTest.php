<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use Illuminate\Http\Request;
use Spira\Auth\Driver\Guard;

class SpiraAuthTest extends TestCase
{
    public function testRolesInToken()
    {
        $user = $this->createUser();
        $this->assignAdmin($user);

        $token = $this->tokenFromUser($user);

        /** @var Guard $auth */
        $auth = $this->app->make('auth');
        $payload = $auth->getTokenizer()->decode($token);
        $this->assertArrayHasKey('_user', $payload);
        $this->assertArrayNotHasKey('_roles', $payload['_user']);
        $this->assertArrayHasKey('roles', $payload['_user']);
        $this->assertCount(2, $payload['_user']['roles'], 'User has 2 roles - default and admin');
    }

    public function testRequestUserResolver()
    {
        $user = $this->createUser();
        $token = $this->tokenFromUser($user);

        /** @var Request $request */
        $request = $this->app->make(Request::class);
        $request->headers->set('Authorization', 'Bearer '.$token);
        $this->assertEquals($user->user_id, $request->user()->user_id);
    }

    public function testRequestAliasUserResolver()
    {
        $user = $this->createUser();
        $token = $this->tokenFromUser($user);

        /** @var Request $request */
        $request = $this->app->make('request');
        $request->headers->set('Authorization', 'Bearer '.$token);
        $this->assertEquals($user->user_id, $request->user()->user_id);
    }

    public function testRequestFacadeUserResolver()
    {
        $user = $this->createUser();
        $token = $this->tokenFromUser($user);

        $request = Illuminate\Support\Facades\Request::getFacadeRoot();
        $request->headers->set('Authorization', 'Bearer '.$token);
        $this->assertEquals($user->user_id, $request->user()->user_id);
    }
}
