<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Extensions\Socialite\Parsers;

class TwitterParser extends AbstractParser
{
    /**
     * Get the user's token.
     *
     * @return string
     */
    protected function getTokenAttribute()
    {
        return $this->user->token;
    }

    /**
     * Get the user's email address.
     *
     * @return string
     */
    protected function getEmailAttribute()
    {
        return $this->user->email;
    }

    /**
     * Get the user's username.
     *
     * @return string
     */
    protected function getUsernameAttribute()
    {
        return $this->user->nickname;
    }

    /**
     * Get the user's first name.
     *
     * @return string
     */
    protected function getFirstNameAttribute()
    {
        return head(explode(' ', $this->user->name));
    }

    /**
     * Get the user's last name.
     *
     * @return string
     */
    protected function getLastNameAttribute()
    {
        return last(explode(' ', $this->user->name));
    }

    /**
     * Get the user's avatar.
     *
     * @return string
     */
    protected function getAvatarImgUrlAttribute()
    {
        return $this->user->avatar;
    }
}
