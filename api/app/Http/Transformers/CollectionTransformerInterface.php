<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 08.07.15
 * Time: 21:04
 */

namespace App\Http\Transformers;


interface CollectionTransformerInterface
{
    /**
     * @param $collection
     * @return mixed
     */
    public function transformCollection($collection);
}