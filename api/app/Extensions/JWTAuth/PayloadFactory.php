<?php namespace App\Extensions\JWTAuth;

use App;
use Tymon\JWTAuth\PayloadFactory as PayloadFactoryBase;

class PayloadFactory extends PayloadFactoryBase
{
    /**
     * @var array
     */
    protected $defaultClaims = ['iss', 'iat', 'exp', 'nbf', 'jti', 'user'];

    /**
     * Create a random value for the token.
     *
     * @return string
     */
    protected function jti()
    {
        return str_random(16);
    }

    /**
     * Get the user object array for the token.
     *
     * @return  mixed
     */
    protected function user()
    {
        $users = App::make('App\Repositories\UserRepository');
        $id = $this->claims['sub'];

        try {
            $user = $users->find($id);
        } catch (\Exception $e) {
            return null;
        }

        return $user->toArray();
    }
}
