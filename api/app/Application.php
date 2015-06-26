<?php namespace App;

use Illuminate\Support\Facades\Storage;
use Laravel\Lumen\Application;
use Monolog\Handler\SyslogUdpHandler;
use Wpb\StringBladeCompiler\Facades\StringView;

class SpiraApplication extends Application
{

    /**
     * Get the Monolog handler for the application.
     *
     * @return \Monolog\Handler\AbstractHandler
     */
    protected function getMonologHandler()
    {

        if (env('LOG_UDP_HOST')){
            return new SyslogUdpHandler(env('LOG_UDP_HOST'), env('LOG_UDP_PORT'));
        }

        return parent::getMonologHandler();
    }


}
