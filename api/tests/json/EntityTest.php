<?php

use Rhumsaa\Uuid\Uuid;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class EntityTest extends TestCase
{
    use DatabaseTransactions;

    protected $entity;

    public function setUp()
    {
        parent::setUp();

        // Get an entry from the DB, to have an ID to use for getOne tests
        $this->entity = App\Models\TestEntity::first();
    }

    /**
     * Get all test entities.
     */
    public function testGetAll()
    {
        $this->get('/test/entities');

        $this->assertResponseOk();
        $this->shouldReturnJson();
        $this->assertJsonArray();
        $this->assertJsonMultipleEntries();
    }

    /**
     * Get a test entity.
     */
    public function testGetOne()
    {
        $this->get('/test/entities/'.$this->entity->entity_id);

        $this->assertResponseOk();
        $this->shouldReturnJson();

        $object = json_decode($this->response->getContent());

        $this->assertTrue(is_object($object), 'Response is an object');

        $this->assertObjectHasAttribute('entityId', $object);
        $this->assertStringMatchesFormat('%x-%x-%x-%x-%x', $object->entityId);
        $this->assertTrue(strlen($object->entityId) === 36, 'UUID has 36 chars');

        $this->assertTrue(is_string($object->varchar), 'Varchar column type is text');
        $this->assertTrue(is_string($object->hash), 'Hash column is a hash');
        $this->assertTrue(is_integer($object->integer), 'Integer column type is integer');
        $this->assertTrue(is_float($object->decimal), 'Decimal column type is integer');
        $this->assertNull($object->nullable, 'Nullable column type is null');
        $this->assertTrue(is_string($object->text), 'Text column type is text');
        $this->assertValidIso8601Date($object->date, 'Date column type is a valid ISO8601 date');
        $this->assertObjectHasAttribute('multiWordColumnTitle', $object, 'Multi word colum is camel cased');

        $this->assertValidIso8601Date($object->createdAt, 'createdAt column is a valid ISO8601 date');
        $this->assertValidIso8601Date($object->updatedAt, 'updatedAt column is a valid ISO8601 date');
    }
}
