<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use Illuminate\Auth\GenericUser;
use Spira\Rbac\Access\Gate;
use Spira\Rbac\Item\Permission;
use Spira\Rbac\Item\Role;
use Spira\Rbac\Storage\DbStorage;

class RbacGateTest extends TestCase
{
    /**
     * @var DbStorage
     */
    protected $auth;

    /**
     * @var Gate
     */
    protected $gate;

    public function setUp()
    {
        parent::setUp();
        $this->auth = $this->app->make(DbStorage::class);
        $this->gate = $this->app->make(Gate::GATE_NAME);
    }

    public function testCheckAccess()
    {
        $this->prepareData();

        $testSuites = [
            'reader A' => [
                'createPost' => false,
                'readPost' => true,
                'updatePost' => false,
                'updateAnyPost' => false,
            ],
            'author B' => [
                'createPost' => true,
                'readPost' => true,
                'updatePost' => true,
                'deletePost' => true,
                'updateAnyPost' => false,
            ],
            'admin C' => [
                'createPost' => true,
                'readPost' => true,
                'updatePost' => false,
                'updateAnyPost' => true,
            ],
        ];

        $params = ['authorID' => 'author B'];

        foreach ($testSuites as $user => $tests) {
            foreach ($tests as $permission => $result) {
                $genericUser = new GenericUser(['id' => $user]);
                $this->assertEquals($result,
                    $this->gate->forUser($genericUser)->check($permission, $params),
                    "Checking $user can $permission"
                );
            }
        }
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
}
