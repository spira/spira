<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spira;

use VanillaConfigurator;
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
     * Runs after a composer update.
     *
     * @param   Event   $event  [description]
     * @return  void
     */
    public static function buildForum(Event $event)
    {
        self::getInstance()->setupVanilla();
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

        // Copy the API Extended module inside the application directory
        $this->recurseCopy('src/apiextended', 'public/applications/apiextended');

        // Copy the SSO plugin to the plugins directory
        $this->recurseCopy('vendor/vanilla/addons/plugins/jsconnect', 'public/plugins/jsconnect');

        // Setup the database variables to be bootstrapped into memory
        copy('bootstrap.database.php', 'public/conf/bootstrap.early.php');

        // Override general functions
        copy('bootstrap.before.php', 'public/conf/bootstrap.before.php');
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
                if (is_dir($source.'/'.$file)) {
                    $this->recurseCopy($source.'/'.$file, $dest.'/'.$file);
                } else {
                    copy($source.'/'.$file, $dest.'/'.$file);
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
        return new self;
    }

    /**
     * Run the vanilla setup functions.
     */
    protected function setupVanilla()
    {
        copy('config.php', 'public/conf/config.php'); //also overwrites the config file for a repeated migration (for qa)
        file_put_contents(
            'public/index.php',
            str_replace(
                "'display_errors', 0",
                "'display_errors', 1",
                file_get_contents('public/index.php')
            )
        );

        (new VanillaConfigurator)->start();
    }
}
