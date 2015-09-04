<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Http\Transformers;

use App;
use Tymon\JWTAuth\Token;
use App\Exceptions\NotImplementedException;

class AuthTokenTransformer extends EloquentModelTransformer
{
    /**
     * Transform the token string into an response array.
     *
     * @param  string  $object
     * @return array
     */
    public function transformItem($object)
    {
        if (is_string($object)) {
            return $this->transformToken($object);
        }

        return parent::transformItem($this->transformToken($object['token']));
    }

    protected function transformToken($token)
    {
        $token = new Token($token);
        $jwtAuth = App::make('Tymon\JWTAuth\JWTAuth');

        return [
            'token' => (string) $token,
            'decodedTokenBody' => $jwtAuth->decode($token)->toArray(),
        ];
    }

    /**
     * Collections are not used for token transformations.
     *
     * @param  mixed  $collection
     * @throws NotImplementedException
     * @return void
     */
    public function transformCollection($collection)
    {
        throw new NotImplementedException('Collections are not used for tokens.');
    }
}
