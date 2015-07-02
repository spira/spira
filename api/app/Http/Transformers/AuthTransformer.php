<?php namespace App\Http\Transformers;

use Illuminate\Contracts\Support\Arrayable;

class AuthTransformer extends BaseTransformer
{
    /**
     * Turn the object into a format adjusted array.
     *
     * @param  Illuminate\Contracts\Support\Arrayable $object
     * @return array
     */
    public function transform(Arrayable $object)
    {
        return parent::transform($object);
    }
}
