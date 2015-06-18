<?php

class MiddlewareTest extends TestCase
{
    /**
     * Test TransformInputData middleware.
     *
     * @return void
     */
    public function testTransformInputData()
    {
        $mw = new App\Http\Middleware\TransformInputData;

        // Create a request object to test
        $request = new Illuminate\Http\Request;
        $request->offsetSet('firstName', 'foo');
        $request->offsetSet('lastname', 'bar');

        // And a next closure
        $next = function($request) { return $request; };

        // Execute
        $request = $mw->handle($request, $next);

        // Assert
        $this->assertArrayHasKey('first_name', $request->all());
        $this->assertArrayNotHasKey('firstName', $request->all());
        $this->assertArrayHasKey('lastname', $request->all());
    }
}
