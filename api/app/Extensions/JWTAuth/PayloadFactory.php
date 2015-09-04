<?php

namespace App\Extensions\JWTAuth;

use Tymon\JWTAuth\PayloadFactory as PayloadFactoryBase;

class PayloadFactory extends PayloadFactoryBase
{
    /**
     * @var array
     */
    protected $defaultClaims = ['iss', 'aud', 'iat', 'exp', 'nbf', 'jti', '_user'];

    /**
     * Set the Issuer (iss) claim.
     *
     * @return string
     */
    public function iss()
    {
        return $this->request->getHttpHost();
    }

    /**
     * Set the Audience (aud) claim.
     *
     * @return string
     */
    public function aud()
    {
        return str_replace('api.', '', $this->request->getHttpHost());
    }

    /**
     * Create a random value for the jti claim.
     *
     * @return string
     */
    protected function jti()
    {
        return str_random(16);
    }
}
