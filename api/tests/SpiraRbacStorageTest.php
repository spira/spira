<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use App\Extensions\Rbac\UserAssignmentStorage;
use Spira\Rbac\Item\Assignment;
use Spira\Rbac\Item\Permission;
use Spira\Rbac\Item\Role;
use Spira\Rbac\Storage\File\ItemStorage;
use Spira\Rbac\Storage\Storage;

class SpiraRbacStorageTest extends FileRbacStorageTest
{
    public function initAuth()
    {
        $this->clean();
        $this->auth = new Storage(
            new ItemStorage(__DIR__.'/Rbac/item.php'),
            new UserAssignmentStorage()
        );
    }

    protected function clean()
    {
        if (file_exists(__DIR__.'/Rbac/item.php')) {
            unlink(__DIR__.'/Rbac/item.php');
        }
    }

    public function testUpdate()
    {
        $this->setExpectedException('Spira\Contract\Exception\NotImplementedException', 'Massive update via Storage is disabled');
        $role = new Role('some role');
        $this->auth->addItem($role);
        $this->auth->assign($role, 'e9f941b8-50f2-31af-a740-87fe9aa3f60f');

        $permission = new Permission('some permission');
        $this->auth->addItem($permission);
        $this->auth->addChild($role, $permission);

        $role->name = 'some new role name';

        $result = $this->auth->updateItem('some role', $role);
    }

    public function testRemoveAllAssignments()
    {
        $this->setExpectedException('Spira\Contract\Exception\NotImplementedException', 'Massive removal via Storage is disabled');
        $role = new Role('some role');
        $this->auth->addItem($role);
        $this->assertInstanceOf(Assignment::class, $this->auth->assign($role, 'e9f941b8-50f2-31af-a740-87fe9aa3f60f'));
        $this->auth->removeAllAssignments($role);
    }

    public function testRemove()
    {
        $this->setExpectedException('Spira\Contract\Exception\NotImplementedException', 'Massive removal via Storage is disabled');
        $role = new Role('some role');
        $permission = new Permission('some permission');
        $this->auth->addItem($role);
        $this->auth->addItem($permission);
        $this->auth->addChild($role, $permission);
        $this->assertInstanceOf(Role::class, $this->auth->getItem('some role'));

        $this->auth->removeItem($role);
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->clean();
    }
}
