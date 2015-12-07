<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Http\Transformers;

use Spira\Core\Contract\Exception\NotImplementedException;
use Spira\Core\Responder\Transformers\EloquentModelTransformer;

class AuthTokenTransformer extends EloquentModelTransformer
{
    /**
     * Transform the token string into an response array.
     *
     * @param  string $token
     * @param array $options
     * @return array
     */
    public function transformItem($token, array $options = [])
    {
        $result = ['token' => (string) $token];

        if (env('APP_DEBUG', false)) {
            $result['decodedTokenBody'] = \App::make('auth')->getTokenizer()->decode($token);
        }

        return $result;
    }

    /**
     * Collections are not used for token transformations.
     *
     * @param  mixed $collection
     * @param array $options
     * @return mixed|void
     */
    public function transformCollection($collection, array $options = [])
    {
        throw new NotImplementedException('Collections are not used for tokens.');
    }
}
