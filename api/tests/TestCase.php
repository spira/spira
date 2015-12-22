<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

class TestCase extends \Spira\Core\tests\TestCase
{
    const TEST_ADMIN_USER_EMAIL = 'john.smith@example.com';

    const TEST_USER_EMAIL = 'nick.jackson@example.com';

    use HelpersTrait;

    public $envVarOverrides = [
        'APP_ENV' => 'testing',
        'CACHE_DRIVER' => 'redis',
        'SESSION_DRIVER' => 'array',
        'QUEUE_DRIVER' => 'sync',
    ];

    public $envVarOriginals = [];

    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__.'/../bootstrap/app.php';
    }

    public function setUp()
    {
        parent::setUp();

        $this->registerEnvironmentOverrides();
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->deregisterEnvironmentOverrides();
    }

    /**
     * @param null $header
     * @return $this
     */
    public function withAuthorization($header = null)
    {
        if (is_null($header)) {
            $user = (new App\Models\User())->findByEmail(static::TEST_USER_EMAIL);
            $header = 'Bearer '.$this->tokenFromUser($user);
        }
        $this->authHeader = $header;

        return $this;
    }

    /**
     * PHPUnit allows for environment variables to be set by the phpunit.xml file, however this only works
     * if the variable was not yet defined. This is a problem in dockerised environments where the environment variables
     * for the runtime are registered on container boot. Instead we register the overrides here and force the override.
     * @link https://phpunit.de/manual/current/en/appendixes.configuration.html#appendixes.configuration.php-ini-constants-variables
     */
    protected function registerEnvironmentOverrides()
    {
        foreach ($this->envVarOverrides as $var => $value) {
            if ($original = getenv($var)) {
                $this->envVarOriginals[$var] = $original;
            }

            putenv("$var=$value");
        }
    }

    protected function deregisterEnvironmentOverrides()
    {
        foreach ($this->envVarOriginals as $var => $value) {
            putenv("$var=$value");
        }
    }
}
