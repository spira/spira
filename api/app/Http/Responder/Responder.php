<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 20.07.15
 * Time: 8:44
 */

namespace App\Http\Responder;

use Spira\Repository\Model\BaseModel;
use Spira\Responder\Responder\ApiResponder;

class Responder extends ApiResponder
{
    /**
     * @param BaseModel[] $items
     * @param array $parameters
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createdCollection($items, array $parameters = [])
    {
        foreach ($items as $item) {
            $item->setVisible(['']);
        }

        return parent::createdCollection($items, $parameters);
    }

    /**
     * @param BaseModel $item
     * @param array $parameters
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createdItem($item, array $parameters = [])
    {
        $item->setVisible(['']);
        return parent::createdItem($item, $parameters);
    }
}
