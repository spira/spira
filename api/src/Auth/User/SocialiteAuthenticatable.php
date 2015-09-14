<?php
/**
 * Created by PhpStorm.
 * User: ivanmatveev
 * Date: 14.09.15
 * Time: 13:56
 */

namespace Spira\Auth\User;


interface SocialiteAuthenticatable
{
    /**
     * @param string $method
     * @return void
     */
    public function setCurrentAuthMethod($method);

    /**
     * @return string
     */
    public function getCurrentAuthMethod();
}