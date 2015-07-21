<?php

namespace App\Repositories;

use App\Models\TestEntity;

class TestRepository extends BaseRepository
{
    /**
     * Model name.
     *
     * @return string
     */
    protected function model()
    {
        return new TestEntity();
    }
}
