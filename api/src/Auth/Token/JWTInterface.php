<?php
/**
 * Created by PhpStorm.
 * User: ivanmatveev
 * Date: 13.09.15
 * Time: 20:25
 */

namespace Spira\Auth\Token;


interface JWTInterface
{
    /**
     * @param  array  $payload
     * @return string
     */
    public function encode(array $payload);

    /**
     * @param  string  $token
     * @return array
     */
    public function decode($token);
}