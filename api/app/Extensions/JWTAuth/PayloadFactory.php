<?php namespace App\Extensions\JWTAuth;

use App;
use App\Repositories\UserRepository;
use App\Services\TransformerService;
use App\Http\Transformers\IlluminateModelTransformer;
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

    /**
     * Get the user object array for the user claim.
     *
     * @return  mixed
     */
    protected function _user()
    {
        $users = App::make(UserRepository::class);
        $id = $this->claims['sub'];

        try {
            $user = $users->find($id);
        } catch (\Exception $e) {
            return null;
        }

        // Transform the user array
        $transformerService = App::make(TransformerService::class);
        $transformer = new IlluminateModelTransformer($transformerService);
        $user = $transformer->transform($user);

        return $user;
    }
}
