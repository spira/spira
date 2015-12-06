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

    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__.'/../bootstrap/app.php';
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
}
