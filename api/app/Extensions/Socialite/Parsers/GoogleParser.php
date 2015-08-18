<?php

namespace App\Extensions\Socialite\Parsers;

class GoogleParser extends AbstractParser
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
        return $this->user->name;
    }

    /**
     * Get the user's first name.
     *
     * @return string
     */
    protected function getFirstNameAttribute()
    {
        return $this->user->user['name']['givenName'];
    }

    /**
     * Get the user's last name.
     *
     * @return string
     */
    protected function getLastNameAttribute()
    {
        return $this->user->user['name']['familyName'];
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
