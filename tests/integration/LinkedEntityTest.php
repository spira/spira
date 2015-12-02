<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */
namespace Spira\Core\tests\integration;

use App\Models\TestEntity;
use App\Models\SecondTestEntity;
use Spira\Core\tests\TestCase;

/**
 * Class LinkedEntityTest.
 * @group integration
 */
class LinkedEntityTest extends TestCase
{
//    public function testGetAll()
//    {
//        $entity = $this->makeEntity();
//        $ids = $entity->secondTestEntities()->get()->pluck('entity_id')->toArray();
//
//        $this->getJson('test/many/'.$entity->entity_id.'/children');
//
//        $response = $this->getJsonResponseAsArray();
//
//        $this->assertEmpty(array_diff(array_pluck($response, 'entityId'), $ids));
//    }
//
//    public function testAttachOne()
//    {
//        /** @var $entity TestEntity */
//        $entity = $this->getFactory(TestEntity::class)->create();
//        $factory = $this->getFactory(SecondTestEntity::class);
//        $second = $factory->create();
//
//        $this->putJson('test/many/'.$entity->entity_id.'/children/'.$second->entity_id, $factory->transformed());
//
//        $this->assertResponseStatus(201);
//        $this->assertResponseHasNoContent();
//
//        $this->assertEquals($entity->secondTestEntities()->first()->entity_id, $second->entity_id);
//    }
//
//    public function testAttachMany()
//    {
//        $entity = $this->makeEntity();
//        $factory = $this->getFactory(SecondTestEntity::class);
//        $newEntities = $factory->count(3)->create();
//
//        $ids = array_merge(
//            $entity->secondTestEntities()->get()->pluck('entity_id')->toArray(),
//            $newEntities->pluck('entity_id')->toArray()
//        );
//
//        $this->postJson('test/many/'.$entity->entity_id.'/children', $factory->transformed());
//
//        $this->assertResponseStatus(201);
//        $this->assertEmpty(array_diff($entity->secondTestEntities()->get()->pluck('entity_id')->toArray(), $ids));
//    }
//
//    public function testSyncMany()
//    {
//        $entity = $this->makeEntity();
//        $factory = $this->getFactory(SecondTestEntity::class);
//        $factory->count(3)->create();
//
//        $transformed = $factory->transformed();
//        $transformed[] = $this->getFactory(SecondTestEntity::class)
//            ->setModel($entity->secondTestEntities()->first())
//            ->transformed();
//
//        $this->putJson('test/many/'.$entity->entity_id.'/children', $transformed);
//
//        $this->assertResponseStatus(201);
//
//        $this->assertCount(4, array_pluck($this->getJsonResponseAsArray(), '_self'));
//
//        $this->assertEmpty(
//            array_diff(
//                $entity->secondTestEntities()->get()->pluck('entity_id')->toArray(),
//                array_pluck($transformed, 'entityId')
//            )
//        );
//    }
//
//    public function testDetachOne()
//    {
//        $entity = $this->makeEntity();
//        $second = $entity->secondTestEntities()->first();
//
//        $this->deleteJson('test/many/'.$entity->entity_id.'/children/'.$second->entity_id);
//
//        $this->assertResponseStatus(204);
//        $this->assertResponseHasNoContent();
//
//        $this->assertNotContains(
//            $second->entity_id,
//            $entity->secondTestEntities()->get()->pluck('entity_id')->toArray()
//        );
//    }
//
//    public function testDetachMany()
//    {
//        $entity = $this->makeEntity();
//
//        $this->deleteJson('test/many/'.$entity->entity_id.'/children');
//
//        $this->assertResponseStatus(204);
//        $this->assertResponseHasNoContent();
//
//        $this->assertTrue($entity->secondTestEntities()->get()->isEmpty());
//    }
//
//    /**
//     * Generates TestEntity with 3 SecondEntites.
//     * First of SecondEntites has 3 TestEntities, others has 1.
//     *
//     * @return TestEntity
//     */
//    protected function makeEntity()
//    {
//        $firstEntities = $this->getFactory(TestEntity::class)->count(5)->create();
//        $secondEntities = $this->getFactory(SecondTestEntity::class)->count(5)->create();
//
//        $firstIds = $firstEntities->take(3)->pluck('entity_id')->toArray();
//        $secondIds = $secondEntities->take(3)->pluck('entity_id')->toArray();
//
//        $first = $firstEntities->first();
//
//        $first->secondTestEntities()->attach($secondIds);
//        $first->secondTestEntities()->first()->testEntities()->sync($firstIds);
//
//        return $first;
//    }
}
