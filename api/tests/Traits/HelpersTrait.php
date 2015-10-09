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

trait HelpersTrait
{
    /**
     * Keep the array with unique data, to avoid query the db multiple times.
     *
     * @var  array
     */
    protected $uniqueUserValues;

    /**
     * @return Faker
     */
    protected function getFakerWithUniqueUserData()
    {
        // Prepare an array with user data already used
        $users = User::all();
        if (! $this->uniqueUserValues) {
            $uniques = ['username' => [], 'email' => []];
            foreach ($users as $user) {
                array_push($uniques['username'], [$user->username => null]);
                array_push($uniques['email'], [$user->email => null]);
            }

            $this->uniqueUserValues = $uniques;
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
        $uniqueBinder($this->uniqueUserValues);

        return $faker;
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
            'user_type' => 'admin',
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

    protected function tokenFromUser($user, $customClaims = [])
    {
        /** @var Guard $auth */
        $auth = $this->app->make('auth');
        $payload = $auth->getPayloadFactory()->createFromUser($user);
        $payload = array_merge($payload, $customClaims);
        return $auth->getTokenizer()->encode($payload);
    }
}
