<?php

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
