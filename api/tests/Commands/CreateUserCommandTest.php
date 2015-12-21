<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use App\Models\Role;
use App\Models\User;
use App\Console\Commands\CreateUserCommand;

/**
 * Class CreateUserCommandTest.
 * @group commands
 */
class CreateUserCommandTest extends TestCase
{
    public function testCreateUserCommand()
    {
        $email = 'john.command.example@example.com';
        $name = 'John';
        $username = 'john';
        $password = 'hunter2';
        $role = Role::ADMIN_ROLE;

        $cmd = $this->mockUserCreateCommand($email, $name, $username, $password, $role);

        $cmd->shouldReceive('info')->once()->andReturn(null);

        $this->assertEquals(0, $cmd->handle());

        /** @var User $userCreated */
        $userCreated = User::where('email', '=', $email)->first();

        $this->assertNotNull($userCreated, 'User was not created');

        $this->assertEquals($email, $userCreated->email);
        $this->assertEquals($name, $userCreated->first_name);
        $this->assertEquals($username, $userCreated->username);

        $this->assertTrue($userCreated->roles->contains(function ($key, Role $roleModel) use ($role) {
            return $roleModel->key == $role;
        }), 'Role is applied');

        $this->assertTrue(Auth::attempt(['email' => $email, 'password' => $password]), 'Credentials work.');
    }

    public function testCreateInvalidUserCommand()
    {
        $email = 'invalid-email';
        $name = 'John';
        $username = 'john';
        $password = 'hunter2';
        $role = Role::ADMIN_ROLE;

        $cmd = $this->mockUserCreateCommand($email, $name, $username, $password, $role);

        $cmd->shouldReceive('error')->once()->andReturn(null);
        $this->assertEquals(1, $cmd->handle());
    }

    /**
     * @param $email
     * @param $name
     * @param $username
     * @param $password
     * @param $role
     * @return CreateUserCommand
     */
    private function mockUserCreateCommand($email, $name, $username, $password, $role)
    {
        /** @var CreateUserCommand $cmd */
        $cmd = Mockery::mock(CreateUserCommand::class.'[ask, secret, choice, info, error]');

        $cmd->shouldReceive('ask')
            ->with('Enter email')
            ->once()
            ->andReturn($email);

        $cmd->shouldReceive('ask')
            ->with('Enter first name')
            ->once()
            ->andReturn($name);

        $cmd->shouldReceive('ask')
            ->with('Enter username', $username)
            ->once()
            ->andReturn($username);

        $cmd->shouldReceive('secret')
            ->with('Enter password')
            ->once()
            ->andReturn($password);

        $cmd->shouldReceive('choice')
            ->with('What roles should be applied? (comma separate options)', ROLE::$roles, null, null, true)
            ->once()
            ->andReturn([$role]);

        return $cmd;
    }
}
