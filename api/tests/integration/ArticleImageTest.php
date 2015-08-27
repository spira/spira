<?php

use App\Models\Article;
use App\Models\ArticleImage;
use App\Models\Image;

class ArticleImageTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        App\Models\Article::flushEventListeners();
        App\Models\Article::boot();

        App\Models\Image::flushEventListeners();
        App\Models\Image::boot();

        App\Models\ArticleImage::flushEventListeners();
        App\Models\ArticleImage::boot();
        // Workaround for model event firing.
        // The package Bosnadev\Database used for automatic UUID creation relies
        // on model events (creating) to generate the UUID.
        //
        // Laravel/Lumen currently doesn't fire repeated model events during
        // unit testing, see: https://github.com/laravel/framework/issues/1181
    }

    /**
     * Prepare a factory generated entity to be sent as input data.
     *
     * @param Arrayable $entity
     *
     * @return array
     */
    protected function prepareEntity($entity)
    {
        // We run the entity through the transformer to get the attributes named
        // as if they came from the frontend.
        $transformer = $this->app->make(\App\Http\Transformers\EloquentModelTransformer::class);
        $entity = $transformer->transform($entity);
        return $entity;
    }

    protected function addImagesToArticle(\App\Models\Article $article)
    {
        $images = factory(Image::class, 5)->create();
        foreach ($images as $image) {
            $imageArticle = factory(ArticleImage::class)->make();
            $imageArticle->article_id = $article->article_id;
            $imageArticle->image_id = $image->image_id;
            $imageArticle->save();
        }
    }

    public function testGetAll()
    {
        $article = factory(App\Models\Article::class)->create();
        $this->addImagesToArticle($article);

        $this->getJson('/articles/'.$article->article_id.'/images');
        $object = json_decode($this->response->getContent());

        $this->assertResponseOk();
        $this->shouldReturnJson();
        $this->assertJsonArray();
        $this->assertJsonMultipleEntries();

        $this->assertEquals(5, count($object));
        $this->assertObjectHasAttribute('_image',current($object));
    }

    public function testPutManyNew()
    {
        $article = factory(App\Models\Article::class)->create();

        $images = factory(Image::class, 5)->create();
        $articleImages = [];
        foreach ($images as $image) {
            $imageArticle = factory(ArticleImage::class)->make();
            $imageArticle->article_id = $article->article_id;
            $imageArticle->image_id = $image->image_id;
            $articleImages[]=$imageArticle;
        }

        $images = array_map(function ($entity) {
            return $this->prepareEntity($entity);
        }, $articleImages);

        $childCount = \App\Models\Article::find($article->article_id)->imagesPivot->count();

        $this->putJson('/articles/'.$article->article_id.'/images', $images);

        $object = json_decode($this->response->getContent());

        $this->assertResponseStatus(201);
        $this->assertEquals($childCount + 5, \App\Models\Article::find($article->article_id)->imagesPivot->count());
        $this->assertTrue(is_array($object));
        $this->assertCount(5, $object);
    }


    public function testPutManyNewInvalid()
    {
        $article = factory(App\Models\Article::class)->create();

        $images = factory(Image::class, 5)->create();
        $articleImages = [];
        foreach ($images as $image) {
            $imageArticle = factory(ArticleImage::class)->make();
            $imageArticle->article_id = $article->article_id;
            $imageArticle->image_id = $image->image_id;
            $articleImages[]=$imageArticle;
        }

        $images = array_map(function ($entity) {
            return $this->prepareEntity($entity);
        }, $articleImages);

        foreach ($images as &$image) {
            unset($image['articleId']);
        }

        $childCount = \App\Models\Article::find($article->article_id)->imagesPivot->count();

        $this->putJson('/articles/'.$article->article_id.'/images', $images);

        $object = json_decode($this->response->getContent());

        $this->assertCount(5, $object->invalid);
        $this->assertObjectHasAttribute('articleId', $object->invalid[0]);
        $this->assertEquals($childCount, \App\Models\Article::find($article->article_id)->imagesPivot->count());
    }



    public function testDeleteMany()
    {
        /** @var Article $article */
        $article = factory(App\Models\Article::class)->create();
        $this->addImagesToArticle($article);

        $childCount = \App\Models\Article::find($article->article_id)->imagesPivot->count();

        $images = array_map(function ($entity) {
            return $this->prepareEntity($entity);
        }, $article->imagesPivot->all());

        $this->deleteJson('/articles/'.$article->article_id.'/images', $images);

        $this->assertResponseStatus(204);
        $this->assertResponseHasNoContent();
        $this->assertEquals($childCount - 5, \App\Models\Article::find($article->article_id)->imagesPivot->count());
    }

}