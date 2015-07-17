<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 16.07.15
 * Time: 0:19
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
