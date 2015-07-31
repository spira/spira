<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 31.07.15
 * Time: 11:57
 */

namespace App\Http\Responder;

use App\Models\Article;

class ArticleResponder extends Responder
{
    public function paginatedCollection($items, $offset = null, $totalCount = null, array $parameters = [])
    {
        /** @var Article[] $items */
        foreach ($items as $item) {
            $item->addHidden('content');
        }

        return parent::paginatedCollection($items, $offset, $totalCount, $parameters);
    }
}
