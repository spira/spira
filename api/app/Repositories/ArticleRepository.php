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

        //if the id is a uuid, try that or fail.
        if (Uuid::isValid($id)) {
            return $this->model->findOrFail($id);
        }

        //otherwise attempt treat the id as a permalink and first try the model, then try the history
        try {
            return $this->model
                ->where('permalink', '=', $id)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) { //id or permalink not found, try permalink history
            return ArticlePermalink::findOrFail($id)->article;
        }
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
