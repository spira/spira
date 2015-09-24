<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Models\ArticleContent;


use Spira\Model\Model\BaseModel;

abstract class ArticleContentBase extends BaseModel
{
    public $table = false;

    protected $primaryKey = false;

    public $timestamps = false;


    public function save($options = []){
        throw new \LogicException("Cannot save json model");
    }

}
