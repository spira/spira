<?php

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
        ini_set('display_errors', 0);
        if ('cli' !== php_sapi_name() && (!ini_get('log_errors') || ini_get('error_log'))) {
            // CLI - display errors only if they're not already logged to STDERR
            ini_set('display_errors', 1);
        }
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
}
