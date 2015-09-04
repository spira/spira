<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Extensions\Socialite\Parsers;

use App\Models\User;

trait UsernameTrait
{
    /**
     * Get the user's username.
     *
     * @return string
     */
    abstract protected function getUsernameAttribute();

    /**
     * Define filter for usernames.
     *
     * @param  string  $username
     *
     * @return string
     */
    protected function filterUsernameAttribute($username)
    {
        if ($this->usernameIsUnique($username)) {
            return $username;
        }

        return $this->makeUsernameUnique($username);
    }

    /**
     * Check if the username is unique.
     *
     * @param  string  $username
     *
     * @return bool
     */
    protected function usernameIsUnique($username)
    {
        return ! (bool) User::username($username)->get()->count();
    }

    /**
     * Make sure that the username is unique for Spira.
     *
     * The username retrieved from the social provider is not guaranteed to be
     * unique for Spira. So to ensure a seamless social signup and signin
     * process, modify the username if it is not unique to be unique.
     *
     * @param  string  $username
     *
     * @return string
     */
    protected function makeUsernameUnique($username)
    {
        // Try prettye methods first, ordered by priority
        $methods = [
            'removeSpaces',
            'replaceSpacesWithDots',
            'switchFirstLast',
            'InitialAndLast',
        ];

        foreach ($methods as $method) {
            $method = camel_case('username_'.$method);
            if (method_exists($this, $method)) {
                $tryName = $this->$method($username);

                if ($this->usernameIsUnique($tryName)) {
                    return $tryName;
                }
            }
        }

        // None of the pretty methods were successful, start numbering
        $suffix = 1;
        do {
            $tryName = $username.' '.$suffix;
            $suffix++;
        } while (! $this->usernameIsUnique($tryName));

        return $tryName;
    }

    /**
     * Removes spaces from username.
     *
     * @param  string  $username
     *
     * @return string
     */
    protected function usernameRemoveSpaces($username)
    {
        return str_replace(' ', '', $username);
    }

    /**
     * Removes spaces from username.
     *
     * @param  string  $username
     *
     * @return string
     */
    protected function usernameReplaceSpacesWithDots($username)
    {
        return str_replace(' ', '.', $username);
    }

    /**
     * Switch position of first and last name.
     *
     * @param  string  $username
     *
     * @return string
     */
    protected function usernameSwitchFirstLast($username)
    {
        $segments = explode(' ', $username);
        if (count($segments) == 2) {
            return implode(' ', array_reverse($segments));
        }

        // The username was not suitable for this operation, return unmodified
        return $username;
    }

    /**
     * Use first name initial and last name.
     *
     * @param  string  $username
     *
     * @return string
     */
    protected function usernameInitialAndLast($username)
    {
        $segments = explode(' ', $username);
        if (count($segments) == 2) {
            return $segments[0][0].$segments[1];
        }

        // The username was not suitable for this operation, return unmodified
        return $username;
    }
}
