<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

class RoleTest extends TestCase
{
    public function testGetAll()
    {
        $this->getJson('/roles');

        $this->assertResponseOk();
        $this->shouldReturnJson();
        $this->assertJsonArray();
        $this->assertJsonMultipleEntries();
    }
}
