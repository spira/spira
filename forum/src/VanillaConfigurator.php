<?php

/**
 * We can not namespace this class as we need to interact with Vanilla's classes
 * that are not following any namespace or psr schema. So we must make sure this
 * class is operating outside namespaces to avoid problems.
 */
class VanillaConfigurator
{
    /**
     * Assign dependencies.
     *
     * @return  void
     */
    public function __construct()
    {
    }

    public function start()
    {
        $this->quickBootstrap();

        $this->migrate();

        $this->config();

        $this->adminUser();

        $this->enableApplications();
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
        define('APPLICATION_VERSION', $this->getVersion());
        define('PATH_ROOT', getcwd().'/public');

        // Boostrap Vanilla
        require_once('public/bootstrap.php');
    }

    /**
     * Create the database tables and default content.
     *
     * @return  void
     */
    protected function migrate()
    {
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
     * Update the configuration.
     *
     * @return void
     */
    protected function config()
    {
        saveToConfig('Garden.Cookie.Salt', RandomString(10));

        $ApplicationInfo = [];
        include(CombinePaths([PATH_APPLICATIONS.DS.'dashboard'.DS.'settings'.DS.'about.php']));

        // Detect Internet connection for CDNs
        $Disconnected = !(bool)@fsockopen('ajax.googleapis.com', 80);

        saveToConfig([
            'Garden.Version' => arrayValue('Version', val('Dashboard', $ApplicationInfo, []), 'Undefined'),
            'Garden.Cdns.Disable' => $Disconnected,
            'Garden.CanProcessImages' => function_exists('gd_info'),
            'EnabledPlugins.HtmLawed' => 'HtmLawed'
        ]);
    }

    /**
     * Create the default admin user.
     *
     * @return void
     */
    protected function adminUser()
    {
        $UserModel = Gdn::userModel();
        $UserModel->defineSchema();

        $user = [
            'Name' => 'admin',
            'Email' => 'admin@admin.com',
            'Password' => 'password'
        ];

        $AdminUserID = $UserModel->SaveAdminUser($user);
    }

    /**
     * Enable applications and create permisisions for roles.
     *
     * @return void
     */
    protected function enableApplications()
    {
        $ApplicationManager = new Gdn_ApplicationManager();
        $AppNames = c('Garden.Install.Applications', ['Conversations', 'Vanilla']);

        foreach ($AppNames as $AppName) {
            $Validation = new Gdn_Validation();
            $ApplicationManager->RegisterPermissions($AppName, $Validation);
            $ApplicationManager->EnableApplication($AppName, $Validation);
        }

        Gdn::pluginManager()->start(true);

        // Flag the application as installed
        saveToConfig('Garden.Installed', true);

        // Setup default permissions for all roles
        PermissionModel::ResetAllRoles();
    }

    /**
     * Get Vanilla version from Vanilla's index.php.
     *
     * @return string
     */
    protected function getVersion()
    {
        $lines = file('public/index.php');

        foreach ($lines as $line) {
            $pattern = '/\'APPLICATION_VERSION\', \'([a-z0-9.]*)\'/';
            preg_match($pattern, $line, $matches);
            if ($matches) {
                return $matches[1];
            }
        }
    }
}
