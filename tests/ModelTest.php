<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spira\Core\tests;

use Spira\Core\Model\Model\DataModel;
use Spira\Core\Model\Model\VirtualModel;
use Spira\Core\Model\Test\TestEntity;

/**
 * Class ModelTest.
 */
class ModelTest extends TestCase
{
    /**
     * Test Model can access table statically.
     */
    public function testStaticTableNameAccess()
    {
        $userClass = TestEntity::class;

        $user = new $userClass();

        $dynamicTableName = $user->getTable();

        $staticTableName = $userClass::getTableName();

        $this->assertEquals($dynamicTableName, $staticTableName);
    }

    public function testStaticPrimaryKeyNameAccess()
    {
        $userClass = TestEntity::class;
        /** @var TestEntity $user */
        $user = new $userClass();

        $dynamicPrimaryKey = $user->getKeyName();

        $staticPrimaryKey = $userClass::getPrimaryKey();

        $this->assertEquals($dynamicPrimaryKey, $staticPrimaryKey);
    }

    /**
     * @expectedException \LogicException
     */
    public function testVirtualModelSaveFailure()
    {
        $virtualModel = new DataModel();

        $virtualModel->save(['foo' => 'bar']);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testVirtualModelNoPrimaryKeyAccess()
    {
        $virtualModel = new DataModel();

        $virtualModel->getKey();
    }

    public function testVirtualModelWithPrimaryKeyAccess()
    {
        $virtualModel = new MockVirtualPK;
        $virtualModel->foo_id = 'baz';

        $pk = $virtualModel->getKey();

        $this->assertEquals('baz', $pk);
    }
}

class MockVirtualPK extends VirtualModel
{
    protected $primaryKey = 'foo_id';
}
