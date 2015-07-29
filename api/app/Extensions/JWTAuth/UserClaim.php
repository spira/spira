<?php namespace App\Extensions\JWTAuth;

use App;
use Tymon\JWTAuth\Claims\Claim;
use App\Services\TransformerService;
use App\Http\Transformers\IlluminateModelTransformer;

class UserClaim extends Claim
{
    /**
     * The claim name.
     *
     * @var string
     */
    protected $name = '_user';

    /**
     * Set the claim value, and call a validate method if available
     *
     * @param $value
     * @throws \Tymon\JWTAuth\Exceptions\InvalidClaimException
     * @return $this
     */
    public function setValue($value)
    {
        if ($value) {
            $transformerService = App::make(TransformerService::class);
            $transformer = new IlluminateModelTransformer($transformerService);
            $value = $transformer->transform($value);
        }

        return parent::setValue($value);
    }

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
