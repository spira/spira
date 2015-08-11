<?php
/**
 * Created by PhpStorm.
 * User: ivanmatveev
 * Date: 10.08.15
 * Time: 19:42
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Spira\Repository\Collection\Collection;

abstract class ChildBaseModel extends BaseModel
{
    /**
     * @param Builder $query
     * @param BaseModel $parent
     * @return Builder
     */
    abstract protected function attachParentModelToQuery(Builder $query, BaseModel $parent);

    /**
     * @param BaseModel $parent
     * @return void
     */
    abstract public function attachParent(BaseModel $parent);

    /**
     * @param $id
     * @param BaseModel $parent
     * @return BaseModel
     */
    public function findByIdAndParent($id, BaseModel $parent)
    {
        $query = $this->attachParentModelToQuery($this->newQuery(), $parent);
        return $query->findOrFail($id);
    }

    /**
     * @param $ids
     * @param BaseModel $parent
     * @return Collection
     */
    public function findManyByIdsAndParent($ids, BaseModel $parent)
    {
        $query = $this->attachParentModelToQuery($this->newQuery(), $parent);
        return $query->findMany($ids);
    }

    public function findAllByParent(BaseModel $parent)
    {
        $query = $this->attachParentModelToQuery($this->newQuery(), $parent);
        return $query->get();
    }
}
