<?php

namespace App\Extensions\JWTAuth;

use App;
use App\Models\User;
use Tymon\JWTAuth\Claims\Claim;
use App\Services\TransformerService;
use Tymon\JWTAuth\Exceptions\InvalidClaimException;
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
     * @param  User  $value
     * @throws InvalidClaimException
     * @return $this
     */
    public function setValue($value)
    {
        // Transform the user before encoding
        $transformerService = App::make(TransformerService::class);
        $transformer = new IlluminateModelTransformer($transformerService);
        $value = $transformer->transform($value);

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
        return is_array($value);
    }
}
