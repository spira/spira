<?php

namespace Spira\Responder\Contract;

interface TransformableInterface
{
    /**
     * @param TransformerInterface $transformer
     * @return mixed
     */
    public function transform(TransformerInterface $transformer);
}
