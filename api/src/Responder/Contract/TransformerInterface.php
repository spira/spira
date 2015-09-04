<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spira\Responder\Contract;

interface TransformerInterface
{
    /**
     * @param $item
     * @return mixed
     */
    public function transformItem($item);

    /**
     * @param $collection
     * @return mixed
     */
    public function transformCollection($collection);
}
