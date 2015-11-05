<?php


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