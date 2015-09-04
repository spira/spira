<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use App\Models\TestEntity;

/**
 * Class ElasticSearchTest.
 */
class ElasticSearchTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        TestEntity::flushEventListeners();
        TestEntity::boot(); //run event listeners

        TestEntity::removeAllFromIndex();
    }

    /**
     * Test model is automatically added to index on save.
     */
    public function testElasticSearchAddToIndex()
    {
        /** @var TestEntity $testEntity */
        $testEntity = factory(TestEntity::class)->create();

        sleep(1); //elastic search takes some time to index

        $search = $testEntity->searchByQuery([
            'match' => [
                'entity_id' => $testEntity->entity_id,
            ],
        ]);

        $this->assertEquals(1, $search->totalHits());

        $testEntity->delete(); //clean up so it doesn't remain in the index
    }

    public function testElasticSearchRemoveFromIndex()
    {
        /** @var TestEntity $testEntity */
        $testEntity = factory(TestEntity::class)->create();

        $testEntity->delete();

        sleep(1); //elastic search takes some time to index

        $search = $testEntity->searchByQuery([
            'match' => [
                'entity_id' => $testEntity->entity_id,
            ],
        ]);

        $this->assertEquals(0, $search->totalHits());
    }

    public function testElasticSearchUpdateIndex()
    {
        /** @var TestEntity $testEntity */
        $testEntity = factory(TestEntity::class)->create();

        $testEntity->setAttribute('varchar', 'searchforthisvalue');
        $testEntity->save();

        sleep(1); //elastic search takes some time to index

        $search = $testEntity->searchByQuery([
            'match' => [
                'varchar' => 'searchforthisvalue',
            ],
        ]);

        $this->assertEquals(1, $search->totalHits());

        $testEntity->delete(); //clean up so it doesn't remain in the index
    }
}
