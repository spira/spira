<?php

use Illuminate\Database\Seeder;

use Faker\Factory as Faker;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create('au_AU');

        factory(App\Models\Article::class, 10)
            ->create()
            ->each(function(\App\Models\Article $article) use ($faker) {

                foreach(range(0,$faker->numberBetween(0, 4)) as $index){
                    $permalink = factory(App\Models\ArticlePermalink::class)->make();
                    $permalink->article()->associate($article);
                    $permalink->save();
                }

            })
        ;
    }
}
