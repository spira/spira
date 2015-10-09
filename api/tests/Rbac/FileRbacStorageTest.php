<?php

use Spira\Rbac\Storage\File\AssignmentStorage;
use Spira\Rbac\Storage\File\ItemStorage;
use Spira\Rbac\Storage\Storage;

class FileRbacStorageTest extends DbRbacStorageTest
{
    public function setUp()
    {
        parent::setUp();
        $this->clean();
        $this->auth = new Storage(
                new ItemStorage(__DIR__.'/item.php'),
                new AssignmentStorage(__DIR__.'/assignment.php')
            );
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->clean();
    }

    protected function clean()
    {
        if (file_exists(__DIR__.'/item.php')){
            unlink(__DIR__.'/item.php');
        }

        if (file_exists(__DIR__.'/assignment.php')){
            unlink(__DIR__.'/assignment.php');
        }
    }
}