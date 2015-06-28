<?php

use Illuminate\Database\Seeder;
use App\Models\User;

class UserStorySeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
        factory(App\Models\User::class)->create([
            'email'=>'john.smith@example.com'
        ]);

        factory(App\Models\User::class, 99)->create();

	}

}
