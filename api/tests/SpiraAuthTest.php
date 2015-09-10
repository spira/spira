<?php

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