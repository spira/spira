<?php

use App\Models\Image;
use App\Models\Article;
use App\Models\ArticleImage;

/**
 * Class ArticleImageTest
 * @group integration
 */
class ArticleImageTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        Article::flushEventListeners();
        Article::boot();

        Image::flushEventListeners();
        Image::boot();

        ArticleImage::flushEventListeners();
        ArticleImage::boot();
        // Workaround for model event firing.
        // The package Bosnadev\Database used for automatic UUID creation relies
        // on model events (creating) to generate the UUID.
        //
        // Laravel/Lumen currently doesn't fire repeated model events during
        // unit testing, see: https://github.com/laravel/framework/issues/1181
    }

    protected function addImagesToArticle(Article $article, $make = false)
    {
        $factory = $this->getFactory();
        $method = 'create';
        if ($make){
            $method = 'make';
        }

        $articleImages = [];
        $factory->get(Image::class)
                ->count(5)
                ->create()
                ->each(function (Image $image) use ($article, $factory, $method, &$articleImages) {
                    $articleImages[] = $factory->get(ArticleImage::class)->{$method}([
                        'article_id' => $article->article_id,
                        'image_id' => $image->image_id,
                    ]);
        });

        return new \Spira\Model\Collection\Collection($articleImages);
    }

    public function testGetAll()
    {
        $article = $this->getFactory()->get(Article::class)->create();
        $this->addImagesToArticle($article);
        $this->getJson('/articles/'.$article->article_id.'/article-images', ['With-Nested' => 'image']);
        $object = json_decode($this->response->getContent());

        $this->assertResponseOk();
        $this->shouldReturnJson();
        $this->assertJsonArray();
        $this->assertJsonMultipleEntries();

        $this->assertEquals(5, count($object));
        $this->assertObjectHasAttribute('_image', current($object));
    }

    public function testPutManyNew()
    {
        $article = $this->getFactory()->get(Article::class)->create();
        $images = $this->addImagesToArticle($article, true);

        $images = $this->getFactory()
            ->get($images)
            ->count(5)
            ->transformed();

        $childCount = Article::find($article->article_id)->articleImages->count();

        $this->putJson('/articles/'.$article->article_id.'/article-images', $images);

        $object = json_decode($this->response->getContent());

        $this->assertResponseStatus(201);
        $this->assertEquals($childCount + 5, Article::find($article->article_id)->articleImages->count());
        $this->assertTrue(is_array($object));
        $this->assertCount(5, $object);
    }


    public function testPutManyNewInvalid()
    {
        $article = $this->getFactory()->get(Article::class)->create();
        $images = $this->addImagesToArticle($article, true);

        $images = $this->getFactory()
            ->get($images)
            ->count(5)
            ->customize([
                'article_id'=>null
            ])
            ->transformed();

        $childCount = Article::find($article->article_id)->articleImages->count();

        $this->putJson('/articles/'.$article->article_id.'/article-images', $images);

        $object = json_decode($this->response->getContent());

        $this->assertCount(5, $object->invalid);
        $this->assertObjectHasAttribute('articleId', $object->invalid[0]);
        $this->assertEquals($childCount, Article::find($article->article_id)->articleImages->count());
    }



    public function testDeleteMany()
    {

        $article = $this->getFactory()->get(Article::class)->create();
        $images = $this->addImagesToArticle($article);

        $images = $this->getFactory()
            ->get($images)
            ->count(5)
            ->transformed();

        $childCount = Article::find($article->article_id)->articleImages->count();

        $this->deleteJson('/articles/'.$article->article_id.'/article-images', $images);

        $this->assertResponseStatus(204);
        $this->assertResponseHasNoContent();
        $this->assertEquals($childCount - 5, Article::find($article->article_id)->articleImages->count());
    }


    public function testGetManyImages()
    {
        $article = $this->getFactory()->get(Article::class)->create();
        $this->addImagesToArticle($article);

        $this->getJson('/articles/'.$article->article_id, ['With-Nested' => 'images']);
        $object = json_decode($this->response->getContent());

        $this->assertResponseStatus(200);
        $this->shouldReturnJson();

        $this->assertObjectHasAttribute('_images', $object);
        $this->assertCount(5, $object->_images);
    }
}
