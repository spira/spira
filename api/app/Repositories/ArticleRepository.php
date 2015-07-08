<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 08.07.15
 * Time: 23:40
 */

namespace App\Repositories;


use App\Models\Article;

class ArticleRepository extends BaseRepository
{

    /**
     * Model name.
     *
     * @return string
     */
    protected function model()
    {
        return Article::class;
    }
}