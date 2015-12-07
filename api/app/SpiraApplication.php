<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace app;

use Laravel\Lumen\Application;
use Monolog\Handler\SyslogUdpHandler;

class SpiraApplication extends Application
{
    /**
     * Set the error handling for the application.
     *
     * @return void
     */
    protected function registerErrorHandling()
    {
        parent::registerErrorHandling();

        // Don't display additional errors on top of the exception being rendered
        // @codeCoverageIgnoreStart
        ini_set('display_errors', 0);
        if ('cli' !== php_sapi_name() && (! ini_get('log_errors') || ini_get('error_log'))) {
            // CLI - display errors only if they're not already logged to STDERR
            ini_set('display_errors', 1);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Get the Monolog handler for the application.
     *
     * @return \Monolog\Handler\AbstractHandler
     */
    protected function getMonologHandler()
    {
        if (env('LOG_UDP_HOST')) {
            return new SyslogUdpHandler(env('LOG_UDP_HOST'), env('LOG_UDP_PORT'));
        }

        // @codeCoverageIgnoreStart
        return parent::getMonologHandler();
        // @codeCoverageIgnoreEnd
    }

    /**
     * @codeCoverageIgnore
     * Determine if the error type is fatal.
     *
     * @param  int  $type
     * @return bool
     */
    protected function isFatalError($type)
    {
        // *** Add type 16777217 that HVVM returns for fatal
        return in_array($type, [16777217, E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE]);
    }

    /**
     * Lumen has so much magic in DI container
     * The only way to override default drivers, Guard and so on
     * Is disable it with this method.
     */
    protected function registerAuthBindings()
    {
        $this->configure('auth');

        return;
    }
}
