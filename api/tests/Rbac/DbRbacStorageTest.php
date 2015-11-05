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
use Spira\Rbac\Storage\Db\AssignmentStorage;
use Spira\Rbac\Storage\Db\ItemStorage;
use Spira\Rbac\Storage\Storage;

class DbRbacStorageTest extends TestCase
{
    /**
     * @var Storage
     */
    protected $auth;

    public function setUp()
    {
        parent::setUp();
        $this->initAuth();
    }

    public function initAuth()
    {
        $this->auth = new Storage($this->app->make(ItemStorage::class), $this->app->make(AssignmentStorage::class));
    }

    public function testGet()
    {
        $role = new Role('some role');
        $this->auth->addItem($role);

        $this->assertNull($this->auth->getItem(''));
        $this->assertNull($this->auth->getItem('role does not exist'));

        $this->assertInstanceOf(Role::class, $this->auth->getItem('some role'));
    }

    public function testGetAll()
    {
        $this->assertCount(0, $this->auth->getItems(Role::TYPE_ROLE));
        $role = new Role('some role');
        $this->auth->addItem($role);

        $role = new Role('some role 2');
        $this->auth->addItem($role);

        $this->assertCount(2, $this->auth->getItems(Role::TYPE_ROLE));
    }

    public function testAdd()
    {
        $role = new Role('admin Z');
        $role->description = 'administrator';
        $this->assertTrue($this->auth->addItem($role));

        $permission = new Permission('edit post');
        $permission->description = 'edit a post';
        $this->assertTrue($this->auth->addItem($permission));
    }

    public function testRemove()
    {
        $role = new Role('some role');
        $permission = new Permission('some permission');
        $this->auth->addItem($role);
        $this->auth->addItem($permission);
        $this->auth->addChild($role, $permission);
        $this->assertInstanceOf(Role::class, $this->auth->getItem('some role'));

        $this->auth->removeItem($role);
        $this->assertNull($this->auth->getItem('some role'));
        $this->assertNotNull($this->auth->getItem('some permission'));
    }

    public function testUpdate()
    {
        $role = new Role('some role');
        $this->auth->addItem($role);
        $this->auth->assign($role, 'e9f941b8-50f2-31af-a740-87fe9aa3f60f');

        $permission = new Permission('some permission');
        $this->auth->addItem($permission);
        $this->auth->addChild($role, $permission);

        $role->name = 'some new role name';

        $result = $this->auth->updateItem('some role', $role);

        $this->assertTrue($result);

        $this->assertNull($this->auth->getItem('some role'));
        $this->assertInstanceOf(Role::class, $this->auth->getItem('some new role name'));
        $assignments = $this->auth->getAssignments('e9f941b8-50f2-31af-a740-87fe9aa3f60f');
        $assignment = current($assignments);
        $this->assertEquals($assignment->roleName, $role->name);
    }

    public function testAddChildSelf()
    {
        $role = new Role('some role');
        $this->auth->addItem($role);
        $this->setExpectedException('InvalidArgumentException', 'Cannot add \'some role \' as a child of itself.');
        $this->auth->addChild($role, $role);
    }

    public function testAddChildPermissionOverROle()
    {
        $role = new Role('some role');
        $permission = new Permission('some permission 1');
        $this->auth->addItem($role);
        $this->auth->addItem($permission);

        $this->setExpectedException('InvalidArgumentException', 'Cannot add a role as a child of a permission.');
        $this->auth->addChild($permission, $role);
    }

    public function testAddChildLoop()
    {
        $permission = new Permission('some permission 1');
        $permission2 = new Permission('some permission 2');
        $permission3 = new Permission('some permission 3');
        $this->auth->addItem($permission);
        $this->auth->addItem($permission2);
        $this->auth->addItem($permission3);

        $this->setExpectedException('InvalidArgumentException', "Cannot add 'some permission 1' as a child of 'some permission 3'. A loop has been detected.");
        $this->auth->addChild($permission, $permission2);
        $this->auth->addChild($permission2, $permission3);
        $this->auth->addChild($permission3, $permission);
    }

    public function testGetChildren()
    {
        $user = new Role('user Z');
        $this->auth->addItem($user);
        $this->assertCount(0, $this->auth->getChildren($user->name));

        $changeName = new Permission('changeName');
        $this->auth->addItem($changeName);
        $this->auth->addChild($user, $changeName);
        $this->assertCount(1, $this->auth->getChildren($user->name));
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

        $this->auth->assign($reader, 'd2acdd06-81a3-3c3f-b105-8f8082e8a6fb');
        $this->auth->assign($author, '0bea1048-180f-3f45-b63d-828c06d50718');
        $this->auth->assign($admin, 'ea9ffb41-0412-3769-93f0-38062fe1d302');
    }

    public function testAssign()
    {
        $role = new Role('some role');
        $this->setExpectedException('InvalidArgumentException', 'Unknown role \'some role\'.');
        $this->auth->assign($role, 'some user');

        $this->auth->addItem($role);
        $this->assertInstanceOf(Assignment::class, $this->auth->assign($role, 'some user'));

        $assignments = $this->auth->getAssignments('some user');
        $this->assertNotEmpty($assignments);
        $this->assertEquals($assignments[0]->roleName, $role->name);
    }

    public function testRevoke()
    {
        $role = new Role('some role');
        $this->auth->addItem($role);
        $this->assertInstanceOf(Assignment::class, $this->auth->assign($role, 'e9f941b8-50f2-31af-a740-87fe9aa3f60f'));

        $this->assertFalse($this->auth->revoke($role, ''));

        $this->auth->revoke($role, 'e9f941b8-50f2-31af-a740-87fe9aa3f60f');
        $this->assertEmpty($this->auth->getAssignments('e9f941b8-50f2-31af-a740-87fe9aa3f60f'));
    }

    public function testAssignMultipleRoles()
    {
        $this->prepareData();

        $reader = $this->auth->getItem('reader');
        $author = $this->auth->getItem('author');
        $this->auth->assign($reader, 'c0f30c58-7bae-39d8-b070-3f537157f5d2');
        $this->auth->assign($author, 'c0f30c58-7bae-39d8-b070-3f537157f5d2');
    }
}
