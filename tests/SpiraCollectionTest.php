<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */
namespace Spira\Core\tests;

use Spira\Core\Model\Collection\ItemTypeException;
use Spira\Core\Model\Test\TestEntity;

class SpiraCollectionTest extends TestCase
{
    public function testGetClass()
    {
        $entity = new TestEntity();
        $collection = $entity->newCollection();
        $this->assertEquals(get_class($entity), $collection->getClassName());
    }

    public function testInvalidAdd()
    {
        $entity = new TestEntity();
        $collection = $entity->newCollection();
        $this->setExpectedException(ItemTypeException::class, 'Item must be instance of '.get_class($entity));
        $collection->add(new \StdClass);
    }

    public function testValidAdd()
    {
        $entity = new TestEntity();
        $collection = $entity->newCollection();
        $collection->add($entity);
        $this->assertInstanceOf(TestEntity::class, $collection->first());
    }
}
