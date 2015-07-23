<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 08.07.15
 * Time: 23:40
 */

namespace App\Repositories;

use App\Models\Article;
use App\Models\ArticlePermalink;
use Rhumsaa\Uuid\Uuid;

class ArticleRepository extends BaseRepository
{
    /**
     * @param string $id
     * @param array $columns
     * @return \App\Models\BaseModel|null
     */
    public function find($id, $columns = ['*'])
    {
        $builder = $this->model->query();
        $query = $builder->getQuery();
        $tableName = $this->model->getTable();
        $joinTableName = ArticlePermalink::getTableName();
        $query->join($joinTableName, $joinTableName.'.article_id', '=', $tableName.'.article_id', 'left');
        if (Uuid::isValid($id)) {
            $query->where($tableName.'.article_id', '=', $id);
        }

        $query->orWhere($joinTableName.'.uri', '=', $id);

        return $builder->get($columns)->first();
    }


    /**
     * Model name.
     *
     * @return string
     */
    protected function model()
    {
        return new Article();
    }
}
