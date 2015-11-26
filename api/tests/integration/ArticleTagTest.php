<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use App\Models\AbstractPost;
use App\Models\Article;
use App\Models\Tag;
use Faker\Factory as Faker;

/**
 * Class ArticleTagTest.
 * @group integration
 */
class ArticleTagTest extends TestCase
{
    protected $baseRoute = '/articles';
    protected $factoryClass = Article::class;

    protected $faker;
    protected $groupTagId;
    protected $categoryTagId;
    protected $topicTagId;

    public function setUp()
    {
        parent::setUp();
        $class = $this->factoryClass;
        // Workaround for model event firing.
        // The package Bosnadev\Database used for automatic UUID creation relies
        // on model events (creating) to generate the UUID.
        //
        // Laravel/Lumen currently doesn't fire repeated model events during
        // unit testing, see: https://github.com/laravel/framework/issues/1181
        $class::flushEventListeners();
        $class::boot();

        $this->faker = Faker::create('au_AU');

        $this->categoryTagId = Tag::where('tag', '=', SeedTags::categoryTagName)->firstOrFail()->tag_id;
        $this->topicTagId = Tag::where('tag', '=', SeedTags::topicTagName)->firstOrFail()->tag_id;
        $this->groupTagId = Tag::where('tag', '=', SeedTags::articleGroupTagName)->value('tag_id');
    }

    /**
     * @param $posts
     * @param bool|false $same
     */
    protected function addTagsToPosts($posts, $same = false)
    {
        $tags = factory(Tag::class, 30)->create();
        $groupedTagPivots = $this->getGroupTagPivots($tags);

        $postTagPivots = null;
        if ($same) {
            $postTagPivots = $groupedTagPivots->random(5)->toArray();
        }
        /** @var AbstractPost[] $posts */
        foreach ($posts as $post) {
            if (! $same) {
                $postTagPivots = $groupedTagPivots->random(5)->toArray();
            }

            $post->tags()->sync($postTagPivots);
        }
    }

    protected function cleanupDiscussions(array $posts)
    {
        foreach ($posts as $post) {
            // Deleting the post will trigger the deleted event that removes
            // the discussion
            $post->delete();
        }
    }

    public function testGetTags()
    {
        $entity = $this->getFactory($this->factoryClass)->create();
        $this->addTagsToPosts([$entity]);
        $class = $this->factoryClass;
        $count = $class::find($entity->post_id)->tags->count();
        $this->getJson($this->baseRoute.'/'.$entity->post_id.'/tags');
        $object = json_decode($this->response->getContent());

        $this->assertResponseOk();
        $this->shouldReturnJson();

        $this->assertEquals(count($object), $count);
    }

    /**
     * Current scenario is tested
     * Say we got 5 tags for post
     * foo, bar, zoo, dar, kar.
     *
     * In request we put only "foo" + 4 new tags
     * So "bar, zoo, dar, kar" are detached from post, "foo" remains and 4 new tags created
     */
    public function testPutTags()
    {
        $entity = $this->getFactory($this->factoryClass)->create();
        $this->addTagsToPosts([$entity]);

        $class = $this->factoryClass;
        // re-acquire for collection to have ids as key
        $entity = $class::find($entity->post_id);

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
            $newTag['_pivot']['tagGroupParentId'] = $this->groupTagId;
        }

        array_push($newTags, $existingTagWillStay);

        $this->withAuthorization()->putJson($this->baseRoute.'/'.$entity->post_id.'/tags', $newTags);

        $this->assertResponseStatus(201);
        $class = $this->factoryClass;
        $updatedPost = $class::find($entity->post_id);
        $updatedTags = $updatedPost->tags->toArray();

        $existingTagId = $existingTagWillStay['tagId'];
        $this->assertCount(1, array_filter($updatedTags, function ($piece) use ($existingTagId) {
            return $piece['tag_id'] == $existingTagId;
        }));

        foreach ($previousTagsWillBeRemoved as $removedTag) {
            if ($removedTag->tag_id == $existingTagWillStay['tagId']) {
                continue;
            }

            $removedTagId = $removedTag->tag_id;
            $this->assertCount(0, array_filter($updatedTags, function ($piece) use ($removedTagId) {
                return $piece['tag_id'] == $removedTagId;
            }));
        }

        $this->assertEquals(5, count($updatedTags));
    }

    public function testPutTagsToDifferentEntities()
    {
        $class = $this->factoryClass;
        $entity = $this->getFactory($class)->create();
        $entity2 = $this->getFactory($class)->create();

        $tags = $this->getFactory(Tag::class)
            ->count(4)
            ->transformed();

        // Add the tag category
        foreach ($tags as &$newTag) {
            $newTag['_pivot']['tagGroupId'] = $this->faker->randomElement([$this->categoryTagId, $this->topicTagId]);
            $newTag['_pivot']['tagGroupParentId'] = $this->groupTagId;
        }

        $this->withAuthorization()->putJson($this->baseRoute.'/'.$entity->post_id.'/tags', $tags);

        $this->assertResponseStatus(201);

        $this->withAuthorization()->putJson($this->baseRoute.'/'.$entity2->post_id.'/tags', $tags);

        $this->assertResponseStatus(201);
    }

    public function testGetOneWithPosts()
    {
        $class = $this->factoryClass;
        $posts = $this->getFactory($class)
            ->count(5)
            ->create();

        $this->addTagsToPosts($posts, true);

        $entity = $class::find($posts->first()->post_id);
        $this->assertEquals(5, $entity->tags->count());
        $tag = $entity->tags->first();

        $nested = strtolower(class_basename($class).'s');
        $this->getJson('/tags/'.$tag->tag_id, ['with-nested' => $nested]);
        $object = json_decode($this->response->getContent());
        $this->assertEquals(5, count($object->{'_'.$nested}));
    }

    public function testDeleteTagGlobal()
    {
        $class = $this->factoryClass;
        $entity = $this->getFactory($class)->create();
        $this->addTagsToPosts([$entity]);

        $this->assertEquals(5, $class::find($entity->post_id)->tags->count());
        $tag = $class::find($entity->post_id)->tags->first();

        $this->withAuthorization()->deleteJson('/tags/'.$tag->tag_id);

        $this->assertResponseStatus(204);
        $this->assertResponseHasNoContent();
        $this->assertEquals(4, $class::find($entity->post_id)->tags->count());
    }

    public function testShouldLogPutTags()
    {
        $class = $this->factoryClass;
        $post = factory($class)->create();

        $tags = $this->getFactory(Tag::class)
            ->count(4)
            ->customize([
                '_pivot' => [
                    'tag_group_id' => $this->categoryTagId,
                    'tag_group_parent_id' => $this->groupTagId,
                ],
            ])
            ->transformed();

        $this->withAuthorization()->putJson($this->baseRoute.'/'.$post->post_id.'/tags', $tags);
        $this->assertResponseStatus(201);

        $post = $class::find($post->post_id);

        //as far as tag touch post i.e. update post timestamps, there can be 2 records
        //sp we need more complex logics here than
        //$this->assertCount(1, $post->revisionHistory->toArray());

        $revisions = $post->revisionHistory->toArray();
        $tagRevision = false;
        foreach ($revisions as $revision) {
            if ($revision['key'] === 'tags') {
                $tagRevision = true;
            }
        }

        $this->assertTrue($tagRevision, 'post revisions has tag entry(ies)');

        $this->cleanupDiscussions([$post]);
    }

    protected function getGroupTagPivots($tags)
    {
        return Tag::getGroupedTagPivots($tags, SeedTags::articleGroupTagName);
    }
}
