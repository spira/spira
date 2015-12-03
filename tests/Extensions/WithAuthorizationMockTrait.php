<?php

namespace Spira\Core\tests\Extensions;

use Illuminate\Auth\GenericUser;
use Illuminate\Http\Request;

trait WithAuthorizationMockTrait
{
    /**
     * @param null $header
     * @return $this
     */
    public function withAuthorization($header = null)
    {
        static $app;
        if ($this->app !== $app) {
            $this->app[Request::class];
            $this->app->rebinding(Request::class, function ($app, Request $request) {
                $request->setUserResolver(function () use ($app) {
                    return new GenericUser(['id' => 'some_id']);
                });
            });
            $app = $this->app;
        }

        return $this;
    }
}
