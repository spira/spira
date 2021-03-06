<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use App\Models\Image;
use Spira\Core\Responder\Transformers\EloquentModelTransformer;
use Rhumsaa\Uuid\Uuid;

/**
 * Class ImageTest.
 * @group integration
 */
class ImageTest extends TestCase
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
        App\Models\Image::flushEventListeners();
        App\Models\Image::boot();
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
        $transformer = $this->app->make(EloquentModelTransformer::class);
        $entity = $transformer->transform($entity);

        return $entity;
    }

    public function testSignRequest()
    {
        $this->withAuthorization()->getJson('/cloudinary/signature?foo=bar&baz=quux');

        $this->assertResponseStatus(200);
        $this->shouldReturnJson();

        $object = json_decode($this->response->getContent());

        $this->assertObjectHasAttribute('signature', $object);
        $this->assertObjectHasAttribute('apiKey', $object);
        $this->assertInternalType('string', $object->apiKey); //no need to test signature response - the service has tests for this
    }

    public function testGetAllPaginated()
    {
        $entities = factory(Image::class, 30)->create()->all();

        $this->getJson('/images', ['Range' => 'entities=0-19']);
        $this->assertResponseStatus(206);
        $this->shouldReturnJson();
        $this->assertJsonArray();
        $this->assertJsonMultipleEntries();
        $object = json_decode($this->response->getContent());
        $this->assertEquals(20, count($object));
    }

    public function testGetOne()
    {
        $entity = factory(Image::class)->create();

        $this->getJson('/images/'.$entity->image_id);

        $this->assertResponseOk();
        $this->shouldReturnJson();

        $object = json_decode($this->response->getContent());

        $this->assertTrue(is_object($object), 'Response is an object');

        $this->assertObjectHasAttribute('_self', $object);
        $this->assertTrue(is_string($object->_self), '_self is a string');

        $this->assertObjectHasAttribute('imageId', $object);
        $this->assertTrue(Uuid::isValid($object->imageId));

        $this->assertTrue(is_string($object->format));
        $this->assertTrue(is_string($object->alt));
        $this->assertTrue(is_string($object->folder));
        $this->assertTrue(is_numeric($object->version));
    }

    public function testPutOneNew()
    {
        $entity = factory(Image::class)->make();
        $id = $entity->image_id;

        $rowCount = Image::count();

        $this->withAuthorization()->putJson('/images/'.$id, $this->prepareEntity($entity));
        $this->shouldReturnJson();
        $object = json_decode($this->response->getContent());

        $this->assertResponseStatus(201);
        $this->assertEquals($rowCount + 1, Image::count());
        $this->assertTrue(is_object($object));
        $this->assertStringStartsWith('http', $object->_self);
    }

    public function testPatchOne()
    {
        $entity = factory(Image::class)->create();
        $id = $entity->image_id;
        $entity->alt = 'foo';
        $preparedEntity = $this->prepareEntity($entity);
        $this->withAuthorization()->patchJson('/images/'.$id, $preparedEntity);
        $this->assertResponseHasNoContent();
        $this->assertResponseStatus(204);
        $checkEntity = Image::find($id);
        $this->assertEquals($checkEntity->alt, $entity->alt);
    }

    public function testDeleteOne()
    {
        $entities = factory(Image::class, 4)->create()->all();

        $entity = $entities[0];
        $id = $entity->image_id;
        $rowCount = Image::count();
        $this->withAuthorization()->deleteJson('/images/'.$id);
        $this->assertResponseStatus(204);
        $this->assertResponseHasNoContent();
        $this->assertEquals($rowCount - 1, Image::count());
    }
}
