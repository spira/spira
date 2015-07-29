<?php

namespace App\Http\Transformers;

use App;
use Tymon\JWTAuth\Token;
use Spira\Responder\Contract\TransformerInterface;

class AuthTokenTransformer implements TransformerInterface
{
    /**
     * Transform the token string into an response array.
     *
     * @param  string  $token
     * @return array
     */
    public function transformItem($token)
    {
        $token = new Token($token);
        $jwtAuth = App::make('Tymon\JWTAuth\JWTAuth');

        return [
            'token' => (string) $token,
            'decodedTokenBody' => $jwtAuth->decode($token)->toArray()
        ];
    }

    /**
     * Collections are not used for token transformations.
     *
     * @param  mixed  $collection
     * @return void
     */
    public function transformCollection($collection)
    {
    }
}
