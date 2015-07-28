<?php namespace App\Extensions\JWTAuth;

use Tymon\JWTAuth\Claims\Claim;

class UserClaim extends Claim
{
    /**
     * The claim name.
     *
     * @var string
     */
    protected $name = '_user';

    /**
     * Validate the user claim.
     *
     * @param  mixed  $value
     * @return boolean
     */
    protected function validate($value)
    {
        return is_array($value) || is_null($value);
    }
}
