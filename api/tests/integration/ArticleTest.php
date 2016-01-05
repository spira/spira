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
use App\Models\User;
use App\Services\Api\Vanilla\Client as VanillaClient;
use Rhumsaa\Uuid\Uuid;
use Spira\Core\Model\Model\BaseModel;

/**
 * Class ArticleTest.
 * @group integration
 */
class ArticleTest extends TestCase
{
    protected $baseRoute = '/articles';

    protected $factoryClass = Article::class;
    protected $permalinkClass = PostPermalink::class;

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
            $this->getFactory($this->permalinkClass)
                ->count(rand(2, 10))->make()->each(
                    function (BaseModel $permalink) use ($post) {
                        $post->permalinks()->save($permalink);
                    }
                );
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
        $entities = $this->makePosts();

        $entity = $entities->first();
        $entity->excerpt = null;
        $this->addPermalinksToPosts($entities);

        $this->withoutAuthorization()->getJson($this->baseRoute, ['Range' => 'entities=0-19']);
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
        $entity = $this->makePost();
        $this->addPermalinksToPosts([$entity]);

        $this->withoutAuthorization($entity)->getJson($this->baseRoute.'/'.$entity->post_id);

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
        $post = $this->makePost();

        $tags = factory(Tag::class, 5)->create();
        $groupedTagPivots = $this->getGroupTagPivots($tags)->toArray();

        $post->tags()->sync($groupedTagPivots);

        $this->withoutAuthorization($post)->getJson($this->baseRoute.'/'.$post->post_id, ['with-nested' => 'tags']);
        $this->assertResponseOk();
        $this->shouldReturnJson();

        $object = json_decode($this->response->getContent());
        $this->assertObjectHasAttribute('_tags', $object);
        $this->assertEquals(5, count($object->_tags));
    }

    public function testGetOneWithNestedAuthor()
    {
        $entity = $this->makePost();

        $this->withoutAuthorization($entity)->getJson($this->baseRoute.'/'.$entity->post_id, ['with-nested' => 'author']);
        $this->assertResponseOk();
        $this->shouldReturnJson();

        $object = json_decode($this->response->getContent());
        $this->assertObjectHasAttribute('_author', $object);
    }

    public function testGetOneWithNestedThumbnail()
    {
        $image = $this->getFactory(Image::class)->create();
        $entity = $this->makePost(['thumbnail_image_id' => $image->getKey()]);

        $this->withoutAuthorization($entity)->getJson($this->baseRoute.'/'.$entity->post_id, ['with-nested' => 'thumbnailImage']);
        $this->assertResponseOk();
        $this->shouldReturnJson();

        $object = json_decode($this->response->getContent());
        $this->assertObjectHasAttribute('_thumbnailImage', $object);
        $this->assertEquals($image->getKey(), $object->_thumbnailImage->imageId);
    }

    public function testGetOneByFirstPermalink()
    {
        $entity = $this->makePost();
        $this->addPermalinksToPosts([$entity]);

        $permalink = $entity->permalinks->first();

        $this->withoutAuthorization($entity)->getJson($this->baseRoute.'/'.$permalink->permalink);

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
        $entity = $this->makePost();
        $this->addPermalinksToPosts([$entity]);

        $permalink = $entity->permalinks->last();

        $this->withoutAuthorization($entity)->getJson($this->baseRoute.'/'.$permalink->permalink);

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
        $post = $this->makePost([], false);
        $entity = $this->getFactory($this->factoryClass)
            ->setTransformer(PostTransformer::class)
            ->setModel($post)
            ->transformed();

        $this->withAdminAuthorization()->postJson($this->baseRoute, $entity);

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
        $class = $this->factoryClass;
        $post = $this->makePost([], false);
        $entity = $this->getFactory($class)
            ->setTransformer(PostTransformer::class)
            ->setModel($post)
            ->transformed();

        $rowCount = $class::count();

        $this->withAdminAuthorization()->putJson($this->baseRoute.'/'.$entity['postId'], $entity);
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
        $post = $this->makePost(['author_id' => (string) Uuid::uuid4()], false);
        $entity = $this->getFactory($this->factoryClass)
            ->setModel($post)
            ->transformed();

        $this->withAdminAuthorization()->putJson($this->baseRoute.'/'.$entity['postId'], $entity);
        $this->shouldReturnJson();
        $object = json_decode($this->response->getContent());

        $this->assertResponseStatus(422);
        $this->assertObjectHasAttribute('authorId', $object->invalid);

        $this->assertEquals('The selected author id is invalid.', $object->invalid->authorId[0]->message);
        $this->assertEquals('Exists', $object->invalid->authorId[0]->type);
    }

    public function testPutMissingIdInBody()
    {
        $entity = $this->makePost();
        $data = $this->getFactory($this->factoryClass)
            ->setTransformer(PostTransformer::class)
            ->setModel($entity)
            ->hide(['permalink', 'post_id'])
            ->customize(['title' => 'foo'])
            ->transformed();

        $this->withAdminAuthorization()->putJson($this->baseRoute.'/'.$entity->post_id, $data);
        $this->shouldReturnJson();

        $this->assertResponseStatus(400);
    }

    public function testPatchOne()
    {
        $entity = $this->makePost();

        $this->withAdminAuthorization()->patchJson($this->baseRoute.'/'.$entity->post_id, ['title' => 'foo']);

        $this->assertResponseStatus(204);
        $class = $this->factoryClass;
        $checkEntity = $class::find($entity->post_id);
        $this->assertEquals($checkEntity->title, 'foo');

        $this->cleanupDiscussions([$entity]);
    }

    public function testPatchOneNewPermalink()
    {
        $entity = $this->makePost();
        $data = $this->getFactory($this->factoryClass)
            ->setTransformer(PostTransformer::class)
            ->setModel($entity)
            ->hide(['post_id'])
            ->customize(['permalink' => 'foo_bar'])
            ->transformed();
        $this->addPermalinksToPosts([$entity]);

        $linksCount = $entity->permalinks->count();

        $this->withAdminAuthorization()->patchJson($this->baseRoute.'/'.$entity->post_id, $data);

        $this->assertResponseStatus(204);
        $class = $this->factoryClass;
        $checkEntity = $class::find($entity->post_id);
        $this->assertEquals($checkEntity->permalink, $entity->permalink);
        $this->assertEquals($checkEntity->permalinks->count(), $linksCount + 1);

        $this->cleanupDiscussions([$entity]);
    }

    public function testPatchOneExistingPermalinkSameEntity()
    {
        $entity = $this->makePost();
        $this->addPermalinksToPosts([$entity]);

        $data = $this->getFactory($this->factoryClass)
            ->setTransformer(PostTransformer::class)
            ->setModel($entity)
            ->showOnly(['permalink'])
            ->transformed();

        $linksCount = $entity->permalinks->count();

        $this->withAdminAuthorization()->patchJson($this->baseRoute.'/'.$entity->post_id, $data);

        $this->assertResponseStatus(204);
        $class = $this->factoryClass;
        $checkEntity = $class::find($entity->post_id);
        $this->assertEquals($checkEntity->permalink, $entity->permalink);
        $this->assertEquals($checkEntity->permalinks->count(), $linksCount);

        $this->cleanupDiscussions([$entity]);
    }

    public function testPatchOneExistingPermalinkDifferentEntity()
    {
        $existingPermalink = 'existing-permalink';

        $this->makePost(['permalink' => $existingPermalink]);

        $post = $this->makePost(['permalink' => 'original']);

        $data = $this->getFactory($this->factoryClass)
            ->setTransformer(PostTransformer::class)
            ->setModel($post)
            ->customize(['permalink' => $existingPermalink])
            ->showOnly(['permalink'])
            ->transformed();

        $this->withAdminAuthorization()->patchJson($this->baseRoute.'/'.$post->post_id, $data);
        $this->shouldReturnJson();

        $this->assertException('There was an issue with the validation of provided entity', 422, 'ValidationException');

        $this->cleanupDiscussions([$post]);
    }

    public function testPatchOneRemovePermalink()
    {
        $entity = $this->makePost();
        $this->addPermalinksToPosts([$entity]);

        $linksCount = $entity->permalinks->count();

        $data = $this->getFactory($this->factoryClass)
            ->setTransformer(PostTransformer::class)
            ->setModel($entity)
            ->customize(['permalink' => ''])
            ->transformed();

        $this->withAdminAuthorization()->patchJson($this->baseRoute.'/'.$entity->post_id, $data);

        $this->assertResponseStatus(204);
        $class = $this->factoryClass;
        $checkEntity = $class::find($entity->post_id);
        $this->assertNull($checkEntity->permalink);
        $this->assertEquals($checkEntity->permalinks->count(), $linksCount);

        $this->cleanupDiscussions([$entity]);
    }

    public function testDeleteOne()
    {
        $entities = $this->makePosts()->all();
        $this->addPermalinksToPosts($entities);

        $entity = array_shift($entities);

        $entityPermalinksCount = $entity->permalinks->count();
        $this->assertEquals($entityPermalinksCount, $this->countPermalinks($entity->post_id));
        $class = $this->factoryClass;
        $rowCount = $class::count();

        $permalinksTotalCount = $this->countPermalinks();

        $this->withAdminAuthorization()->deleteJson($this->baseRoute.'/'.$entity->post_id);
        $permalinksTotalCountAfterDelete = $this->countPermalinks();

        $this->assertResponseStatus(204);
        $this->assertResponseHasNoContent();
        $this->assertEquals($rowCount - 1, $class::count());
        $this->assertEquals($permalinksTotalCount - $entityPermalinksCount, $permalinksTotalCountAfterDelete);

        $this->cleanupDiscussions($entities);
    }

    public function testGetPermalinks()
    {
        $entity = $this->makePost();
        $this->addPermalinksToPosts([$entity]);

        $count = $this->countPermalinks($entity->post_id);

        $this->withoutAuthorization($entity)->getJson($this->baseRoute.'/'.$entity->post_id.'/permalinks');

        $this->assertResponseOk();
        $this->shouldReturnJson();

        $object = json_decode($this->response->getContent());

        $this->assertEquals(count($object), $count);

        $this->cleanupDiscussions([$entity]);
    }

    public function testGetPermalinksNotFoundPost()
    {
        $this->withAdminAuthorization()->getJson($this->baseRoute.'/foo_bar/permalinks');
        $this->shouldReturnJson();
        $this->assertResponseStatus(422);
    }

    public function testGetMetas()
    {
        $entity = $this->makePost();
        $this->addMetasToPosts([$entity]);

        $count = Meta::where('metaable_id', '=', $entity->post_id)->count();

        $this->withoutAuthorization($entity)->getJson($this->baseRoute.'/'.$entity->post_id.'/meta');
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
        $post = $this->makePost();
        $this->addMetasToPosts([$post]);

        $metaCount = Meta::where('metaable_id', '=', $post->post_id)->count();

        $entities = array_map(function (Meta $entity) {
            return $this->getFactory(Meta::class)->setModel($entity)->customize(['meta_content' => 'foobar'])->transformed();
        }, $post->metas->all());

        $entities[] = $this->getFactory(Meta::class)->customize(
            [
                'meta_name' => 'barfoobar',
                'meta_content' => 'barfoobarfoo',
                'metaable_id' => $post->post_id,
            ]
        )->transformed();

        $this->withAdminAuthorization()->putJson($this->baseRoute.'/'.$post->post_id.'/meta', $entities);

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
        $post = $this->makePost();

        $meta = $this->getFactory(Meta::class)->customize(
            [
                'meta_name' => 'foo',
                'meta_content' => 'bar',
            ]
        )->make();
        $post->metas()->save($meta);

        $data = $this->getFactory(Meta::class)->customize(
            [
                'meta_name' => 'foo',
                'meta_content' => 'foobar',
                'metaable_id' => $post->post_id,
            ]
        )->transformed();

        $this->withAdminAuthorization()->putJson($this->baseRoute.'/'.$post->post_id.'/meta', [$data]);
        $this->assertResponseStatus(422);
    }

    public function deleteMeta()
    {
        $post = $this->makePost();
        $this->addMetasToPosts([$post]);

        $metaEntity = $post->metas->first();
        $metaCount = Meta::where('metaable_id', '=', $post->post_id)->count();

        $this->withAdminAuthorization()->deleteJson($this->baseRoute.'/'.$post->post_id.'/meta/'.$metaEntity->name);
        $class = $this->factoryClass;
        $updatedPost = $class::find($post->post_id);
        $this->assertEquals($metaCount - 1, $updatedPost->metas->count());

        $this->cleanupDiscussions([$post]);
    }

    public function testShouldCreateDiscussionWhenPostCreated()
    {
        $post = $this->makePost();

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

        $post = $this->makePost();
        $discussion = $client->api('discussions')->findByForeignId($post->post_id);
        $post->delete();

        $this->assertEquals($post->post_id, $discussion['Discussion']['ForeignID']);
        $client->api('discussions')->findByForeignId($post->post_id);
    }

    public function testShouldGetCommentsForPost()
    {
        $post = $this->makePost();
        $body = 'A comment';

        // Get the discussion
        $client = App::make(VanillaClient::class);
        $discussion = $client->api('discussions')->findByForeignId($post->post_id);
        $discussionId = $discussion['Discussion']['DiscussionID'];

        // Add Comment
        $client->api('comments')->create($discussionId, $body);

        $this->withoutAuthorization($post)->getJson($this->baseRoute.'/'.$post->post_id.'/comments');
        $this->assertResponseStatus(200);
        $response = json_decode($this->response->getContent(), true);

        $this->assertCount(1, $response);
        $this->assertEquals($body, $response[0]['body']);

        // Clean up by removing the discussion created
        $client->api('discussions')->remove($discussion['Discussion']['DiscussionID']);
    }

    public function testShouldGetCommentsForPostUsingWithNestedHeader()
    {
        $post = $this->makePost();
        $body = 'A comment';

        // Get the discussion
        $client = App::make(VanillaClient::class);
        $discussion = $client->api('discussions')->findByForeignId($post->post_id);
        $discussionId = $discussion['Discussion']['DiscussionID'];

        // Add Comment
        $client->api('comments')->create($discussionId, $body);

        $this->withoutAuthorization($post)->getJson($this->baseRoute.'/'.$post->post_id, ['With-Nested' => 'comments']);
        $array = json_decode($this->response->getContent(), true);

        $this->assertCount(1, $array['_comments']);
        $this->assertEquals($body, $array['_comments'][0]['body']);

        // Clean up by removing the discussion created
        $client->api('discussions')->remove($discussion['Discussion']['DiscussionID']);
    }

    public function testShouldPostCommentForPost()
    {
        $body = 'A comment';
        $post = $this->makePost();

        $user = $this->createUser();
        $token = $this->tokenFromUser($user);

        $this->withAuthorization('Bearer '.$token, $post, $user)->postJson($this->baseRoute.'/'.$post->post_id.'/comments', ['body' => $body]);
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
        $post = $this->makePost();

        $user = $this->createUser();
        $token = $this->tokenFromUser($user);

        $this->withAuthorization('Bearer '.$token, $post, $user)->postJson($this->baseRoute.'/'.$post->post_id.'/comments', ['body' => '']);

        $array = json_decode($this->response->getContent(), true);

        $this->assertArrayHasKey('body', $array['invalid']);
        $this->assertResponseStatus(422);
    }

    public function testShouldNotPostCommentWithoutAuthedUserForPost()
    {
        $body = 'A comment';
        $post = $this->makePost();

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
        $post = $this->makePost();

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

        $post = $this->makePost();
        $this->addMetasToPosts([$post]);

        $metaEntity = $post->metas->first();
        $metaCount = $post->metas->count();
        $this->withAuthorization()->deleteJson($this->baseRoute.'/'.$post->post_id.'/meta/'.$metaEntity->meta_id);
        $class = $this->factoryClass;
        $post = $class::find($post->post_id);

        $this->assertCount($metaCount + 1, $post->revisionHistory->toArray());

        $this->cleanupDiscussions([$post]);
    }

    /** @return AbstractPost */
    protected function makePost($attr = [], $saved = true)
    {
        $factory = $this->getFactory($this->factoryClass);

        return $saved
            ? $factory->create($attr)
            : $factory->make($attr);
    }

    /** @return \Spira\Core\Model\Collection\Collection */
    protected function makePosts($num = 5)
    {
        return $this->getFactory($this->factoryClass)->count($num)->create();
    }

    protected function countPermalinks($post_id = null)
    {
        return $post_id
            ? call_user_func_array($this->permalinkClass.'::where', ['post_id', '=', $post_id])->count()
            : call_user_func_array($this->permalinkClass.'::all', [])->count();
    }

    protected function getGroupTagPivots($tags)
    {
        return Tag::getGroupedTagPivots($tags, SeedTags::articleGroupTagName);
    }

    public function withAuthorization($header = null, $post = null, $user = null)
    {
        return parent::withAuthorization($header);
    }

    public function withAdminAuthorization($post = null, $user = null)
    {
        return parent::withAdminAuthorization();
    }

    public function withoutAuthorization($post = null, $user = null)
    {
        return parent::withoutAuthorization();
    }
}
