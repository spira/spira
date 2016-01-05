<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use App\Models\User;

class TestCase extends \Spira\Core\tests\TestCase
{
    const TEST_ADMIN_USER_EMAIL = 'john.smith@example.com';

    const TEST_USER_EMAIL = 'nick.jackson@example.com';

    use HelpersTrait;

    public static $envVarOverrides = [
        'APP_ENV' => 'testing',
        'CACHE_DRIVER' => 'redis',
        'SESSION_DRIVER' => 'array',
        'QUEUE_DRIVER' => 'sync',
    ];

    public static $envVarOriginals = [];

    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__.'/../bootstrap/app.php';
    }

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::registerEnvironmentOverrides();
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        self::deregisterEnvironmentOverrides();
    }

    /**
     * @param null $header
     * @return TestCase
     */
    public function withAuthorization($header = null)
    {
        if (is_null($header)) {
            return $this->withUserAuthorization($this->getTestUser());
        }

        $this->authHeader = $header;

        return $this;
    }

    /**
     * @return TestCase
     */
    public function withUserAuthorization(User $user)
    {
        return $this->withAuthorization('Bearer '.$this->tokenFromUser($user));
    }

    /**
     * @return TestCase
     */
    public function withAdminAuthorization()
    {
        return $this->withUserAuthorization($this->getAdminUser());
    }

    /**
     * @return TestCase
     */
    public function withoutAuthorization()
    {
        $this->authHeader = null;

        return $this;
    }

    /**
     * @return User
     */
    public function getTestUser()
    {
        return (new App\Models\User())->findByEmail(static::TEST_USER_EMAIL);
    }

    /**
     * @return User
     */
    public function getAdminUser()
    {
        return (new App\Models\User())->findByEmail(static::TEST_ADMIN_USER_EMAIL);
    }

    /**
     * PHPUnit allows for environment variables to be set by the phpunit.xml file, however this only works
     * if the variable was not yet defined. This is a problem in dockerised environments where the environment variables
     * for the runtime are registered on container boot. Instead we register the overrides here and force the override.
     * @link https://phpunit.de/manual/current/en/appendixes.configuration.html#appendixes.configuration.php-ini-constants-variables
     */
    protected static function registerEnvironmentOverrides()
    {
        foreach (self::$envVarOverrides as $var => $value) {
            if ($original = getenv($var)) {
                self::$envVarOriginals[$var] = $original;
            }

            putenv("$var=$value");
        }
    }

    protected static function deregisterEnvironmentOverrides()
    {
        foreach (self::$envVarOriginals as $var => $value) {
            putenv("$var=$value");
        }
    }
}
