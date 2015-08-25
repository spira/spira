<?php

namespace App\Services\Api\Vanilla\Api;

class User extends ApiAbstract
{
    /**
     * Connect a user from with a new or existing user in Vanilla.
     *
     * @param  string $userId
     * @param  string $username
     * @param  string $email
     * @param  string $photo
     * @param  array  $roles
     *
     * @return array
     */
    public function sso($userId, $username, $email, $photo = '', array $roles = [])
    {
        $parameters = [
            'UniqueID' => $userId,
            'Name' => $username,
            'Email' => $email,
            'Photo' => $photo,
            'Roles' => $roles
        ];

        $parameters = array_filter($parameters);

        return $this->post('users/sso', $parameters);
    }
}
