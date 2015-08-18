<?php

namespace App\Services\Api\Vanilla\Api;

class User extends ApiAbstract
{
    /**
     * Get all users.
     *
     * @link   https://github.com/kasperisager/vanilla-api/wiki/Endpoints#find-all-users
     *
     * @return array
     */
    public function all()
    {
        return $this->get('users');
    }

    /**
     * Get all users.
     *
     * Note that the information in the @link for this method is incorrect. If
     * the user is successfully created, the server does not respond with the
     * new user, but with an empty response. If there is anything wrong, like
     * email already exists, username taken or invalid data, the server responds
     * with a error 400.
     *
     * When the user is created this way, a welcome email will be sent to the
     * user. No option to disable that, it's hardcoded in the user controller.
     *
     * @link   https://github.com/kasperisager/vanilla-api/wiki/Endpoints#create-a-new-user
     *
     * @param  string $username
     * @param  string $email
     * @param  string $password
     * @param  array  $role
     *
     * @return array
     */
    public function create($username, $email, $password, $role = [8])
    {
        $parameters = [
            'Name' => $username,
            'Email' => $email,
            'Password' => $password,
            'RoleID' => $role,
        ];

        return $this->post('users', $parameters);
    }
}
