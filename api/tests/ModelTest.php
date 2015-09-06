<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

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
        $userClass = '\App\Models\User';

        $user = new $userClass();

        $dynamicTableName = $user->getTable();

        $staticTableName = $userClass::getTableName();

        $this->assertEquals($dynamicTableName, $staticTableName);
    }
}
