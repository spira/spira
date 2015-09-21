<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use App\Models\ArticleComment;
use App\Models\Tag;
use App\Models\Image;
use App\Models\Article;
use App\Models\ArticleMeta;
use App\Models\ArticleImage;
use App\Models\ArticlePermalink;
use App\Models\User;

class ArticleSeeder extends BaseSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $images = Image::all();

        $users = User::all();

        factory(Article::class, 50)
            ->create()
            ->each(function (Article $article) use ($images, $users) {

                //add a meta tag
                $article->articleMetas()->save(factory(ArticleMeta::class)->make());

                //add permalinks
                $permalinks = factory(ArticlePermalink::class, 2)->make()->all();
                $article->articlePermalinks()->saveMany($permalinks);

                //add tags
                $tags = factory(Tag::class, 2)->make()->all();
                $article->tags()->saveMany($tags);

//                //add comments
//                factory(ArticleComment::class, rand(2,10))->make()
//                    ->each(function (ArticleComment $comment) use ($article, $users) {
//                        print_r($users->random()->toArray());
//                        $comment->setAuthor(1234);
//                        $article->comments()->save($comment->toArray());
//                    });

                $this->randomElements($images)
                    ->each(function (Image $image) use ($article) {
                    factory(ArticleImage::class)->create([
                        'article_id' => $article->article_id,
                        'image_id' => $image->image_id,
                    ]);
                });

            });
    }
}
