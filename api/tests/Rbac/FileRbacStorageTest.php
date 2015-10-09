<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use Spira\Rbac\Item\Assignment;
use Spira\Rbac\Item\Permission;
use Spira\Rbac\Item\Role;
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

    public function testAssignSame()
    {
        $role = new Role('some role');
        $this->auth->addItem($role);
        $this->assertInstanceOf(Assignment::class, $this->auth->assign($role, 'some user'));
        $this->setExpectedException('InvalidArgumentException', 'Authorization item \'some role\' has already been assigned to user \'some user\'.');
        $this->auth->assign($role, 'some user');
    }

    public function testRemoveAllAssignments()
    {
        $role = new Role('some role');
        $this->auth->addItem($role);
        $this->assertInstanceOf(Assignment::class, $this->auth->assign($role, 'some user'));
        $this->auth->removeAllAssignments($role);
        $this->assertEmpty($this->auth->getAssignments('some user'));
    }

    public function testUpdateNameToExistingItem()
    {
        $role = new Role('some role');
        $this->auth->addItem($role);

        $role2 = new Role('some new role');
        $this->auth->addItem($role2);

        $this->setExpectedException('InvalidArgumentException', 'Unable to change the item name. The name \'some role\' is already used by another item.');

        $role2->name = 'some role';
        $this->auth->updateItem('some new role', $role2);
    }

    public function testAddChildNotExist()
    {
        $permission = new Permission('some permission 1');
        $permission2 = new Permission('some permission 2');

        $this->auth->addItem($permission);

        $this->setExpectedException('InvalidArgumentException', 'Either \'some permission 1\' or \'some permission 2\' does not exist.');
        $this->auth->addChild($permission, $permission2);
    }

    public function testAddChildExistingChild()
    {
        $permission = new Permission('some permission 1');
        $permission2 = new Permission('some permission 2');

        $this->auth->addItem($permission);
        $this->auth->addItem($permission2);

        $this->auth->addChild($permission, $permission2);

        $this->setExpectedException('InvalidArgumentException', 'The item \'some permission 1\' already has a child \'some permission 2\'.');

        $this->auth->addChild($permission, $permission2);
    }

    public function testLoad()
    {
        $storage = new AssignmentStorage(__DIR__.'/assignment_test.php');
        $assignments = $storage->getAssignments('some user');
        $assignment = current($assignments);
        $this->assertEquals($assignment->roleName, 'some role');
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->clean();
    }

    protected function clean()
    {
        if (file_exists(__DIR__.'/item.php')) {
            unlink(__DIR__.'/item.php');
        }

        if (file_exists(__DIR__.'/assignment.php')) {
            unlink(__DIR__.'/assignment.php');
        }
    }
}
