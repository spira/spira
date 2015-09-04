<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use App\Models\Article;
use App\Models\Tag;
use Spira\Model\Collection\Collection;

/**
 * Class ArticleTagTest.
 * @group integration
 */
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

    /**
     * @param $articles
     * @param bool|false $same
     */
    protected function addTagsToArticles($articles, $same = false)
    {
        $tags = null;
        if ($same) {
            /** @var Collection $tags */
            $tags = $this->getFactory()->get(\App\Models\Tag::class)->count(4)->create();
        }
        /** @var Article[] $articles */
        foreach ($articles as $article) {
            if (! $same) {
                /** @var Collection $tags */
                $tags = $this->getFactory()->get(\App\Models\Tag::class)->count(4)->create();
            }

            $article->tags()->sync($tags->lists('tag_id')->toArray());
        }
    }

    public function testGetTags()
    {
        $entity = $this->getFactory()->get(Article::class)->create();
        $this->addTagsToArticles([$entity]);

        $count = Article::find($entity->article_id)->tags->count();
        $this->getJson('/articles/'.$entity->article_id.'/tags');
        $object = json_decode($this->response->getContent());

        $this->assertResponseOk();
        $this->shouldReturnJson();

        $this->assertEquals(count($object), $count);
    }

    /**
     * Current scenario is tested
     * Say we got 5 tags for article
     * foo, bar, zoo, dar, kar.
     *
     * In request we put only "foo" + 4 new tags
     * So "bar, zoo, dar, kar" are detached from article, "foo" remains and 4 new tags created
     */
    public function testPutTags()
    {
        $entity = $this->getFactory()->get(Article::class)->create();
        $this->addTagsToArticles([$entity]);

        // re-acquire for collection to have ids as key
        $entity = Article::find($entity->article_id);

        $previousTagsWillBeRemoved = $entity->tags;

        $existingTagWillStay = $this->getFactory()->get(Tag::class)
            ->setModel($previousTagsWillBeRemoved->first())
            ->transformed();

        $newTags = $this->getFactory()->get(Tag::class)
            ->count(4)
            ->transformed();

        array_push($newTags, $existingTagWillStay);

        $this->putJson('/articles/'.$entity->article_id.'/tags', $newTags);

        $this->assertResponseStatus(201);

        $updatedArticle = Article::find($entity->article_id);
        $updatedTags = $updatedArticle->tags->toArray();

        $this->assertArrayHasKey($existingTagWillStay['tagId'], $updatedTags);
        foreach ($previousTagsWillBeRemoved as $removedTag) {
            if ($removedTag->tag_id == $existingTagWillStay['tagId']) {
                continue;
            }
            $this->assertArrayNotHasKey($removedTag->tag_id, $updatedTags);
        }

        $this->assertEquals(5, count($updatedTags));
    }

    public function testGetOneWithArticles()
    {
        $articles = $this->getFactory()->get(Article::class)
            ->count(5)
            ->create();

        $this->addTagsToArticles($articles, true);

        $entity = Article::find($articles->first()->article_id);
        $this->assertEquals(4, $entity->tags->count());
        $tag = $entity->tags->first();

        $this->getJson('/tags/'.$tag->tag_id, ['with-nested' => 'articles']);
        $object = json_decode($this->response->getContent());
        $this->assertEquals(5, count($object->_articles));
    }

    public function testGetTagByIdAndName()
    {
        $tag = $this->getFactory()->get(Tag::class)->create();
        $this->getJson('/tags/'.$tag->tag);

        $object = json_decode($this->response->getContent());

        $this->assertResponseOk();
        $this->shouldReturnJson();

        $this->assertTrue(is_object($object), 'Response is an object');

        $this->assertObjectHasAttribute('_self', $object);
        $this->assertTrue(is_string($object->_self), '_self is a string');

        $this->assertObjectHasAttribute('tagId', $object);
        $this->assertObjectHasAttribute('tag', $object);

        $this->getJson('/tags/'.$tag->tag_id);
        $object2 = json_decode($this->response->getContent());

        $this->assertResponseOk();
        $this->shouldReturnJson();

        $this->assertTrue(is_object($object2), 'Response is an object');

        $this->assertObjectHasAttribute('_self', $object2);
        $this->assertTrue(is_string($object2->_self), '_self is a string');

        $this->assertObjectHasAttribute('tagId', $object2);
        $this->assertObjectHasAttribute('tag', $object2);

        $this->assertEquals($object, $object2);
    }

    public function testPostTagGlobal()
    {
        $tag = $this->getFactory()->get(Tag::class)->transformed();

        $this->post('/tags', $tag);

        $this->shouldReturnJson();

        $object = json_decode($this->response->getContent());

        $this->assertResponseStatus(201);
        $this->assertTrue(is_object($object));
        $this->assertStringStartsWith('http', $object->_self);
    }

    public function testPostTagInvalid()
    {
        $tag = $this->getFactory()->get(Tag::class)
            ->customize(['tag' => '%$@""'])
            ->transformed();

        $this->post('/tags', $tag);

        $this->shouldReturnJson();
        $this->assertResponseStatus(422);
        $object = json_decode($this->response->getContent());
        $this->assertObjectHasAttribute('invalid', $object);
        $this->assertObjectHasAttribute('tag', $object->invalid);
        $this->assertEquals('The tag may only contain letters, numbers, dashes and spaces.', $object->invalid->tag[0]->message);
    }

    public function testPatchTagGlobal()
    {
        $factory = $this->getFactory()->get(Tag::class);
        $factory->create();
        $tag = $factory
            ->customize(['tag' => 'foo'])
            ->transformed();

        $this->patchJson('/tags/'.$tag['tagId'], $tag);

        $this->shouldReturnJson();
        $this->assertResponseStatus(204);
        $checkEntity = Tag::find($tag['tagId']);
        $this->assertEquals($checkEntity->tag, $tag['tag']);
    }

    public function testPatchTagInvalid()
    {
        $factory = $this->getFactory()->get(Tag::class);
        $factory->create();
        $tag = $factory
            ->customize(['tag' => '%$@""'])
            ->transformed();

        $this->patchJson('/tags/'.$tag['tagId'], $tag);
        $this->shouldReturnJson();
        $this->assertResponseStatus(422);
        $object = json_decode($this->response->getContent());
        $this->assertObjectHasAttribute('invalid', $object);
        $this->assertObjectHasAttribute('tag', $object->invalid);
        $this->assertEquals('The tag may only contain letters, numbers, dashes and spaces.', $object->invalid->tag[0]->message);
    }

    public function testDeleteTagGlobal()
    {
        $entity = $this->getFactory()->get(Article::class)->create();
        $this->addTagsToArticles([$entity]);

        $this->assertEquals(4, Article::find($entity->article_id)->tags->count());
        $tag = $entity->tags->first();

        $this->deleteJson('/tags/'.$tag->tag_id);

        $this->assertResponseStatus(204);
        $this->assertResponseHasNoContent();
        $this->assertEquals(3, Article::find($entity->article_id)->tags->count());
    }
}
