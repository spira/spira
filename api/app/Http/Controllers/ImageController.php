<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

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
