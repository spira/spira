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
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
        $result = $this->model->where('permalink','=',$id)->get()->first();

        if (!$result && Uuid::isValid($id)){
            $result = $this->model->find($id);
        }

        /** @var ArticlePermalink $permalink */
        if (!$result && $permalink = ArticlePermalink::find($id)) {
            $result = $permalink->article;
        }

        if (!$result){
            throw new ModelNotFoundException('Could not find article for current permalink');
        }

        return $result;
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
