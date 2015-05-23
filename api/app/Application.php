<?php namespace App;

use Laravel\Lumen\Application;
use Monolog\Handler\SyslogUdpHandler;

class NglumeApplication extends Application
{

    /**
     * Get the Monolog handler for the application.
     *
     * @return \Monolog\Handler\AbstractHandler
     */
    protected function getMonologHandler()
    {
        return new SyslogUdpHandler(env('LOG_UDP_HOST'), env('LOG_UDP_PORT'));
    }


}
