<?php

namespace Spira\Rate\Model;


use Spira\Model\Model\BaseModel;

trait RateableTrait
{

    public function rate()
    {
        /** @var BaseModel $model */
        $model = $this;
        return $model->morphMany(Rating::class, 'rateable');
    }
}