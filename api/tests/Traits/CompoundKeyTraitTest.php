<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */
use App\Models\Localization;

/**
 * Class CompoundKeyTraitTest.
 */
class CompoundKeyTraitTest extends TestCase
{
    /**
     * @expectedException     LogicException
     * @expectedExceptionCode 0
     */
    public function testBootCompoundKeyTrait()
    {
        MockSinglePK::bootCompoundKeyTrait();
    }

    public function testGetQualifiedColumnName()
    {
        $localization = new Localization();

        $qualifiedColumnName = $localization->getQualifiedColumnName('region_code');

        $this->assertEquals($qualifiedColumnName, 'localizations.region_code');
    }

    public function testSetKeysForSaveQuery()
    {
        // Create an entity
        $entity = factory(App\Models\TestEntity::class)->create();

        $this->putJson('/test/entities/'.$entity->entity_id.'/localizations/au', [
            'varchar' => 'foo',
            'decimal' => 0.234,
        ]);

        // For some reason the only way to access the function setKeysForSaveQuery is to put a
        // localization where one already exists. Can not access method directly.
        $this->putJson('/test/entities/'.$entity->entity_id.'/localizations/au', [
            'varchar' => 'foobar',
        ]);
        $this->assertResponseStatus(201);
    }
}

class MockSinglePK extends Localization
{
    protected $primaryKey = 'foo_id';
}
