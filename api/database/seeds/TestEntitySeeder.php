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

        foreach(range(0, 20) as $index){

            TestEntity::fakeTestEntity();

        }

	}

}
