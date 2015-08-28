<?php

namespace App\Http\Controllers;

use App\Http\Transformers\EloquentModelTransformer;
use App\Models\Image;

class ImageController extends EntityController
{
    public function __construct(Image $model, EloquentModelTransformer $transformer)
    {
        parent::__construct($model, $transformer);
    }
}
