<?php


use Faker\Factory as Faker;

class UserStorySeeder extends Seeder {

    private $faker;

    public function __construct(){

        $this->faker = Faker::create('au_AU');

    }

    private function createUser($email = null, $seed = null){


        $faker = $this->faker;

        if ($seed){

            $faker->seed($seed);
        }

        if ($email == null){
            $email = $faker->email;
        }

        $user = new User([
            'user_id' => $faker->uuid,
            'email' => $email,
            'password' => Hash::make('password'),
            'first_name' => $faker->firstName,
            'last_name' => $faker->lastName,
            'phone' => $faker->phoneNumber,
            'mobile' => $faker->phoneNumber,
        ]);

        $user->timestamps = true;
        $user->save();

    }

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{

        $this->createUser('john.smith@example.com');

        foreach(range(0, 99) as $index){

            $this->createUser();

        }



	}

}
