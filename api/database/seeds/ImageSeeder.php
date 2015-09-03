<?php

use App\Models\Image;
use Illuminate\Database\Seeder;

class ImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        /**
         * @todo
         * the intent of this seeder is to first try to call the cloudinary api and seed the database with real
         * cloudinary public ids. If that fails then it will fall back to seeding mocks.
         */

        //create & link images
        factory(Image::class, 5)->create();
    }
}
