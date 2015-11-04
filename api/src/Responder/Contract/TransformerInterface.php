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
     * @param array $options
     * @return mixed
     */
    public function transformItem($item, array $options);

    /**
     * @param $collection
     * @param array $options
     * @return mixed
     */
    public function transformCollection($collection, array $options);
}
