<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use App\Extensions\Rbac\UserAssignmentStorage;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Spira\Core\Contract\Exception\NotImplementedException;
use Spira\Rbac\Access\Gate;
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
        $this->app->instance([GateContract::class], new Gate($this->auth, function () { return $this->app['auth']->user(); }));
    }

    protected function clean()
    {
        if (file_exists(__DIR__.'/Rbac/item.php')) {
            unlink(__DIR__.'/Rbac/item.php');
        }
    }

    public function testUpdate()
    {
        $this->setExpectedException(NotImplementedException::class, 'Massive update via Storage is disabled');
        $role = new Role('some role');
        $this->auth->addItem($role);

        $permission = new Permission('some permission');
        $this->auth->addItem($permission);
        $this->auth->addChild($role, $permission);

        $role->name = 'some new role name';

        $result = $this->auth->updateItem('some role', $role);
    }

    public function testRemoveAllAssignments()
    {
        $this->setExpectedException(NotImplementedException::class, 'Massive removal via Storage is disabled');
        $role = new Role('some role');
        $this->auth->addItem($role);
        $this->auth->removeAllAssignments($role);
    }

    public function testRevoke()
    {
        $role = new Role('some role');
        $user = $this->createUser();
        $this->auth->addItem($role);
        $this->assertInstanceOf(Assignment::class, $this->auth->assign($role, $user->user_id));

        $this->assertFalse($this->auth->revoke($role, ''));

        $this->auth->revoke($role, $user->user_id);
        $this->assertEmpty($this->auth->getAssignments($user->user_id));
    }

    public function testRemove()
    {
        $this->setExpectedException(NotImplementedException::class, 'Massive removal via Storage is disabled');
        $role = new Role('some role');
        $permission = new Permission('some permission');
        $this->auth->addItem($role);
        $this->auth->addItem($permission);
        $this->auth->addChild($role, $permission);
        $this->assertInstanceOf(Role::class, $this->auth->getItem('some role'));

        $this->auth->removeItem($role);
    }

    public function testAssignSame()
    {
        $role = new Role('some role');
        $user = $this->createUser();
        $this->auth->addItem($role);
        $this->assertInstanceOf(Assignment::class, $this->auth->assign($role, $user->user_id));
        $this->setExpectedException('Illuminate\Database\QueryException');
        $this->auth->assign($role, $user->user_id);
    }

    protected function prepareData()
    {
        $rule = new AuthorRule;

        $createPost = new Permission('createPost');
        $createPost->description = 'create a post';
        $this->auth->addItem($createPost);

        $readPost = new Permission('readPost');
        $readPost->description = 'read a post';
        $this->auth->addItem($readPost);

        $updatePost = new Permission('updatePost');
        $updatePost->description = 'update a post';
        $updatePost->attachRule($rule);
        $this->auth->addItem($updatePost);

        $updateAnyPost = new Permission('updateAnyPost');
        $updateAnyPost->description = 'update any post';
        $this->auth->addItem($updateAnyPost);

        $deletePost = new Permission('deletePost');
        $deletePost->description = 'delete a post';
        $this->auth->addItem($deletePost);

        $reader = new Role('reader');
        $this->auth->addItem($reader);
        $this->auth->addChild($reader, $readPost);

        $author = new Role('author');
        $this->auth->addItem($author);
        $this->auth->addChild($author, $createPost);
        $this->auth->addChild($author, $updatePost);
        $this->auth->addChild($author, $reader);
        $this->auth->addChild($author, $deletePost);

        $admin = new Role('admin C');
        $this->auth->addItem($admin);
        $this->auth->addChild($admin, $author);
        $this->auth->addChild($admin, $updateAnyPost);
    }

    public function testAssignMultipleRoles()
    {
        $this->prepareData();
        $user = $this->createUser();
        $reader = $this->auth->getItem('reader');
        $author = $this->auth->getItem('author');
        $this->auth->assign($reader, $user->user_id);
        $this->auth->assign($author, $user->user_id);
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->clean();
    }
}
