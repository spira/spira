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

    /**
     * Remove a user.
     *
     * The method to remove a user can be "keep", "wipe" or "delete".
     * - keep: Delete the user but keep user's content.
     * - wipe: Delete the user, keep user's content but blank it.
     * - delete: Delete the user and remove all user's content.
     *
     * The linked method in the API is currently broken and does not work so for
     * now we call a hotfixed version in the extended API.
     *
     * @link   https://github.com/kasperisager/vanilla-api/wiki/Endpoints#remove-a-user
     *
     * @param  int    $id
     * @param  string $method
     *
     * @return array
     */
    public function remove($id, $method = 'delete')
    {
        $parameters = [
            'Method' => $method
        ];

        return $this->delete('users/hotfix/'.$id, $parameters);
    }
}
