<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{

        $this->call('UserStorySeeder');
        $this->call('TestEntitySeeder');

        $this->command->info('User table seeded!');
	}

}
