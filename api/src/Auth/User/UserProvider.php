<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spira\Auth\User;

use Illuminate\Support\Str;
use Illuminate\Contracts\Auth\Authenticatable;
use Spira\Contract\Exception\NotImplementedException;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;

class UserProvider implements \Illuminate\Contracts\Auth\UserProvider
{
    /**
     * @var string
     */
    private $model;
    /**
     * @var HasherContract
     */
    private $hasher;

    /**
     * @var \Closure|null
     */
    private $tokenUserProvider;

    /**
     * @param HasherContract $hasher
     * @param string $model
     * @param \Closure $tokenUserProvider
     */
    public function __construct(HasherContract $hasher, $model, \Closure $tokenUserProvider = null)
    {
        $this->model = $model;
        $this->hasher = $hasher;
        $this->tokenUserProvider = $tokenUserProvider;
    }

    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed  $identifier
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById($identifier)
    {
        return $this->createModel()->newQuery()->find($identifier);
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     *
     * @param  mixed $identifier
     * @param  string $token payload array
     * @return Authenticatable|null
     * @throws \Exception
     */
    public function retrieveByToken($identifier, $token)
    {
        $function = $this->tokenUserProvider;
        if (! is_null($function) && $user = $function($token, $this)) {
            return $user;
        }

        return $this->retrieveById($identifier);
    }

    /**
     * Update the "remember me" token for the given user in storage.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @param  string $token
     * @throws NotImplementedException
     */
    public function updateRememberToken(Authenticatable $user, $token)
    {
        throw new NotImplementedException;
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array  $credentials
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        // First we will add each credential element to the query as a where clause.
        // Then we can execute the query and, if we found a user, return it in a
        // Eloquent User "model" that will be utilized by the Guard instances.
        $query = $this->createModel()->newQuery();

        foreach ($credentials as $key => $value) {
            if (! Str::contains($key, 'password')) {
                $query->where($key, $value);
            }
        }

        return $query->first();
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  array  $credentials
     * @return bool
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        $plain = $credentials['password'];

        return $this->hasher->check($plain, $user->getAuthPassword());
    }

    /**
     * Create a new instance of the model.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createModel()
    {
        $class = '\\'.ltrim($this->model, '\\');

        return new $class;
    }
}
