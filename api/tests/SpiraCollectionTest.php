<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use Spira\Model\Collection\ItemTypeException;

class SpiraCollectionTest extends TestCase
{
    public function testGetClass()
    {
        $entity = new \App\Models\TestEntity();
        $collection = $entity->newCollection();
        $this->assertEquals(get_class($entity), $collection->getClassName());
    }

    public function testInvalidAdd()
    {
        $entity = new \App\Models\TestEntity();
        $collection = $entity->newCollection();
        $this->setExpectedException(ItemTypeException::class, 'Item must be instance of '.get_class($entity));
        $collection->add(new \StdClass);
    }
}
