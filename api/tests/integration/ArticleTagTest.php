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
use Faker\Factory as Faker;

/**
 * Class ArticleTagTest.
 * @group integration
 */
class ArticleTagTest extends TestCase
{
    private $faker;
    private $articleGroupTagId;
    private $categoryTagId;
    private $topicTagId;

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

        $this->faker = Faker::create('au_AU');

        $this->categoryTagId = Tag::where('tag', '=', SeedTags::categoryTagName)->firstOrFail()->tag_id;
        $this->topicTagId = Tag::where('tag', '=', SeedTags::topicTagName)->firstOrFail()->tag_id;
        $this->articleGroupTagId = Tag::where('tag', '=', SeedTags::articleGroupTagName)->value('tag_id');
    }

    /**
     * @param $articles
     * @param bool|false $same
     */
    protected function addTagsToArticles($articles, $same = false)
    {
        $tags = factory(Tag::class, 30)->create();
        $groupedTagPivots = Tag::getGroupedTagPivots($tags, SeedTags::articleGroupTagName);

        $articleTagPivots = null;
        if ($same) {
            $articleTagPivots = $groupedTagPivots->random(5)->toArray();
        }
        /** @var Article[] $articles */
        foreach ($articles as $article) {
            if (! $same) {
                $articleTagPivots = $groupedTagPivots->random(5)->toArray();
            }

            $article->tags()->sync($articleTagPivots);
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

        // Add the tag category
        foreach ($newTags as &$newTag) {
            $newTag['_pivot']['tagGroupId'] = $this->faker->randomElement([$this->categoryTagId, $this->topicTagId]);
            $newTag['_pivot']['tagGroupParentId'] = $this->articleGroupTagId;
        }

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

    public function testPutTagsToDifferentEntities()
    {
        $entity = $this->getFactory(Article::class)->create();
        $entity2 = $this->getFactory(Article::class)->create();

        $tags = $this->getFactory(Tag::class)
            ->count(4)
            ->transformed();

        // Add the tag category
        foreach ($tags as &$newTag) {
            $newTag['_pivot']['tagGroupId'] = $this->faker->randomElement([$this->categoryTagId, $this->topicTagId]);
            $newTag['_pivot']['tagGroupParentId'] = $this->articleGroupTagId;
        }

        $this->putJson('/articles/'.$entity->article_id.'/tags', $tags);

        $this->assertResponseStatus(201);

        $this->putJson('/articles/'.$entity2->article_id.'/tags', $tags);

        $this->assertResponseStatus(201);
    }

    public function testGetOneWithArticles()
    {
        $articles = $this->getFactory(Article::class)
            ->count(5)
            ->create();

        $this->addTagsToArticles($articles, true);

        $entity = Article::find($articles->first()->article_id);
        $this->assertEquals(5, $entity->tags->count());
        $tag = $entity->tags->first();

        $this->getJson('/tags/'.$tag->tag_id, ['with-nested' => 'articles']);
        $object = json_decode($this->response->getContent());
        $this->assertEquals(5, count($object->_articles));
    }

    public function testDeleteTagGlobal()
    {
        $entity = $this->getFactory(Article::class)->create();
        $this->addTagsToArticles([$entity]);

        $this->assertEquals(5, Article::find($entity->article_id)->tags->count());
        $tag = Article::find($entity->article_id)->tags->first();

        $this->deleteJson('/tags/'.$tag->tag_id);

        $this->assertResponseStatus(204);
        $this->assertResponseHasNoContent();
        $this->assertEquals(4, Article::find($entity->article_id)->tags->count());
    }

    public function testShouldLogPutTags()
    {
        $article = factory(Article::class)->create();

        $tags = $this->getFactory(Tag::class)
            ->count(4)
            ->customize([
                '_pivot' => [
                    'tag_group_id' => $this->categoryTagId,
                    'tag_group_parent_id' => $this->articleGroupTagId,
                ],
            ])
            ->transformed();

        $this->putJson('/articles/'.$article->article_id.'/tags', $tags);
        $this->assertResponseStatus(201);

        $article = Article::find($article->article_id);

        //as far as tag touch article i.e. update article timestamps, there can be 2 records
        //sp we need more complex logics here than
        //$this->assertCount(1, $article->revisionHistory->toArray());

        $revisions = $article->revisionHistory->toArray();
        $tagRevision = false;
        foreach ($revisions as $revision) {
            if ($revision['key'] === 'tags') {
                $tagRevision = true;
            }
        }

        $this->assertTrue($tagRevision, 'Article revisions has tag entry(ies)');

        $this->cleanupDiscussions([$article]);
    }
}
