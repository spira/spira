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
            ->each(function(\App\Models\Article $article) {
                $permalink = factory(App\Models\ArticlePermalink::class)->make();

                $permalink->article()->associate($article);
                $permalink->save();

                $article->currentPermalink()->associate($permalink);
                $article->save();

            });
    }
}
