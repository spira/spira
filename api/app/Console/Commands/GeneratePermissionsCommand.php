<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Console\Commands;

use App\Http\Auth\ManipulateWithOwn;
use App\Http\Controllers\UserController;
use Illuminate\Console\Command;
use Spira\Rbac\Item\Permission;
use Spira\Rbac\Item\Role;
use Spira\Rbac\Storage\DbStorage;

class GeneratePermissionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rbac:permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Basic spira permissions';

    public function handle()
    {
        $auth = $this->getAuthStorage();

        if (! $auth->getItem(UserController::class.'@getOne')) {
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
    }

    /**
     * @return DbStorage
     */
    protected function getAuthStorage()
    {
        return app(DbStorage::class);
    }
}
