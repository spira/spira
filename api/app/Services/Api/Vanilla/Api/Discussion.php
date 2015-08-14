<?php

namespace App\Services\Api\Vanilla\Api;

class Discussion extends ApiAbstract
{
    /**
     * List all discussions.
     *
     * @link https://github.com/kasperisager/vanilla-api/wiki/Endpoints#find-all-discussions
     *
     * @param int $page
     *
     * @return array
     */
    public function all($page = 1)
    {
        return $this->get('discussions', ['page' => 'p'.$page]);
    }
}
