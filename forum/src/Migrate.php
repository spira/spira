<?php

/**
 * We can not namespace this class as we need to interact with Vanilla's classes
 * that are not following any namespace or psr schema. So we must make sure this
 * class is operating outside namespaces to avoid problems.
 */
class Migrate
{
    /**
     * Assign dependencies.
     *
     * @return  void
     */
    public function __construct()
    {
    }

    public function migrate()
    {
        $this->quickBootstrap();

        // The structures and related classes that creates Vanilla's DB scheme
        // do more wild things like using undefined variables, merging booleans
        // as arrays etc etc. So we disable all errors except fatal errors, so
        // we can run these files.
        error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR);
        ini_set('display_errors', 0);

        // Create the database tables
        require_once('public/applications/dashboard/settings/structure.php');
        require_once('public/applications/vanilla/settings/structure.php');
        require_once('public/applications/conversations/settings/structure.php');
    }

    /**
     * Bootstrap the parts of Vanilla required for initializing DB.
     *
     * @return void
     */
    protected function quickBootstrap()
    {
        // Disable strict errors as Vanilla do some quite wild things when it
        // comes to class overrides that doesn't adhere to PHP strict standards.
        error_reporting(E_ALL ^ E_STRICT);

        // Trick Vanilla to believe we are on the setup page.
        $_GET['p'] = '/dashboard/setup';

        // Define initial constants
        define('DS', '/');
        define('APPLICATION', 'Vanilla');
        define('PATH_ROOT', getcwd().'/public');

        // Boostrap Vanilla
        require_once('public/bootstrap.php');
    }
}
