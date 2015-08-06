<?php

namespace Spira;

use Migrate;
use Composer\Script\Event;

class Build
{
    /**
     * Runs after a composer install.
     *
     * @param   Event   $event  [description]
     * @return  void
     */
    public static function postInstall(Event $event)
    {
        self::getInstance()->buildVanillaApp();
    }

    /**
     * Runs after a composer update.
     *
     * @param   Event   $event  [description]
     * @return  void
     */
    public static function postUpdate(Event $event)
    {
        self::getInstance()->buildVanillaApp();
    }

    /**
     * Combine the retrieved packages to the required application.
     *
     * @return void
     */
    protected function buildVanillaApp()
    {
        // Copy the main application to public
        $this->recurseCopy('vendor/vanilla/vanilla', 'public');

        // Copy the API module inside the application directory
        $this->recurseCopy('vendor/kasperisager/vanilla-api', 'public/applications/api');

        // Copy the SSO plugin to the plugins directory
        $this->recurseCopy('vendor/vanilla/addons/plugins/jsconnect', 'public/plugins/jsconnect');

        // Copy the initial configuration to conf directory
        copy('config.php', 'public/conf/config.php');

        // Setup Vanilla
        (new Migrate)->start();
    }

    /**
     * Recursively copy a directory structure.
     *
     * @param  string  $source
     * @param  string  $dest
     * @return void
     */
    protected function recurseCopy($source, $dest)
    {
        $dir = opendir($source);
        @mkdir($dest);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($source . '/' . $file)) {
                    $this->recurseCopy($source . '/' . $file, $dest . '/' . $file);
                } else {
                    copy($source . '/' . $file, $dest . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    /**
     * Get an instance of the application builder.
     *
     * @return Build
     */
    protected static function getInstance()
    {
        return new Build;
    }
}
