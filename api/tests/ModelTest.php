<?php


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
