<?php

namespace App\Services;

use Illuminate\Http\RedirectResponse;

/**
 * This class is temporary fixing session requirement while making redirect response because of Lumen 5.2 doesn't support sessions
 *
 * It should override redirect() method dependency, but there is call of new \Laravel\Lumen\Http\Redirector
 */
class Redirector extends \Laravel\Lumen\Http\Redirector
{
    /** @inherit */
    protected function createRedirect($path, $status, $headers)
    {
        $redirect = new RedirectResponse($path, $status, $headers);
        $redirect->setRequest($this->app->make('request'));

        return $redirect;
    }
}