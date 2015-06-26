<?php

use App\Models\TestEntity;
use Illuminate\Database\Seeder;

class TestEntitySeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{

        factory(App\Models\TestEntity::class, 20)->create();

	}

}
