<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 17.07.15
 * Time: 20:19
 */

namespace Spira\Responder\Contract;

interface TransformableInterface
{
    /**
     * @param TransformerInterface $transformer
     * @return mixed
     */
    public function transform(TransformerInterface $transformer);
}
