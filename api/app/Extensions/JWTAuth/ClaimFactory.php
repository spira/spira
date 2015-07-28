<?php namespace App\Extensions\JWTAuth;

use Tymon\JWTAuth\Claims\Custom;
use Tymon\JWTAuth\Claims\Factory;

class ClaimFactory extends Factory
{
    /**
     * @var array
     */
    private static $classMap = [
        'aud' => 'Tymon\JWTAuth\Claims\Audience',
        'exp' => 'Tymon\JWTAuth\Claims\Expiration',
        'iat' => 'Tymon\JWTAuth\Claims\IssuedAt',
        'iss' => 'Tymon\JWTAuth\Claims\Issuer',
        'jti' => 'Tymon\JWTAuth\Claims\JwtId',
        'nbf' => 'Tymon\JWTAuth\Claims\NotBefore',
        'sub' => 'Tymon\JWTAuth\Claims\Subject',
        'user' => 'App\Extensions\JWTAuth\UserClaim'
    ];

    /**
     * Get the instance of the claim when passing the name and value
     *
     * @param  string  $name
     * @param  mixed   $value
     * @return \Tymon\JWTAuth\Claims\Claim
     */
    public function get($name, $value)
    {
        if ($this->has($name)) {
            return new self::$classMap[$name]($value);
        }

        return new Custom($name, $value);
    }

    /**
     * Check whether the claim exists
     *
     * @param  string  $name
     * @return boolean
     */
    public function has($name)
    {
        return array_key_exists($name, self::$classMap);
    }
}
