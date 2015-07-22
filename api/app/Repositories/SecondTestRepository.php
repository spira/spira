<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 15.07.15
 * Time: 2:36
 */

namespace App\Repositories;

use App\Models\BaseModel;
use App\Models\SecondTestEntity;

class SecondTestRepository extends BaseRepository
{
    /**
     * Model name.
     *
     * @return BaseModel
     */
    protected function model()
    {
        return new SecondTestEntity();
    }
}
