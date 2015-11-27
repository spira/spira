<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use App\Models\User;
use Faker\Factory as Faker;
use Spira\Auth\Driver\Guard;
use Spira\Rbac\Storage\StorageInterface;

trait HelpersTrait
{
    /**
     * Keep the array with unique data, to avoid query the db multiple times.
     *
     * @var  array
     */
    protected static $userFaker;

    /**
     * @return Faker
     */
    protected function getFakerWithUniqueUserData()
    {
        if (is_null(self::$userFaker)) {
            // Prepare an array with user data already used
            $users = User::all();

            $uniques = ['username' => [], 'email' => []];
            foreach ($users as $user) {
                $uniques['username'][$user->username] = null;
                $uniques['email'][$user->email] = null;
            }

            // As the array of already used faker data is protected in Faker and
            // has no accessor method, we'll rely on ReflectionObject to modify
            // the property before letting faker generate data.

            //though reflected object should be added to the faker itself somehow
            // which is hacky
            //so we decided to overcome it with bindTo hack
            $faker = Faker::create();
            $unique = $faker->unique();

            $binder = function ($value) {
                $this->uniques = $value;
            };

            $uniqueBinder = $binder->bindTo($unique, $unique);
            $uniqueBinder($uniques);
            self::$userFaker = $faker;
        }

        return self::$userFaker;
    }

    /**
     * @param  array  $attributes
     *
     * @return User
     */
    protected function createUser(array $attributes = [])
    {
        $faker = $this->getFakerWithUniqueUserData();
        $default = [
            'email' => $faker->unique()->email,
            'username' => $faker->unique()->username,
        ];
        $attr = array_merge($default, $attributes);

        $user = factory(User::class)->create($attr);

        return $user;
    }

    /**
     * @param int $count
     */
    protected function createUsers($count = 10)
    {
        for ($i = 0; $i < $count; $i++) {
            $this->createUser();
        }
    }

    /**
     * @return Spira\Rbac\Storage\Storage
     */
    protected function getAuthStorage()
    {
        return $this->app->make(StorageInterface::class);
    }

    /**
     * Validates Response is a JSON and returns it as an object.
     *
     * @return stdClass|array
     */
    protected function getJsonResponseAsObject()
    {
        return $this->getJsonResponse(false);
    }

    /**
     * Validates Response is a JSON and returns it as an array.
     *
     * @return array
     */
    protected function getJsonResponseAsArray()
    {
        return $this->getJsonResponse(true);
    }

    /**
     * Validates Response is a JSON and returns it as an array or object.
     *
     * @param bool $asArray
     * @return array
     */
    protected function getJsonResponse($asArray = false)
    {
        $this->shouldReturnJson();

        return json_decode($this->response->getContent(), $asArray);
    }

    protected function assignSuperAdmin($user)
    {
        $this->assignRole($user, 'superAdmin');
    }

    protected function assignAdmin($user)
    {
        $this->assignRole($user, 'admin');
    }

    protected function assignTest($user)
    {
        $this->assignRole($user, 'testrole');
    }

    protected function assignRole($user, $roleName)
    {
        $someRole = $this->getAuthStorage()->getItem($roleName);
        $this->getAuthStorage()->assign($someRole, $user->user_id);
    }

    protected function tokenFromUser($user, $customClaims = [])
    {
        $user = User::findOrFail($user->user_id);

        /** @var Guard $auth */
        $auth = $this->app->make('auth');
        $payload = $auth->getPayloadFactory()->createFromUser($user);
        $payload = array_merge($payload, $customClaims);

        return $auth->getTokenizer()->encode($payload);
    }
}
