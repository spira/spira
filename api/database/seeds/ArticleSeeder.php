<?php

use App\Models\ArticlePermalink;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //$faker = Faker::create('au_AU');
        factory(App\Models\Article::class, 10)
            ->create()
            ->each(function (\App\Models\Article $article) {
                $permalinks = factory(ArticlePermalink::class, rand(0, 4))->make()->all();
                foreach ($permalinks as $permalink) {
                    $article->permalinks->add($permalink);
                }
                $metas = factory(\App\Models\ArticleMeta::class, 4)->make()->all();
                foreach ($metas as $meta) {
                    $article->metas->add($meta);
                }
                $article->push();
            })
        ;
    }
}
