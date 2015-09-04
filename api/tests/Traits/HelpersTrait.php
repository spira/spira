<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use App\Models\User;
use App\Models\UserProfile;
use Faker\Factory as Faker;

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
     * @param  int    $times
     *
     * @return User|void
     */
    protected function createUser(array $attributes = [], $times = 1)
    {
        for ($i = 0; $i < $times; $i++) {
            $faker = $this->getFakerWithUniqueUserData();
            $default = [
                'email' => $faker->unique()->email,
                'username' => $faker->unique()->username,
                'user_type' => 'admin',
            ];
            $attr = array_merge($default, $attributes);

            $user = factory(User::class)->create($attr);

            $user->setProfile(factory(UserProfile::class)->make());
        }

        if ($times === 1) {
            return $user;
        } else {
            return;
        }
    }

    protected function tokenFromUser($user, $customClaims = [])
    {
        $cfg = $this->app->config->get('jwt');
        $validator = new Tymon\JWTAuth\Validators\PayloadValidator;
        $request = new Illuminate\Http\Request;
        $claimFactory = new Tymon\JWTAuth\Claims\Factory;

        $adapter = new App\Extensions\JWTAuth\NamshiAdapter($cfg['secret'], $cfg['algo']);
        $payloadFactory = new App\Extensions\JWTAuth\PayloadFactory($claimFactory, $request, $validator);

        $claims = ['sub' => $user->user_id, '_user' => $user];
        $claims = array_merge($customClaims, $claims);

        $payload = $payloadFactory->make($claims);

        $token = $adapter->encode($payload->get());

        return $token;
    }
}
