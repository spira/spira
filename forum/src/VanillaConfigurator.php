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

        $this->configureJsConnect();

        $this->configureApiModule();
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
        define('APPLICATION_VERSION', $this->getVanillaVersion());
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
            'Name' => getenv('VANILLA_ADMIN_NAME'),
            'Email' => getenv('VANILLA_ADMIN_EMAIL'),
            'Password' => getenv('VANILLA_ADMIN_PASSWORD')
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
     * Setup a provider in jsConnect.
     *
     * @return void
     */
    protected function configureJsConnect()
    {
        $provider = [
            'AuthenticationKey' => getenv('VANILLA_JSCONNECT_CLIENT_ID'),
            'AssociationSecret' => getenv('VANILLA_JSCONNECT_SECRET'),
            'AssociationHashMethod' => 'md5',
            'AuthenticationSchemeAlias' => 'jsconnect',
            'Name' => 'Spira',
            'AuthenticateUrl' => getenv('API_HOST').'/auth/sso/vanilla',
            'Attributes' => serialize([
                'HashType' => 'sha1',
                'TestMode' => false,
                'Trusted' => true
            ]),
            'Active' => true,
            'IsDefault' => true
        ];

        Gdn::SQL()->Options('Ignore', true)->Insert('UserAuthenticationProvider', $provider);
    }

    /**
     * Enable and setup API module.
     *
     * @return void
     */
    protected function configureApiModule()
    {
        saveToConfig([
            'EnabledApplications.api' => 'api',
            'API.Secret' => getenv('VANILLA_API_SECRET'),
            'API.Version' => $this->getApiVersion(),
        ]);
    }

    /**
     * Get Vanilla version string.
     *
     * @return string
     */
    protected function getVanillaVersion()
    {
        $file = 'public/index.php';
        $pattern = '/\'APPLICATION_VERSION\', \'([a-z0-9.]*)\'/';

        return $this->getStringFromFile($file, $pattern);
    }

    /**
     * Get API version string.
     *
     * @return string
     */
    protected function getApiVersion()
    {
        $file = 'public/applications/api/settings/about.php';
        $pattern = '/\'Version\'.*\'([0-9.]*)\'/';

        return $this->getStringFromFile($file, $pattern);
    }

    /**
     * Get string from a file.
     *
     * @param string $file
     * @param string $pattern
     *
     * @return string
     */
    protected function getStringFromFile($file, $pattern)
    {
        $lines = file($file);

        foreach ($lines as $line) {
            preg_match($pattern, $line, $matches);
            if ($matches) {
                return $matches[1];
            }
        }
    }
}
