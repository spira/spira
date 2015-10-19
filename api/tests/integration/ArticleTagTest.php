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
            $tags = $this->getFactory(Tag::class)->count(4)->create();
        }
        /** @var Article[] $articles */
        foreach ($articles as $article) {
            if (! $same) {
                /** @var Collection $tags */
                $tags = $this->getFactory(Tag::class)->count(4)->create();
            }

            $article->tags()->sync($tags->lists('tag_id')->toArray());
        }
    }

    protected function cleanupDiscussions(array $articles)
    {
        foreach ($articles as $article) {
            // Deleting the article will trigger the deleted event that removes
            // the discussion
            $article->delete();
        }
    }

    public function testGetTags()
    {
        $entity = $this->getFactory(Article::class)->create();
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
        $entity = $this->getFactory(Article::class)->create();
        $this->addTagsToArticles([$entity]);

        // re-acquire for collection to have ids as key
        $entity = Article::find($entity->article_id);

        $previousTagsWillBeRemoved = $entity->tags;

        $existingTagWillStay = $this->getFactory(Tag::class)
            ->setModel($previousTagsWillBeRemoved->first())
            ->transformed();

        $newTags = $this->getFactory(Tag::class)
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
        $articles = $this->getFactory(Article::class)
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

    public function testDeleteTagGlobal()
    {
        $entity = $this->getFactory(Article::class)->create();
        $this->addTagsToArticles([$entity]);

        $this->assertEquals(4, Article::find($entity->article_id)->tags->count());
        $tag = Article::find($entity->article_id)->tags->first();

        $this->deleteJson('/tags/'.$tag->tag_id);

        $this->assertResponseStatus(204);
        $this->assertResponseHasNoContent();
        $this->assertEquals(3, Article::find($entity->article_id)->tags->count());
    }

    public function testShouldLogPutTags()
    {
        $this->markTestIncomplete(
            'This test is broken and has not been fixed yet.'
        );

        $article = factory(Article::class)->create();

        $tags = $this->getFactory(Tag::class)
            ->count(4)
            ->transformed();

        $this->putJson('/articles/'.$article->article_id.'/tags', $tags);

        $article = Article::find($article->article_id);
        $this->assertCount(1, $article->revisionHistory->toArray());

        $this->cleanupDiscussions([$article]);
    }
}
