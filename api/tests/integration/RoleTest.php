<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

/**
 * Class RoleTest.
 * @group integration
 */
class RoleTest extends TestCase
{
    public function testGetAll()
    {
        $this->withAuthorization()->getJson('/roles');

        $this->assertResponseOk();
        $this->shouldReturnJson();
        $this->assertJsonArray();
        $this->assertJsonMultipleEntries();
    }

    public function testGetAllWithNestedPermissions()
    {

        $this->withAuthorization()->getJson('/roles', ['with-nested' => 'permissions']);

        $this->assertResponseOk();
        $this->shouldReturnJson();
        $this->assertJsonArray();
        $this->assertJsonMultipleEntries();

        $result = json_decode($this->response->getContent(), true);

        $this->assertArrayHasKey('_permissions', $result[0]);
    }

}
