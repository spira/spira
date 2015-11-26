<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use App\Http\Transformers\PostTransformer;
use App\Models\AbstractPost;
use App\Models\Article;
use App\Models\Meta;
use App\Models\Image;
use App\Models\PostPermalink;
use App\Models\Tag;
use App\Services\Api\Vanilla\Client as VanillaClient;
use Rhumsaa\Uuid\Uuid;

/**
 * Class ArticleTest.
 * @group integration
 */
class ArticleTest extends TestCase
{
    protected $baseRoute = '/articles';

    protected $factoryClass = Article::class;

    public function setUp()
    {
        parent::setUp();

        // Workaround for model event firing.
        // The package Bosnadev\Database used for automatic UUID creation relies
        // on model events (creating) to generate the UUID.
        //
        // Laravel/Lumen currently doesn't fire repeated model events during
        // unit testing, see: https://github.com/laravel/framework/issues/1181
        $class = $this->factoryClass;
        $class::flushEventListeners();
        $class::boot();
    }

    /**
     * @param AbstractPost[] $posts
     */
    protected function addPermalinksToPosts($posts)
    {
        foreach ($posts as $post) {
            $this->getFactory(PostPermalink::class)
                ->count(rand(2, 10))->make()->each(function (PostPermalink $permalink) use ($post) {
                    $post->permalinks()->save($permalink);
                });
        }
    }

    /**
     * @param AbstractPost[] $posts
     */
    protected function addMetasToPosts($posts)
    {
        foreach ($posts as $post) {
            $uniqueMetas = [];
            $this->getFactory(Meta::class)
                ->count(4)
                ->make()
                ->each(function (Meta $meta) use ($post, &$uniqueMetas) {
                    if (! in_array($meta->meta_name, $uniqueMetas)) {
                        $post->metas()->save($meta);
                        array_push($uniqueMetas, $meta->meta_name);
                    }
                });
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

    public function testGetAllPaginated()
    {
        $entities = $this->getFactory($this->factoryClass)->count(5)->create();

        $entity = $entities->first();
        $entity->excerpt = null;
        $this->addPermalinksToPosts($entities);

        $this->getJson($this->baseRoute, ['Range' => 'entities=0-19']);
        $this->assertResponseStatus(206);
        $this->shouldReturnJson();
        $this->assertJsonArray();
        $this->assertJsonMultipleEntries();
        $object = json_decode($this->response->getContent());
        $this->assertNotNull($object[0]->excerpt);
        $this->assertObjectNotHasAttribute('content', $object[0]);

        $this->cleanupDiscussions($entities->all());
    }

    public function testGetOne()
    {
        $entity = $this->getFactory($this->factoryClass)->create();
        $this->addPermalinksToPosts([$entity]);

        $this->getJson($this->baseRoute.'/'.$entity->post_id);

        $this->assertResponseOk();
        $this->shouldReturnJson();

        $object = json_decode($this->response->getContent());

        $this->assertTrue(is_object($object), 'Response is an object');

        $this->assertObjectHasAttribute('_self', $object);
        $this->assertTrue(is_string($object->_self), '_self is a string');

        $this->assertObjectHasAttribute('postId', $object);
        $this->assertObjectHasAttribute('authorId', $object);
        $this->assertTrue(Uuid::isValid($object->postId));
        $this->assertTrue(Uuid::isValid($object->authorId));

        $this->assertTrue(is_string($object->title));
        $this->assertTrue(is_string($object->permalink) || is_null($object->permalink));

        $this->cleanupDiscussions([$entity]);
    }

    public function testGetOneWithNestedTags()
    {
        $post = $this->getFactory($this->factoryClass)->create();

        $tags = factory(Tag::class, 5)->create();
        $groupedTagPivots = $this->getGroupTagPivots($tags)->toArray();

        $post->tags()->sync($groupedTagPivots);

        $this->getJson($this->baseRoute.'/'.$post->post_id, ['with-nested' => 'tags']);
        $this->assertResponseOk();
        $this->shouldReturnJson();

        $object = json_decode($this->response->getContent());
        $this->assertObjectHasAttribute('_tags', $object);
        $this->assertEquals(5, count($object->_tags));
    }

    public function testGetOneWithNestedAuthor()
    {
        $entity = $this->getFactory($this->factoryClass)->create();

        $this->getJson($this->baseRoute.'/'.$entity->post_id, ['with-nested' => 'author']);
        $this->assertResponseOk();
        $this->shouldReturnJson();

        $object = json_decode($this->response->getContent());
        $this->assertObjectHasAttribute('_author', $object);
    }

    public function testGetOneWithNestedThumbnail()
    {
        $image = $this->getFactory(Image::class)->create();
        $entity = $this->getFactory($this->factoryClass)->create([
            'thumbnail_image_id' => $image->getKey(),
        ]);

        $this->getJson($this->baseRoute.'/'.$entity->post_id, ['with-nested' => 'thumbnailImage']);
        $this->assertResponseOk();
        $this->shouldReturnJson();

        $object = json_decode($this->response->getContent());
        $this->assertObjectHasAttribute('_thumbnailImage', $object);
        $this->assertEquals($image->getKey(), $object->_thumbnailImage->imageId);
    }

    public function testGetOneByFirstPermalink()
    {
        $entity = $this->getFactory($this->factoryClass)->create();
        $this->addPermalinksToPosts([$entity]);

        $permalink = $entity->permalinks->first();
        $this->getJson($this->baseRoute.'/'.$permalink->permalink);

        $this->assertResponseOk();
        $this->shouldReturnJson();

        $object = json_decode($this->response->getContent());

        $this->assertTrue(is_object($object), 'Response is an object');

        $this->assertObjectHasAttribute('_self', $object);
        $this->assertTrue(is_string($object->_self), '_self is a string');

        $this->assertObjectHasAttribute('postId', $object);
        $this->assertTrue(Uuid::isValid($object->postId));

        $this->assertTrue(is_string($object->title));
        $this->assertTrue(is_string($object->permalink) || is_null($object->permalink));

        $this->cleanupDiscussions([$entity]);
    }

    public function testGetOneByLastPermalink()
    {
        $entity = $this->getFactory($this->factoryClass)->create();
        $this->addPermalinksToPosts([$entity]);

        $permalink = $entity->permalinks->last();
        $this->getJson($this->baseRoute.'/'.$permalink->permalink);

        $this->assertResponseOk();
        $this->shouldReturnJson();

        $object = json_decode($this->response->getContent());

        $this->assertTrue(is_object($object), 'Response is an object');

        $this->assertObjectHasAttribute('_self', $object);
        $this->assertTrue(is_string($object->_self), '_self is a string');

        $this->assertObjectHasAttribute('postId', $object);
        $this->assertTrue(Uuid::isValid($object->postId));

        $this->assertTrue(is_string($object->title));
        $this->assertTrue(is_string($object->permalink) || is_null($object->permalink));

        $this->cleanupDiscussions([$entity]);
    }

    public function testPostOne()
    {
        /** @var AbstractPost $entity */
        $entity = $this->getFactory($this->factoryClass)
            ->setTransformer(PostTransformer::class)
            ->transformed();

        $this->withAuthorization()->postJson($this->baseRoute, $entity);

        $this->shouldReturnJson();

        $object = json_decode($this->response->getContent());

        $this->assertResponseStatus(201);
        $this->assertTrue(is_object($object));
        $this->assertStringStartsWith('http', $object->_self);

        $class = $this->factoryClass;
        $this->cleanupDiscussions([$class::find($entity['postId'])]);
    }

    public function testPutOneNew()
    {
        $entity = $this->getFactory($this->factoryClass)
            ->setTransformer(PostTransformer::class)
            ->transformed();
        $class = $this->factoryClass;
        $rowCount = $class::count();

        $this->withAuthorization()->putJson($this->baseRoute.'/'.$entity['postId'], $entity);
        $this->shouldReturnJson();
        $object = json_decode($this->response->getContent());

        $this->assertResponseStatus(201);

        $this->assertEquals($rowCount + 1, $class::count());
        $this->assertTrue(is_object($object));
        $this->assertStringStartsWith('http', $object->_self);

        $this->cleanupDiscussions([$class::find($entity['postId'])]);
    }

    public function testPutOneNonExistingAuthor()
    {
        $entity = $this->getFactory($this->factoryClass)
            ->customize(['author_id' => (string) Uuid::uuid4()])
            ->transformed();

        $this->withAuthorization()->putJson($this->baseRoute.'/'.$entity['postId'], $entity);
        $this->shouldReturnJson();
        $object = json_decode($this->response->getContent());

        $this->assertResponseStatus(422);
        $this->assertObjectHasAttribute('authorId', $object->invalid);

        $this->assertEquals('The selected author id is invalid.', $object->invalid->authorId[0]->message);
        $this->assertEquals('Exists', $object->invalid->authorId[0]->type);
    }

    public function testPutMissingIdInBody()
    {
        $factory = $this->getFactory($this->factoryClass);
        $entity = $factory->create();
        $data = $factory->setTransformer(PostTransformer::class)
            ->hide(['permalink','post_id'])
            ->customize(['title' => 'foo'])
            ->transformed();

        $this->withAuthorization()->putJson($this->baseRoute.'/'.$entity->post_id, $data);
        $this->shouldReturnJson();

        $this->assertResponseStatus(400);
    }

    public function testPatchOne()
    {
        $entity = $this->getFactory($this->factoryClass)->create();

        $this->withAuthorization()->patchJson($this->baseRoute.'/'.$entity->post_id, ['title' => 'foo']);
        $this->shouldReturnJson();
        $this->assertResponseStatus(204);
        $class = $this->factoryClass;
        $checkEntity = $class::find($entity->post_id);
        $this->assertEquals($checkEntity->title, 'foo');

        $this->cleanupDiscussions([$entity]);
    }

    public function testPatchOneNewPermalink()
    {
        $factory = $this->getFactory($this->factoryClass);
        $entity = $factory->create();
        $data = $factory->setTransformer(PostTransformer::class)
            ->hide(['post_id'])
            ->customize(['permalink' => 'foo_bar'])
            ->transformed();
        $this->addPermalinksToPosts([$entity]);

        $linksCount = $entity->permalinks->count();

        $this->withAuthorization()->patchJson($this->baseRoute.'/'.$entity->post_id, $data);
        $this->shouldReturnJson();
        $this->assertResponseStatus(204);
        $class = $this->factoryClass;
        $checkEntity = $class::find($entity->post_id);
        $this->assertEquals($checkEntity->permalink, $entity->permalink);
        $this->assertEquals($checkEntity->permalinks->count(), $linksCount + 1);

        $this->cleanupDiscussions([$entity]);
    }

    public function testPatchOneExistingPermalinkSameEntity()
    {
        $factory = $this->getFactory($this->factoryClass);
        $entity = $factory->create();
        $this->addPermalinksToPosts([$entity]);

        $data = $factory->setTransformer(PostTransformer::class)
            ->setModel($entity)
            ->showOnly(['permalink'])
            ->transformed();

        $linksCount = $entity->permalinks->count();

        $this->withAuthorization()->patchJson($this->baseRoute.'/'.$entity->post_id, $data);
        $this->shouldReturnJson();
        $this->assertResponseStatus(204);
        $class = $this->factoryClass;
        $checkEntity = $class::find($entity->post_id);
        $this->assertEquals($checkEntity->permalink, $entity->permalink);
        $this->assertEquals($checkEntity->permalinks->count(), $linksCount);

        $this->cleanupDiscussions([$entity]);
    }

    public function testPatchOneExistingPermalinkDifferentEntity()
    {
        $factory = $this->getFactory($this->factoryClass);

        $existingPermalink = 'existing-permalink';

        $this->getFactory($this->factoryClass)->create(['permalink' => $existingPermalink]);

        /** @var AbstractPost $post */
        $post = $factory->create(['permalink' => 'original']);

        $data = $factory->setTransformer(PostTransformer::class)
            ->setModel($post)
            ->customize(['permalink' => $existingPermalink])
            ->showOnly(['permalink'])
            ->transformed();

        $this->withAuthorization()->patchJson($this->baseRoute.'/'.$post->post_id, $data);
        $this->shouldReturnJson();

        $this->assertException('There was an issue with the validation of provided entity', 422, 'ValidationException');

        $this->cleanupDiscussions([$post]);
    }

    public function testPatchOneRemovePermalink()
    {
        $factory = $this->getFactory($this->factoryClass);
        $entity = $factory->create();
        $this->addPermalinksToPosts([$entity]);

        $linksCount = $entity->permalinks->count();

        $data = $factory->setTransformer(PostTransformer::class)
            ->customize(['permalink' => ''])
            ->transformed();

        $this->withAuthorization()->patchJson($this->baseRoute.'/'.$entity->post_id, $data);
        $this->shouldReturnJson();
        $this->assertResponseStatus(204);
        $class = $this->factoryClass;
        $checkEntity = $class::find($entity->post_id);
        $this->assertNull($checkEntity->permalink);
        $this->assertEquals($checkEntity->permalinks->count(), $linksCount);

        $this->cleanupDiscussions([$entity]);
    }

    public function testDeleteOne()
    {
        $entities = $this->getFactory($this->factoryClass)->count(5)->create()->all();
        $this->addPermalinksToPosts($entities);

        $entity = array_shift($entities);

        $entityPermalinksCount = $entity->permalinks->count();
        $this->assertEquals($entityPermalinksCount, PostPermalink::where('post_id', '=', $entity->post_id)->count());
        $class = $this->factoryClass;
        $rowCount = $class::count();

        $permalinksTotalCount = PostPermalink::all()->count();
        $this->withAuthorization()->deleteJson($this->baseRoute.'/'.$entity->post_id);
        $permalinksTotalCountAfterDelete = PostPermalink::all()->count();

        $this->assertResponseStatus(204);
        $this->assertResponseHasNoContent();
        $this->assertEquals($rowCount - 1, $class::count());
        $this->assertEquals($permalinksTotalCount - $entityPermalinksCount, $permalinksTotalCountAfterDelete);

        $this->cleanupDiscussions($entities);
    }

    public function testGetPermalinks()
    {
        $entity = $this->getFactory($this->factoryClass)->create();
        $this->addPermalinksToPosts([$entity]);

        $count = PostPermalink::where('post_id', '=', $entity->post_id)->count();

        $this->getJson($this->baseRoute.'/'.$entity->post_id.'/permalinks');

        $this->assertResponseOk();
        $this->shouldReturnJson();

        $object = json_decode($this->response->getContent());

        $this->assertEquals(count($object), $count);

        $this->cleanupDiscussions([$entity]);
    }

    public function testGetPermalinksNotFoundPost()
    {
        $this->getJson($this->baseRoute.'/foo_bar/permalinks');
        $this->shouldReturnJson();
        $this->assertResponseStatus(422);
    }

    public function testGetMetas()
    {
        $entity = $this->getFactory($this->factoryClass)->create();
        $this->addMetasToPosts([$entity]);

        $count = Meta::where('metaable_id', '=', $entity->post_id)->count();

        $this->getJson($this->baseRoute.'/'.$entity->post_id.'/meta');
        $class = $this->factoryClass;
        $postCheck = $class::find($entity->post_id);
        $metaCheck = $postCheck->metas->first();
        $this->assertEquals($entity->post_id, $metaCheck->metaable_id);

        $this->assertResponseOk();
        $this->shouldReturnJson();

        $object = json_decode($this->response->getContent());

        $this->assertEquals(count($object), $count);

        $this->cleanupDiscussions([$entity]);
    }

    public function testAddMetas()
    {
        $post = $this->getFactory($this->factoryClass)->create();
        $this->addMetasToPosts([$post]);

        $metaCount = Meta::where('metaable_id', '=', $post->post_id)->count();

        $entities = array_map(function (Meta $entity) {
            return $this->getFactory(Meta::class)->setModel($entity)->customize(['meta_content' => 'foobar'])->transformed();
        }, $post->metas->all());

        $entities[] = $this->getFactory(Meta::class)->customize(
            [
                'meta_name' => 'barfoobar',
                'meta_content' => 'barfoobarfoo',
            ]
        )->transformed();

        $this->withAuthorization()->postJson($this->baseRoute.'/'.$post->post_id.'/meta', $entities);

        $this->assertResponseStatus(201);
        $class = $this->factoryClass;
        $updatedPost = $class::find($post->post_id);

        $this->assertEquals($metaCount + 1, $updatedPost->metas->count());
        $counter = 0;
        foreach ($updatedPost->metas as $meta) {
            if ($meta->meta_content == 'foobar') {
                $counter++;
            }
        }
        $this->assertEquals($metaCount, $counter);

        $this->cleanupDiscussions([$post]);
    }

    public function testAddDuplicateMetaNames()
    {
        /** @var AbstractPost $post */
        $post = $this->getFactory($this->factoryClass)->create();
        $factory = $this->getFactory(Meta::class)->customize(
            [
                'meta_name' => 'foo',
                'meta_content' => 'bar',
            ]
        );
        $meta = $factory->make();
        $post->metas()->save($meta);
        $data = $factory->customize(
            [
                'meta_name' => 'foo',
                'meta_content' => 'foobar',
            ]
        )->transformed();

        $this->withAuthorization()->postJson($this->baseRoute.'/'.$post->post_id.'/meta', $data);

        $this->assertResponseStatus(500);
    }

    public function deleteMeta()
    {
        $post = $this->getFactory($this->factoryClass)->create();
        $this->addMetasToPosts([$post]);

        $metaEntity = $post->metas->first();
        $metaCount = Meta::where('metaable_id', '=', $post->post_id)->count();
        $this->withAuthorization()->deleteJson($this->baseRoute.'/'.$post->post_id.'/meta/'.$metaEntity->name);
        $class = $this->factoryClass;
        $updatedPost = $class::find($post->post_id);
        $this->assertEquals($metaCount - 1, $updatedPost->metas->count());

        $this->cleanupDiscussions([$post]);
    }

    public function testShouldCreateDiscussionWhenPostCreated()
    {
        $post = $this->getFactory($this->factoryClass)->create();

        // Get the discussion
        $client = App::make(VanillaClient::class);
        $discussion = $client->api('discussions')->findByForeignId($post->post_id);

        $this->assertEquals($post->title, $discussion['Discussion']['Name']);
        $this->assertEquals($post->post_id, $discussion['Discussion']['ForeignID']);

        // Clean up by removing the discussion created
        $client->api('discussions')->remove($discussion['Discussion']['DiscussionID']);
    }

    /**
     * @expectedException Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function testShouldDeleteDiscussionWhenPostDeleted()
    {
        $client = App::make(VanillaClient::class);

        $post = $this->getFactory($this->factoryClass)->create();
        $discussion = $client->api('discussions')->findByForeignId($post->post_id);
        $post->delete();

        $this->assertEquals($post->post_id, $discussion['Discussion']['ForeignID']);
        $client->api('discussions')->findByForeignId($post->post_id);
    }

    public function testShouldGetCommentsForPost()
    {
        $post = $this->getFactory($this->factoryClass)->create();
        $body = 'A comment';

        // Get the discussion
        $client = App::make(VanillaClient::class);
        $discussion = $client->api('discussions')->findByForeignId($post->post_id);
        $discussionId = $discussion['Discussion']['DiscussionID'];

        // Add Comment
        $client->api('comments')->create($discussionId, $body);

        $this->getJson($this->baseRoute.'/'.$post->post_id.'/comments');
        $this->assertResponseStatus(200);
        $response = json_decode($this->response->getContent(), true);

        $this->assertCount(1, $response);
        $this->assertEquals($body, $response[0]['body']);

        // Clean up by removing the discussion created
        $client->api('discussions')->remove($discussion['Discussion']['DiscussionID']);
    }

    public function testShouldGetCommentsForPostUsingWithNestedHeader()
    {
        $post = $this->getFactory($this->factoryClass)->create();
        $body = 'A comment';

        // Get the discussion
        $client = App::make(VanillaClient::class);
        $discussion = $client->api('discussions')->findByForeignId($post->post_id);
        $discussionId = $discussion['Discussion']['DiscussionID'];

        // Add Comment
        $client->api('comments')->create($discussionId, $body);

        $this->getJson($this->baseRoute.'/'.$post->post_id, ['With-Nested' => 'comments']);
        $array = json_decode($this->response->getContent(), true);

        $this->assertCount(1, $array['_comments']);
        $this->assertEquals($body, $array['_comments'][0]['body']);

        // Clean up by removing the discussion created
        $client->api('discussions')->remove($discussion['Discussion']['DiscussionID']);
    }

    public function testShouldPostCommentForPost()
    {
        $body = 'A comment';
        $post = $this->getFactory($this->factoryClass)->create();

        $user = $this->createUser();
        $token = $this->tokenFromUser($user);

        $this->withAuthorization('Bearer '.$token)->postJson($this->baseRoute.'/'.$post->post_id.'/comments', ['body' => $body]);
        $array = json_decode($this->response->getContent(), true);

        $this->assertResponseStatus(200);
        $this->assertEquals($body, $array['body']);

        // Clean up Vanilla by removing the discussion and user created
        $client = App::make(VanillaClient::class);
        $client->api('discussions')->removeByForeignId($post->post_id);
        $user = $client->api('users')->sso($array['_author']['userId'], '', '');
        $client->api('users')->remove($user['User']['UserID']);
    }

    public function testShouldNotPostCommentWithoutBodyForPost()
    {
        $post = $this->getFactory($this->factoryClass)->create();

        $user = $this->createUser();
        $token = $this->tokenFromUser($user);

        $this->withAuthorization('Bearer '.$token)->postJson($this->baseRoute.'/'.$post->post_id.'/comments', ['body' => '']);

        $array = json_decode($this->response->getContent(), true);

        $this->assertArrayHasKey('body', $array['invalid']);
        $this->assertResponseStatus(422);
    }

    public function testShouldNotPostCommentWithoutAuthedUserForPost()
    {
        $body = 'A comment';
        $post = factory($this->factoryClass)->create();

        $this->postJson($this->baseRoute.'/'.$post->post_id.'/comments', ['body' => $body]);

        $this->assertResponseStatus(401);
    }

    // @Todo: Relationship is now polymorphic which does not support revisionable out of the box

    public function testShouldLogPutMetas()
    {
        $this->markTestSkipped(
            'Meta now has polymorphic relationships which do not support revisionable.'
        );

        $user = $this->createUser();
        $token = $this->tokenFromUser($user);
        $post = $this->getFactory($this->factoryClass)->create();

        $meta = $this->getFactory(Meta::class)->make();
        $entities = [$meta];

        $this->withAuthorization('Bearer '.$token)->putJson($this->baseRoute.'/'.$post->post_id.'/meta', $entities);

        //as far as tag touch post i.e. update post timestamps, there can be 2 records
        //sp we need more complex logics here than
        //$this->assertCount(1, $revisions = $post->revisionHistory->toArray());
        $class = $this->factoryClass;
        $post = $class::find($post->post_id);
        $revisions = $post->revisionHistory->toArray();
        $metaRevision = false;
        foreach ($revisions as $revision) {
            if ($revision['key'] === 'metas') {
                $metaRevision = true;
            }
        }

        $this->assertTrue($metaRevision);

        $this->cleanupDiscussions([$post]);
    }

    public function testShouldLogDeleteMeta()
    {
        $this->markTestSkipped(
            'Meta now has polymorphic relationships which do not support revisionable.'
        );

        $post = $this->getFactory($this->factoryClass)->create();
        $this->addMetasToPosts([$post]);

        $metaEntity = $post->metas->first();
        $metaCount = $post->metas->count();
        $this->withAuthorization()->deleteJson($this->baseRoute.'/'.$post->post_id.'/meta/'.$metaEntity->meta_id);
        $class = $this->factoryClass;
        $post = $class::find($post->post_id);

        $this->assertCount($metaCount + 1, $post->revisionHistory->toArray());

        $this->cleanupDiscussions([$post]);
    }

    protected function getGroupTagPivots($tags)
    {
        return Tag::getGroupedTagPivots($tags, SeedTags::articleGroupTagName);
    }
}
