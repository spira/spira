<?php namespace App\Models;

use Faker\Factory as Faker;
use Illuminate\Support\Facades\Hash;

class User extends BaseModel {
    /**
     * The database table used by the model.
     *
     * @var string
     */
    public $table = 'users';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'first_name', 'last_name', 'email', 'password', 'reset_token', 'phone', 'mobile'];
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'reset_token'];

    protected $primaryKey = 'user_id';

    /**
     * Get the access route for the entity.
     *
     * @return string
     */
    public function entityRoute()
    {
        return '/users';
    }

    /**
     * Generate fake user
     * @param array $overrides
     * @param null $seed
     * @return User
     */
    public static function fakeUser($overrides = [], $seed = null){

        $faker = Faker::create('au_AU');;

        if ($seed){
            $faker->seed($seed);
        }

        $userInfo = array_merge([
            'user_id' => $faker->uuid,
            'email' => $faker->email,
            'password' => Hash::make('password'),
            'first_name' => $faker->firstName,
            'last_name' => $faker->lastName,
            'phone' => $faker->optional(0.5)->phoneNumber,
            'mobile' => $faker->optional(0.5)->phoneNumber,
        ], $overrides);

        $user = new User($userInfo);

        $user->timestamps = true;
        $user->save();

        return $user;

    }

}