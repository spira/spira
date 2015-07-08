<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 08.07.15
 * Time: 21:04
 */

namespace App\Http\Transformers;


interface ItemTransformerInterface
{
    /**
     * @param $item
     * @return mixed
     */
    public function transformItem($item);
}