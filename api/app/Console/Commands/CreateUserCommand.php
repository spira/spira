<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use App\Models\UserCredential;
use Illuminate\Console\Command;
use Illuminate\Support\Debug\Dumper;
use Illuminate\Support\Facades\Validator;
use Rhumsaa\Uuid\Uuid;

class CreateUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create user with credentials.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $email = $this->ask('Enter email');
        $name = $this->ask('Enter first name');
        $username = $this->ask('Enter username', strtolower($name));
        $password = $this->secret('Enter password');

        $roles = $this->choice('What roles should be applied? (comma separate options)', Role::$roles, null, null, true);

        $userData = [
            'user_id' => Uuid::uuid4(),
            'email' => $email,
            'first_name' => $name,
            'username' => $username,
        ];

        $validationRules = User::getValidationRules($userData['user_id']);
        unset($validationRules[User::getPrimaryKey()]);
        $validator = Validator::make($userData, $validationRules);

        if ($validator->fails()) {
            $this->error('Validation failed:');
            // @codeCoverageIgnoreStart
            if (env('APP_ENV') != 'testing') {
                (new Dumper)->dump($validator->errors()->toArray());
            }
            // @codeCoverageIgnoreEnd
            return 1;
        }

        $user = new User($userData);
        $user->save();

        $user->roles()->sync($roles);

        $user->setCredential(new UserCredential(['password' => $password]));

        $this->info('Successfully created user:');

        // @codeCoverageIgnoreStart
        if (env('APP_ENV') != 'testing') {
            (new Dumper)->dump($user->fresh()->toArray());
        }
        // @codeCoverageIgnoreEnd

        return 0;
    }
}
