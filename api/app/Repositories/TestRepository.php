<?php namespace App\Repositories;

class TestRepository extends BaseRepository
{
    /**
     * Model name.
     *
     * @return string
     */
    protected function model()
    {
        return 'App\Models\TestEntity';
    }
}
