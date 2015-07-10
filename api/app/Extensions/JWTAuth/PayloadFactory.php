<?php

namespace App\Extensions\JWTAuth;

use Tymon\JWTAuth\PayloadFactory as PayloadFactoryBase;

class PayloadFactory extends PayloadFactoryBase
{
    /**
     * Create a random value for the token.
     *
     * @return string
     */
    protected function jti()
    {
        return str_random(16);
    }
}
