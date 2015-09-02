<?php

use Illuminate\Http\Request;

class MiddlewareTest extends TestCase
{
    /**
     * Test TransformInputData middleware.
     *
     * @return void
     */
    public function testTransformInputData()
    {
        $mw = new App\Http\Middleware\TransformInputDataMiddleware();

        // Create a request object to test
        $request = new Illuminate\Http\Request();
        $request->offsetSet('firstName', 'foo');
        $request->offsetSet('lastname', 'bar');

        // And a next closure
        $next = function ($request) { return $request; };

        // Execute
        $request = $mw->handle($request, $next);

        // Assert
        $this->assertArrayHasKey('first_name', $request->all());
        $this->assertArrayNotHasKey('firstName', $request->all());
        $this->assertArrayHasKey('lastname', $request->all());
    }

    public function testTransformInputDataNested()
    {
        $mw = new App\Http\Middleware\TransformInputDataMiddleware();

        // Create a request object to test
        $request = new Illuminate\Http\Request();
        $request->offsetSet('firstName', 'foo');
        $request->offsetSet('lastname', 'bar');
        $request->offsetSet('nestedArray', ['fooBar' => 'bar', 'foo' => 'bar', 'oneMore' => ['andThis' => true]]);

        // And a next closure
        $next = function ($request) { return $request; };

        // Execute
        $request = $mw->handle($request, $next);

        // Assert
        $this->assertArrayHasKey('first_name', $request->all());
        $this->assertArrayNotHasKey('firstName', $request->all());
        $this->assertArrayHasKey('lastname', $request->all());
        $this->assertArrayHasKey('nested_array', $request->all());
        $this->assertArrayHasKey('foo_bar', $request->nested_array);
        $this->assertArrayHasKey('and_this', $request->nested_array['one_more']);
    }

    public function testUserResolverMiddleware()
    {
        $user = $this->createUser();
        $token = $this->tokenFromUser($user);

        $request = $this->app->make(Request::class);
        $request->headers->set('Authorization', 'Bearer '.$token);

        $next = function ($request) { return $request; };
        $mw = $this->app->make('App\Http\Middleware\UserResolverMiddleware');

        $request = $mw->handle($request, $next);

        $this->assertEquals($user->user_id, $request->user()->user_id);
    }
}
