<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use App\Http\Auth\ManipulateWithOwn;
use Illuminate\Database\Migrations\Migration;
use Spira\Rbac\Item\Permission;
use Spira\Rbac\Item\Role;
use Spira\Rbac\Storage\DbStorage;
use App\Http\Controllers\UserController;

class AddPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $auth = $this->getAuthStorage();

        $getOne = new Permission(UserController::class.'@getOne');
        $getOne->description = 'Get single user record by id';
        $auth->addItem($getOne);

        $getAllPaginated = new Permission(UserController::class.'@getAllPaginated');
        $getAllPaginated->description = 'Get all users';
        $auth->addItem($getAllPaginated);

        $patchOne = new Permission(UserController::class.'@patchOne');
        $patchOne->description = 'Update user record by id';
        $auth->addItem($patchOne);

        $deleteOne = new Permission(UserController::class.'@deleteOne');
        $deleteOne->description = 'Delete user by id';
        $auth->addItem($deleteOne);

        $adminRole = new Role('admin');
        $adminRole->description = 'Admin role';
        $auth->addItem($adminRole);

        $userRole = new Role('user');
        $auth->addItem($userRole);

        $auth->addChild($adminRole, $getOne);
        $auth->addChild($adminRole, $getAllPaginated);

        $auth->addChild($adminRole, $patchOne);
        $auth->addChild($adminRole, $deleteOne);
        $auth->addChild($adminRole, $userRole);

        $manipulateWithOwn = new Permission('manipulateWithOwn');
        $manipulateWithOwn->description = 'General permission to update record which belongs to the user';
        $manipulateWithOwn->attachRule(new ManipulateWithOwn());
        $auth->addItem($manipulateWithOwn);

        $auth->addChild($userRole, $manipulateWithOwn);

        $auth->addChild($manipulateWithOwn, $patchOne);
        $auth->addChild($manipulateWithOwn, $getOne);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }

    /**
     * @return DbStorage
     */
    protected function getAuthStorage()
    {
        return app(DbStorage::class);
    }
}
