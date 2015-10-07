<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use Spira\Rbac\Item\Permission;
use Spira\Rbac\Item\Role;
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
        $this->auth = $this->app->make(Storage::class);
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

        $this->auth->assign($reader, 'reader A');
        $this->auth->assign($author, 'author B');
        $this->auth->assign($admin, 'admin C');
    }

    public function testAssignMultipleRoles()
    {
        $this->prepareData();

        $reader = $this->auth->getItem('reader');
        $author = $this->auth->getItem('author');
        $this->auth->assign($reader, 'readingAuthor');
        $this->auth->assign($author, 'readingAuthor');
    }

    public function testAssignmentsToIntegerId()
    {
        $this->prepareData();

        $reader = $this->auth->getItem('reader');
        $author = $this->auth->getItem('author');
        $this->auth->assign($reader, 42);
        $this->auth->assign($author, 1337);
        $this->auth->assign($reader, 1337);

        $this->assertEquals(0, count($this->auth->getAssignments(0)));
        $this->assertEquals(1, count($this->auth->getAssignments(42)));
        $this->assertEquals(2, count($this->auth->getAssignments(1337)));
    }
}
