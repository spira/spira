<?php

use App\Models\Article;
use App\Models\Tag;
use Spira\Model\Collection\Collection;

class ArticleTagTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        // Workaround for model event firing.
        // The package Bosnadev\Database used for automatic UUID creation relies
        // on model events (creating) to generate the UUID.
        //
        // Laravel/Lumen currently doesn't fire repeated model events during
        // unit testing, see: https://github.com/laravel/framework/issues/1181
        App\Models\Article::flushEventListeners();
        App\Models\Article::boot();
    }

    protected function addTagsToArticles($articles)
    {
        /** @var Article[] $articles */
        foreach ($articles as $article) {
            /** @var Collection $tags */
            $tags = factory(\App\Models\Tag::class, 4)->create();
            $article->tags()->sync($tags->lists('tag_id')->toArray());
        }
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

    public function testGetTags()
    {
        $entity = factory(Article::class)->create();
        $this->addTagsToArticles([$entity]);

        $count = Article::find($entity->article_id)->tags->count();
        $this->get('/articles/'.$entity->article_id.'/tags');
        $object = json_decode($this->response->getContent());

        $this->assertResponseOk();
        $this->shouldReturnJson();

        $this->assertEquals(count($object), $count);
    }

    /**
     * Current scenario is tested
     * Say we got 5 tags for article
     * foo, bar, zoo, dar, kar
     *
     * In request we put only "foo" + 4 new tags
     * So "bar, zoo, dar, kar" are detached from article, "foo" remains and 4 new tags created
     */
    public function testPutTags()
    {
        $entity = factory(Article::class)->create();
        $this->addTagsToArticles([$entity]);

        // re-acquire for collection to have ids as key
        $entity = Article::find($entity->article_id);

        $previousTagsWillBeRemoved = $entity->tags;
        $existingTagWillStay = $entity->tags->first();

        $newTags = array_map(function ($entity) {
            return $this->prepareEntity($entity);
        }, factory(\App\Models\Tag::class, 4)->make()->all());
        array_push($newTags,$this->prepareEntity($existingTagWillStay));

        $this->put('/articles/'.$entity->article_id.'/tags', ['data' => $newTags]);

        $this->assertResponseStatus(201);

        $updatedArticle = Article::find($entity->article_id);
        $updatedTags = $updatedArticle->tags->toArray();

        $this->assertArrayHasKey($existingTagWillStay->tag_id, $updatedTags);
        foreach ($previousTagsWillBeRemoved as $removedTag) {
            if ($removedTag->tag_id == $existingTagWillStay->tag_id){
                continue;
            }
            $this->assertArrayNotHasKey($removedTag->tag_id, $updatedTags);
        }

        $this->assertEquals(5, count($updatedTags));

    }

    public function testGetTagGlobal()
    {
        $tag = factory(\App\Models\Tag::class)->create();
        $this->get('/tags/'.$tag->tag);

        $object = json_decode($this->response->getContent());

        $this->assertResponseOk();
        $this->shouldReturnJson();

        $this->assertTrue(is_object($object), 'Response is an object');

        $this->assertObjectHasAttribute('_self', $object);
        $this->assertTrue(is_string($object->_self), '_self is a string');

        $this->assertObjectHasAttribute('tagId', $object);
        $this->assertObjectHasAttribute('tag', $object);
    }

    public function testPostTagGlobal()
    {
        $tag = factory(\App\Models\Tag::class)->make();

        $this->post('/tags', $this->prepareEntity($tag));

        $this->shouldReturnJson();

        $object = json_decode($this->response->getContent());

        $this->assertResponseStatus(201);
        $this->assertTrue(is_object($object));
        $this->assertStringStartsWith('http', $object->_self);
    }

    public function testPostTagInvalid()
    {
        $tag = factory(\App\Models\Tag::class)->make();
        $tag->tag = '%$@""';
        $this->post('/tags', $this->prepareEntity($tag));

        $this->shouldReturnJson();
        $this->assertResponseStatus(422);
        $object = json_decode($this->response->getContent());
        $this->assertObjectHasAttribute('invalid', $object);
        $this->assertObjectHasAttribute('tag', $object->invalid);
        $this->assertEquals('The tag may only contain letters, numbers, dashes and spaces.', $object->invalid->tag[0]->message);

    }

    public function testPatchTagGlobal()
    {
        $tag = factory(\App\Models\Tag::class)->create();
        $id = $tag->tag_id;
        $tag->tag = 'foo';

        $this->patch('/tags/'.$id, $this->prepareEntity($tag));

        $this->shouldReturnJson();
        $this->assertResponseStatus(204);
        $checkEntity = Tag::find($id);
        $this->assertEquals($checkEntity->tag, $tag->tag);
    }

    public function testPatchTagInvalid()
    {
        $tag = factory(\App\Models\Tag::class)->create();
        $id = $tag->tag_id;
        $tag->tag = '%$@""';

        $this->patch('/tags/'.$id, $this->prepareEntity($tag));
        $this->shouldReturnJson();
        $this->assertResponseStatus(422);
        $object = json_decode($this->response->getContent());
        $this->assertObjectHasAttribute('invalid', $object);
        $this->assertObjectHasAttribute('tag', $object->invalid);
        $this->assertEquals('The tag may only contain letters, numbers, dashes and spaces.', $object->invalid->tag[0]->message);
    }

    public function testDeleteTagGlobal()
    {
        $entity = factory(Article::class)->create();
        $this->addTagsToArticles([$entity]);

        $this->assertEquals(4, Article::find($entity->article_id)->tags->count());
        $tag = $entity->tags->first();


        $this->delete('/tags/'.$tag->tag_id);

        $this->assertResponseStatus(204);
        $this->assertResponseHasNoContent();
        $this->assertEquals(3, Article::find($entity->article_id)->tags->count());

    }

}