<?php

use App\Models\Article;
use App\Models\ArticleImage;
use App\Models\ArticleMeta;
use App\Models\Image;
use App\Models\Tag;
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
            ->each(function (Article $article){

                //add metas
                $metas = factory(ArticleMeta::class, 2)->make()->all();
                $article->metas()->saveMany($metas);

                //add permalinks
                $permalinks = factory(ArticlePermalink::class, rand(0, 4))->make()->all();
                $article->permalinks()->saveMany($permalinks);

                //add tags
                $tags = factory(Tag::class, 2)->make()->all();
                $article->tags()->saveMany($tags);


                //create & link images
                factory(Image::class, 5)
                    ->create()
                    ->each(function (Image $image) use ($article) {
                        factory(ArticleImage::class)->create([
                            'article_id' => $article->article_id,
                            'image_id' => $image->image_id,
                        ]);
                    });

            })
        ;
    }
}
