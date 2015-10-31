<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use App\Models\Tag;
use Spira\Model\Collection\Collection;

class TagTest extends TestCase
{
    /**
     * @param $parentTags
     * @param bool|false $same
     */
    protected function addTagsToTags($parentTags, $same = false)
    {
        $tags = null;
        if ($same) {
            /** @var Collection $tags */
            $tags = $this->getFactory(\App\Models\Tag::class)->count(4)->create();
        }
        /** @var Tag[] $parentTags */
        foreach ($parentTags as $parentTag) {
            if (! $same) {
                /** @var Collection $tags */
                $tags = $this->getFactory(\App\Models\Tag::class)->count(4)->create();
            }

            $parentTag->childTags()->sync($tags->lists('tag_id')->toArray());
        }
    }

    public function testGetTagByIdAndName()
    {
        $tag = $this->getFactory(Tag::class)->create();
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

    public function testGetTagWithNestedChildrenAndParents()
    {
        /** @var Tag $tag */
        $tag = $this->getFactory(Tag::class)->create();
        $tagChildren = $this->getFactory(Tag::class)->count(3)->create();
        $tagParents = $this->getFactory(Tag::class)->count(5)->create();

        $tag->childTags()->sync($tagChildren->lists('tag_id')->toArray());
        $tag->parentTags()->sync($tagParents->lists('tag_id')->toArray());

        $this->getJson('/tags/'.$tag->tag, ['with-nested' => 'parentTags, childTags']);

        $object = json_decode($this->response->getContent());

        $this->assertResponseOk();
        $this->shouldReturnJson();

        $this->assertObjectHasAttribute('_parentTags', $object);
        $this->assertObjectHasAttribute('_childTags', $object);

        $this->assertEquals(3, count($object->_childTags));
        $this->assertEquals(5, count($object->_parentTags));
    }

    public function testPostTag()
    {
        $tag = $this->getFactory(Tag::class)->transformed();

        $this->postJson('/tags', $tag);

        $this->shouldReturnJson();

        $object = json_decode($this->response->getContent());

        $this->assertResponseStatus(201);
        $this->assertTrue(is_object($object));
        $this->assertStringStartsWith('http', $object->_self);
    }

    public function testPostTagInvalid()
    {
        $tag = $this->getFactory(Tag::class)
            ->customize(['tag' => 'This tag is longer than 30 characters'])
            ->transformed();

        $this->postJson('/tags', $tag);

        $this->shouldReturnJson();
        $this->assertResponseStatus(422);
        $object = json_decode($this->response->getContent());
        $this->assertObjectHasAttribute('invalid', $object);
        $this->assertObjectHasAttribute('tag', $object->invalid);
        $this->assertEquals('The tag may not be greater than 30 characters.', $object->invalid->tag[0]->message);
    }

    public function testPatchTag()
    {
        $factory = $this->getFactory(Tag::class);
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
        $factory = $this->getFactory(Tag::class);
        $factory->create();
        $tag = $factory
            ->customize(['tag' => 'This tag is longer than 30 characters'])
            ->transformed();

        $this->patchJson('/tags/'.$tag['tagId'], $tag);
        $this->shouldReturnJson();
        $this->assertResponseStatus(422);
        $object = json_decode($this->response->getContent());
        $this->assertObjectHasAttribute('invalid', $object);
        $this->assertObjectHasAttribute('tag', $object->invalid);
        $this->assertEquals('The tag may not be greater than 30 characters.', $object->invalid->tag[0]->message);
    }

    /**
     * Current scenario is tested
     * Say we got 5 tags for tag
     * foo, bar, zoo, dar, kar.
     *
     * In request we put only "foo" + 4 new tags
     * So "bar, zoo, dar, kar" are detached from tag, "foo" remains and 4 new tags created
     */
    public function testPutTags()
    {
        $entity = $this->getFactory(Tag::class)->create();
        $this->addTagsToTags([$entity]);

        // re-acquire for collection to have ids as key
        $entity = Tag::find($entity->tag_id);

        $previousTagsWillBeRemoved = $entity->childTags;

        $existingTagWillStay = $this->getFactory(Tag::class)
            ->setModel($previousTagsWillBeRemoved->first())
            ->transformed();

        $newTags = $this->getFactory(Tag::class)
            ->count(4)
            ->transformed();

        array_push($newTags, $existingTagWillStay);

        $this->putJson('/tags/'.$entity->tag_id.'/child-tags', $newTags);

        $this->assertResponseStatus(201);

        $updatedTag = Tag::find($entity->tag_id);
        $updatedTags = $updatedTag->childTags->toArray();

        $this->assertArrayHasKey($existingTagWillStay['tagId'], $updatedTags);
        foreach ($previousTagsWillBeRemoved as $removedTag) {
            if ($removedTag->tag_id == $existingTagWillStay['tagId']) {
                continue;
            }
            $this->assertArrayNotHasKey($removedTag->tag_id, $updatedTags);
        }

        $this->assertEquals(5, count($updatedTags));
    }
}
