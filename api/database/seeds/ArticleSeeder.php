<?php

use App\Models\Article;
use App\Models\ArticleMeta;
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

        factory(Article::class, 50)
            ->create()
            ->each(function (Article $article) {
                $permalinks = factory(ArticlePermalink::class, rand(0, 4))->make()->all();
                foreach ($permalinks as $permalink) {
                    $article->permalinks->add($permalink);
                }
                $metas = factory(ArticleMeta::class, 4)->make()->all();
                foreach ($metas as $meta) {
                    $article->metas->add($meta);
                }
                $article->push();
            })
        ;

        Article::addAllToIndex(); //push all articles to elastic search
    }
}
