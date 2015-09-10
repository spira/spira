<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use Illuminate\Http\Request;

class SpiraAuthTest extends TestCase
{
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
}
