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
use App\Exceptions\NotImplementedException;

class AuthTokenTransformer extends EloquentModelTransformer
{
    /**
     * Transform the token string into an response array.
     *
     * @param  string  $token
     * @return array
     */
    public function transformItem($token)
    {
        return [
            'token' => (string) $token
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
